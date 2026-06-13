import os
import json
import time
import re
from pathlib import Path
from dotenv import load_dotenv
import mysql.connector
from google import genai
from google.genai import types
from PyPDF2 import PdfReader
from docx import Document
from azure.storage.queue import QueueClient
from azure.storage.blob import BlobServiceClient

# ==============================================================================
# ENVIRONMENT VALIDATION & INITIALIZATION
# ==============================================================================
load_dotenv()

GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")
AZURE_ACCOUNT_NAME = os.getenv("AZURE_STORAGE_ACCOUNT_NAME")
AZURE_ACCOUNT_KEY = os.getenv("AZURE_STORAGE_ACCOUNT_KEY")

DB_HOST = os.getenv("DB_HOST", "localhost")
DB_USER = os.getenv("DB_USER", "root")
DB_PASS = os.getenv("DB_PASSWORD") or os.getenv("DB_PASS") or os.getenv("DB_PASSWORD_PROD") or ""
DB_NAME = os.getenv("DB_NAME", "postyourjobhere")

if not GEMINI_API_KEY:
    raise EnvironmentError("Critical Error: GEMINI_API_KEY environment variable missing.")

# Instantiate modern Gemini API SDK Client
gemini_client = genai.Client(api_key=GEMINI_API_KEY)

# Initialize Azure Storage Connection Strings
azure_conn_str = f"DefaultEndpointsProtocol=https;AccountName={AZURE_ACCOUNT_NAME};AccountKey={AZURE_ACCOUNT_KEY};EndpointSuffix=core.windows.net"

queue_name = "ai-processing-queue"
queue_client = QueueClient.from_connection_string(conn_str=azure_conn_str, queue_name=queue_name)
blob_service_client = BlobServiceClient.from_connection_string(azure_conn_str)

try:
    queue_client.create_queue()
    print(f"[+] Verified: '{queue_name}' didn't exist, created it successfully.")
except Exception:
    pass

# ==============================================================================
# UTILITY TEXT CLEANERS & TEXT EXTRACTORS
# ==============================================================================
def sanitize_text(value, max_length=16000):
    if not isinstance(value, str): 
        return ""
    value = value.replace("\x00", " ").replace("\r\n", "\n").replace("\r", "\n")
    value = re.sub(r"[ \t]+", " ", value)
    value = re.sub(r"\n{3,}", "\n\n", value)
    text = value.strip().encode("utf-8", errors="replace").decode("utf-8", errors="replace")
    return text[:max_length] if max_length else text

def extract_text_from_file(file_path):
    ext = Path(file_path).suffix.lower()
    if ext == ".pdf":
        reader = PdfReader(file_path)
        return "\n".join([p.extract_text() for p in reader.pages if p.extract_text()])
    elif ext == ".docx":
        doc = Document(file_path)
        return "\n".join([p.text.strip() for p in doc.paragraphs if p.text.strip()])
    else:
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            return f.read().strip()

# ==============================================================================
# RECRUITER ENGINE CORE LOGIC
# ==============================================================================
def evaluate_cv_with_ai(cv_text, job_title, job_desc, job_reqs):
    """
    Constructs a structural prompt using structured typing schema matching recruiter requirements.
    """
    clean_cv = sanitize_text(cv_text)
    
    # Redesigned prompt built explicitly for high-impact scannable sections
    prompt = f"""
    You are an expert executive human resources recruiter. Analyze the candidate's CV against the job posting details.
    
    JOB TITLE: {job_title}
    JOB DESCRIPTION: {job_desc}
    JOB REQUIREMENTS: {job_reqs}
    
    CANDIDATE CV TEXT:
    ---
    {clean_cv}
    ---
    
    Generate a recruiter-focused candidate analysis. You must output a JSON object containing an overall alignment score and a beautifully formatted HTML breakdown matching the structural instructions.

    For the 'summary' field, construct the content using clear HTML headings and brief, high-impact bulleted listings exactly as structured below:
    
    <h3>🌟 Core Strengths & Experience</h3>
    <ul>
        <li>[Highlight 2 key technical skills, projects, or tenure records that perfectly align with requirements]</li>
    </ul>

    <h3>⚠️ Key Gaps & Weak points</h3>
    <ul>
        <li>[Highlight 1-2 key gaps, missing criteria, or unverified requirements from the CV context]</li>
    </ul>

    <h3>🎯 Structural Fit Assessment</h3>
    <ul>
        <li>[Provide 1-2 quick sentences on functional/cultural suitability for this role context]</li>
    </ul>
    """
    
    # Modern SDK strict structural typing control
    response = gemini_client.models.generate_content(
        model="gemini-2.5-flash",
        contents=prompt,
        config=types.GenerateContentConfig(
            response_mime_type="application/json",
            response_schema=types.Schema(
                type=types.Type.OBJECT,
                properties={
                    "score": types.Schema(type=types.Type.INTEGER, description="Integer between 0 and 100 representing job matching compliance"),
                    "summary": types.Schema(type=types.Type.STRING, description="The complete structured HTML recruiter sections and bullet points"),
                    "fit_status": types.Schema(type=types.Type.STRING, description="Value must be: 'Excellent', 'Good', or 'Not a fit'")
                },
                required=["score", "summary", "fit_status"]
            ),
            temperature=0.15
        )
    )
    return json.loads(response.text)

def process_applicant_job(applicant_id):
    conn = mysql.connector.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME)
    cursor = conn.cursor(dictionary=True)
    local_file_path = None
    is_blob = False
    
    try:
        cursor.execute("SELECT * FROM applicants WHERE applicant_id = %s", (applicant_id,))
        applicant = cursor.fetchone()
        
        if not applicant:
            print(f"[-] Record processing cancelled: Applicant ID {applicant_id} missing from data tables.")
            return
        
        cursor.execute("SELECT * FROM jobs WHERE job_id = %s", (applicant['job_id'],))
        job = cursor.fetchone()
        
        raw_path = applicant['cv_storage_path']
        temp_dir = os.environ.get('TEMP') or os.environ.get('TMP') or '/tmp'
        local_file_path = os.path.join(temp_dir, applicant['cv_filename'])
        
        if "blob://" in raw_path or "cv-storage" in raw_path:
            is_blob = True
            print(f"[*] Downloading document out of Azure Blob Storage...")
            clean_path = raw_path.replace("blob://", "")
            path_parts = clean_path.split('/', 1)
            
            container_name = path_parts[0] if len(path_parts) > 1 else "cv-storage"
            blob_name = path_parts[1] if len(path_parts) > 1 else clean_path
            
            blob_client = blob_service_client.get_blob_client(container=container_name, blob=blob_name)
            with open(local_file_path, "wb") as download_file:
                download_file.write(blob_client.download_blob().readall())
        else:
            local_file_path = raw_path.replace("local://", "")

        print(f"[*] Extracting raw file content out of: {local_file_path}")
        extracted_text = extract_text_from_file(local_file_path)
        
        print(f"[*] Querying gemini-2.5-flash for Candidate Evaluation on ID: {applicant_id}")
        ai_payload = evaluate_cv_with_ai(extracted_text, job['title'], job['description'], job['requirements'])
        
        update_query = """
            UPDATE applicants 
            SET ai_score = %s, ai_summary = %s, ai_processed_at = NOW(), status = 'reviewed'
            WHERE applicant_id = %s
        """
        cursor.execute(update_query, (ai_payload['score'], ai_payload['summary'], applicant_id))
        conn.commit()
        print(f"[+] Successfully saved AI Metrics to database. Score: {ai_payload['score']}%")
        
    except Exception as e:
        print(f"[!] Processing Pipeline Exception on Applicant ID {applicant_id}: {str(e)}")
    finally:
        # Relocated Cleanup phase to protect file I/O sequence stability
        if is_blob and local_file_path and os.path.exists(local_file_path):
            try:
                os.remove(local_file_path)
            except Exception:
                pass
        cursor.close()
        conn.close()

# ==============================================================================
# MAIN LISTENER DAEMON LOOP
# ==============================================================================
if __name__ == "__main__":
    print(f"🚀 Background Worker Service Online. Watching queue: '{queue_name}'...")
    
    while True:
        try:
            messages = queue_client.receive_messages(messages_per_page=1, visibility_timeout=30)
            message_found = False
            for msg in messages:
                message_found = True
                payload = json.loads(msg.content)
                target_id = int(payload.get("applicant_id", 0))
                
                print(f"\n[+] Message detected in queue. Dispatching execution thread for Applicant ID: {target_id}")
                process_applicant_job(target_id)
                queue_client.delete_message(msg)
                
            if not message_found:
                time.sleep(2)
                
        except KeyboardInterrupt:
            print("\n[-] Shutdown command detected. Background worker safely exiting daemon loops.")
            break
        except Exception as e:
            print(f"[!] Daemon Core Polling Exception: {str(e)}")
            time.sleep(5)