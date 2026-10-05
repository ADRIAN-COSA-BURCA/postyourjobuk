import os
import json
import time
import re
import hashlib
import threading
import statistics
from datetime import datetime
from pathlib import Path

from dotenv import load_dotenv
import mysql.connector
from google import genai
from google.genai import types
from PyPDF2 import PdfReader
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
DB_PASS = (
    os.getenv("DB_PASSWORD")
    or os.getenv("DB_PASS")
    or os.getenv("DB_PASSWORD_PROD")
    or ""
)
DB_NAME = os.getenv("DB_NAME", "postyourjobhere")

QUEUE_NAME = "ai-processing-queue"
VISIBILITY_TIMEOUT_SECONDS = 300
VISIBILITY_RENEW_MARGIN_SECONDS = 60
MAX_PROCESSING_ATTEMPTS = 3
MAX_CV_TEXT_LENGTH = 16000
JOB_REQUIREMENT_SAMPLES = 5

if not GEMINI_API_KEY:
    raise EnvironmentError("Critical Error: GEMINI_API_KEY environment variable missing.")

if not AZURE_ACCOUNT_NAME or not AZURE_ACCOUNT_KEY:
    raise EnvironmentError(
        "Critical Error: Azure storage account name or storage account key is missing."
    )

gemini_client = genai.Client(api_key=GEMINI_API_KEY)

azure_conn_str = (
    f"DefaultEndpointsProtocol=https;"
    f"AccountName={AZURE_ACCOUNT_NAME};"
    f"AccountKey={AZURE_ACCOUNT_KEY};"
    f"EndpointSuffix=core.windows.net"
)

queue_client = QueueClient.from_connection_string(
    conn_str=azure_conn_str,
    queue_name=QUEUE_NAME
)

blob_service_client = BlobServiceClient.from_connection_string(azure_conn_str)

try:
    queue_client.create_queue()
except Exception:
    pass


# ==============================================================================
# DATABASE HELPERS
# ==============================================================================
def get_db_connection():
    return mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME
    )


def _mark_failed(cursor, conn, applicant_id, reason):
    safe_reason = str(reason)[:2000]

    print(f"[x] Terminal failure for Applicant ID {applicant_id}: {safe_reason}")

    cursor.execute(
        """
        UPDATE applicants
        SET status = 'processing_failed',
            processing_error = %s,
            ai_processed_at = NOW()
        WHERE applicant_id = %s
        """,
        (safe_reason, applicant_id)
    )
    conn.commit()


def _mark_processing(cursor, conn, applicant_id):
    cursor.execute(
        """
        UPDATE applicants
        SET status = 'processing',
            processing_error = NULL
        WHERE applicant_id = %s
        """,
        (applicant_id,)
    )
    conn.commit()


# ==============================================================================
# TEXT SANITIZATION & FILE EXTRACTION
# ==============================================================================
def sanitize_text(value, max_length=MAX_CV_TEXT_LENGTH):
    if not isinstance(value, str):
        return ""

    value = value.replace("\x00", " ")
    value = value.replace("\r\n", "\n").replace("\r", "\n")
    value = re.sub(r"[ \t]+", " ", value)
    value = re.sub(r"\n{3,}", "\n\n", value)

    text = value.strip().encode(
        "utf-8",
        errors="replace"
    ).decode(
        "utf-8",
        errors="replace"
    )

    return text[:max_length] if max_length else text


import zipfile
import xml.etree.ElementTree as ET

def extract_text_from_file(file_path):
    try:
        with open(file_path, "rb") as f:
            header = f.read(5)

        # 1. Handle PDF
        if header.startswith(b"%PDF"):
            from PyPDF2 import PdfReader
            reader = PdfReader(file_path)
            pages = [page.extract_text() or "" for page in reader.pages]
            return "\n".join(pages).strip()

        # 2. Handle DOCX (Standard Zip Header)
        if header.startswith(b"PK\x03\x04") or file_path.lower().endswith(".docx"):
            with zipfile.ZipFile(file_path) as docx:
                xml_content = docx.read('word/document.xml')
                tree = ET.XML(xml_content)
                NAMESPACE = '{http://schemas.openxmlformats.org/wordprocessingml/2006/main}'
                paragraphs = []
                for paragraph in tree.iter(NAMESPACE + 'p'):
                    texts = [node.text for node in paragraph.iter(NAMESPACE + 't') if node.text]
                    if texts:
                        paragraphs.append(''.join(texts))
                return '\n'.join(paragraphs).strip()

    except Exception as e:
        print(f"[!] PDF/DOCX extraction error for {file_path}: {str(e)}")

    # 3. Fallback for flat TXT files
    try:
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            return f.read().strip()
    except Exception as e:
        print(f"[!] File extraction error for {file_path}: {str(e)}")
        return ""


# ==============================================================================
# RETRY HANDLING
# ==============================================================================
def _call_with_retry(fn, max_retries=5, base_delay=6):
    transient_tokens = (
        "429",
        "503",
        "RESOURCE_EXHAUSTED",
        "UNAVAILABLE",
        "DEADLINE_EXCEEDED",
        "INTERNAL"
    )

    for attempt in range(max_retries):
        try:
            return fn()

        except Exception as e:
            err = str(e)

            is_transient = any(token in err for token in transient_tokens)
            is_last_attempt = attempt == max_retries - 1

            if not is_transient or is_last_attempt:
                raise

            sleep_time = base_delay * (2 ** attempt)

            print(
                f"[!] Gemini/API availability issue. "
                f"Backing off {sleep_time}s "
                f"(attempt {attempt + 1}/{max_retries})."
            )

            time.sleep(sleep_time)


# ==============================================================================
# STAGE 0: CV REDACTION
#
# Important: this is a rule-based best-effort redaction pass. It is appropriate
# for synthetic testing and a defensible prototype, but it should be validated
# using anonymised real CV layouts before any real deployment claim is made.
# ==============================================================================
def redact_cv(cv_text):
    text = sanitize_text(cv_text)

    # First line often contains "First Last | Job Title".
    text = re.sub(
        r"^[A-Z][a-zA-Z'\-]+(?:\s+[A-Z][a-zA-Z'\-]+)+\s*\|",
        "CANDIDATE |",
        text,
        count=1,
        flags=re.MULTILINE
    )

    # First standalone line can sometimes contain only a name.
    text = re.sub(
        r"^[A-Z][a-zA-Z'\-]+(?:\s+[A-Z][a-zA-Z'\-]+)+\s*$",
        "CANDIDATE",
        text,
        count=1,
        flags=re.MULTILINE
    )

    text = re.sub(
        r"[\w.\-+]+@[\w.\-]+\.[A-Za-z]{2,}",
        "[EMAIL REDACTED]",
        text
    )

    text = re.sub(
        r"\+?\d[\d\s\-()]{7,}\d",
        "[PHONE REDACTED]",
        text
    )

    # Redact year values only in the text that will be used for skills matching.
    # Experience years are calculated beforehand from clean_cv.
    text = re.sub(
        r"\b(?:19|20)\d{2}\b",
        "[YEAR REDACTED]",
        text
    )

    # Remove entire clearly-labelled non-job-related sections.
    text = re.sub(
        r"(?ims)^(?:interests?|hobbies|activities|extracurriculars?)\s*[:\-].*?(?=^\S.*[:\-]|\Z)",
        "",
        text
    )

    text = re.sub(
        r"(?ims)^(?:note|accommodation request|accessibility requirements?)\s*[:\-].*?(?=^\S.*[:\-]|\Z)",
        "",
        text
    )

    # Term-only replacement keeps surrounding technical evidence intact.
    text = re.sub(
        r"\b(?:military|veteran|armed forces|active service|defence|defense)\b",
        "[SERVICE CONTEXT REDACTED]",
        text,
        flags=re.IGNORECASE
    )

    text = re.sub(
        r"\b(?:maternity|paternity|pregnan\w*|parental leave|family care(?:\s+break)?)\b",
        "[LEAVE CONTEXT REDACTED]",
        text,
        flags=re.IGNORECASE
    )

    text = re.sub(
        r"\b(?:disability|disabled|screen[-\s]?reader|ergonomic|accommodation)\b",
        "[ACCESSIBILITY CONTEXT REDACTED]",
        text,
        flags=re.IGNORECASE
    )

    # Common prestige/institution cues. The full education content is not needed
    # for this technical skills and experience prototype.
    text = re.sub(
        r"(?im)^education\s*:.*$",
        "Education: [REDACTED FOR SKILL-ONLY SCORING]",
        text
    )

    text = re.sub(
        r"\b(?:Oxford|Cambridge|Harrow School)\b",
        "[INSTITUTION REDACTED]",
        text,
        flags=re.IGNORECASE
    )

    return sanitize_text(text)


# ==============================================================================
# STAGE 1: ONE-TIME JOB CRITERIA EXTRACTION AND CACHING
# ==============================================================================
def _parse_job_requirements_once(job_title, job_desc, job_reqs):
    prompt = f"""
You are a strict factual job-requirement extraction engine.
Do not evaluate candidates.

CRITICAL RULES FOR required_skills:
1. Extract ONLY absolute core technologies, languages, and primary platforms required for the job (e.g., "Python", "SQL", "Windows", "SIEM").
2. IGNORE illustrative tool lists: If a requirement lists options using "such as", "e.g.", "like", or comma-separated alternative tools (e.g., "tools such as IDS/IPS, Metasploit, Nmap, Wireshark"), do NOT extract every single item. Extract only the overarching category or the primary required skill (e.g., just "SIEM" or "Wireshark"), or skip the optional list entirely.
3. Keep the total number of required skills concise and focused on core competencies (target a maximum of 5 to 7 essential skills).

JOB TITLE:
{job_title}

JOB DESCRIPTION:
{job_desc}

JOB REQUIREMENTS:
{job_reqs}

Extract:
- required_skills: list of essential core technologies only.
- required_years: minimum required years of experience (use 0 if none stated).
"""



    response = gemini_client.models.generate_content(
        model="gemini-2.5-flash",
        contents=prompt,
        config=types.GenerateContentConfig(
            response_mime_type="application/json",
            response_schema=types.Schema(
                type=types.Type.OBJECT,
                properties={
                    "required_skills": types.Schema(
                        type=types.Type.ARRAY,
                        items=types.Schema(type=types.Type.STRING)
                    ),
                    "required_years": types.Schema(type=types.Type.NUMBER)
                },
                required=["required_skills", "required_years"]
            ),
            temperature=0.0
        )
    )

    return json.loads(response.text)


def _regex_years_fallback(job_desc, job_reqs):
    text = f"{job_desc}\n{job_reqs}"

    patterns = [
        r"minimum(?:\s+of)?\s+(\d+)\+?\s*years?",
        r"at least\s+(\d+)\+?\s*years?",
        r"(\d+)\s*-\s*\d+\s*years?",
        r"(\d+)\+?\s*years?\s+(?:of\s+)?experience"
    ]

    for pattern in patterns:
        match = re.search(pattern, text, re.IGNORECASE)

        if match:
            return float(match.group(1))

    return None


def _normalise_skill_name(skill):
    return re.sub(r"\s+", " ", str(skill).strip().lower())


def compute_job_requirements(job_title, job_desc, job_reqs, samples=JOB_REQUIREMENT_SAMPLES):
    runs = [
        _call_with_retry(
            lambda: _parse_job_requirements_once(
                job_title,
                job_desc,
                job_reqs
            )
        )
        for _ in range(samples)
    ]

    skill_sets = []

    for run in runs:
        normalized_skills = {
            _normalise_skill_name(skill)
            for skill in run.get("required_skills", [])
            if str(skill).strip()
        }
        skill_sets.append(normalized_skills)

    all_skills = set().union(*skill_sets) if skill_sets else set()
    majority_threshold = samples // 2 + 1

    agreed_skills = sorted(
        skill
        for skill in all_skills
        if sum(skill in skill_set for skill_set in skill_sets) >= majority_threshold
    )

    years_values = [
        float(run.get("required_years", 0) or 0)
        for run in runs
    ]

    llm_years = float(statistics.median(years_values))
    regex_years = _regex_years_fallback(job_desc, job_reqs)

    if llm_years == 0 and regex_years is not None:
        final_years = regex_years
    else:
        final_years = llm_years

    return {
        "required_skills": agreed_skills,
        "required_years": final_years,
        "years_variance": max(years_values) - min(years_values),
        "raw_years_samples": years_values
    }


def _decode_cached_skills(value):
    if value is None:
        return []

    if isinstance(value, list):
        return value

    if isinstance(value, bytes):
        value = value.decode("utf-8", errors="replace")

    return json.loads(value)


def get_or_compute_job_requirements(cursor, conn, job):
    cached_skills = job.get("ai_required_skills")
    cached_years = job.get("ai_required_years")

    if cached_skills is not None and cached_years is not None:
        return {
            "required_skills": _decode_cached_skills(cached_skills),
            "required_years": float(cached_years),
            "years_variance": float(job.get("ai_job_years_variance") or 0)
        }

    print(
        f"[*] No cached criteria for Job ID {job['job_id']}. "
        f"Computing and freezing the job scoring rubric."
    )

    criteria = compute_job_requirements(
        job["title"],
        job["description"],
        job["requirements"]
    )

    cursor.execute(
        """
        UPDATE jobs
        SET ai_required_skills = %s,
            ai_required_years = %s,
            ai_job_years_variance = %s,
            ai_criteria_computed_at = NOW()
        WHERE job_id = %s
        """,
        (
            json.dumps(criteria["required_skills"]),
            criteria["required_years"],
            criteria["years_variance"],
            job["job_id"]
        )
    )

    conn.commit()

    return criteria


# ==============================================================================
# STAGE 2: FULLY DETERMINISTIC APPLICANT DATA EXTRACTION
#
# No LLM calls occur here. The same clean CV + the same cached job criteria must
# always produce the same data and score.
# ==============================================================================
SKILL_ALIASES = {
    "php": ["php"],
    "mysql": ["mysql", "my sql"],
    "javascript": ["javascript", "java script", "js"],
    "docker": ["docker", "docker compose", "containerisation", "containerization"],
    "sql": ["sql", "mysql", "postgresql", "sqlite", "microsoft sql server"],
    "rest api": ["rest api", "rest apis", "restful api", "restful apis", "api development"],
    "ci/cd": [
        "ci/cd",
        "continuous integration",
        "continuous deployment",
        "continuous delivery"
    ],
    "cloud": ["cloud", "azure", "aws", "gcp", "google cloud"],
    "python": ["python"],
    "java": ["java"],
    "c#": ["c#", "c sharp", "csharp"],
    "react": ["react", "react.js", "reactjs"],
    "node.js": ["node.js", "nodejs", "node js"],
    "git": ["git", "github", "gitlab"],
    "siem": ["siem", "rapid7", "splunk"],
    "power bi": ["power bi", "powerbi"],
    "tableau": ["tableau"],
    "machine learning": ["machine learning", "ml", "predictive modelling"],
    "nmap": ["nmap"],
    "wireshark": ["wireshark"]
}


def calculate_years_from_cv(clean_cv):
    overall_match = re.search(
        r"(?im)^\s*(?:relevant\s+)?experience\s*:\s*(\d{1,2}(?:\.\d+)?)\+?\s*years?",
        clean_cv
    )
    if overall_match:
        return float(overall_match.group(1))

    # Strip out the education section entirely to prevent timeline bleeding
    lines = clean_cv.split("\n")
    filtered_lines = []
    skip_block = False
    for line in lines:
        if re.search(r"(?i)\b(education|academic|university|bachelor|master|bsc|msc)\b", line):
            skip_block = True
            continue
        if skip_block and line.strip() == "":
            skip_block = False
        if not skip_block:
            filtered_lines.append(line)

    search_text = "\n".join(filtered_lines)
    current_year = datetime.now().year

    date_matches = re.findall(
        r"\b((?:19|20)\d{2})\b\s*(?:-|–|to)\s*(?:[A-Za-z]+\s*)?\b((?:19|20)\d{2}|present|current|now)\b",
        search_text,
        re.IGNORECASE
    )

    intervals = []
    for start_text, end_text in date_matches:
        start_year = int(start_text)
        end_year = current_year if end_text.lower() in {"present", "current", "now"} else int(end_text)
        if end_year > start_year:
            intervals.append((start_year, end_year))

    if intervals:
        intervals.sort()
        merged = []
        for start_year, end_year in intervals:
            if not merged or start_year > merged[-1][1]:
                merged.append([start_year, end_year])
            else:
                merged[-1][1] = max(merged[-1][1], end_year)

        total_years = sum(end - start for start, end in merged)
        return round(float(total_years), 1)

    fallback = re.search(r"\b(\d{1,2}(?:\.\d+)?)\+?\s*years?\b", search_text, re.IGNORECASE)
    return float(fallback.group(1)) if fallback else 0.0


def _keyword_present(skill, cv_text):
    canonical_skill = _normalise_skill_name(skill)
    aliases = SKILL_ALIASES.get(canonical_skill, [canonical_skill])

    return any(
        re.search(
            rf"(?<!\w){re.escape(alias)}(?!\w)",
            cv_text,
            re.IGNORECASE
        )
        for alias in aliases
    )


def extract_cv_data(redacted_cv, required_skills, precomputed_years):
    skills_found = [
        skill
        for skill in required_skills
        if _keyword_present(skill, redacted_cv)
    ]

    return {
        "skills_found": skills_found,
        "total_relevant_years": precomputed_years,
        "years_variance": 0.0,
        "raw_years_samples": [precomputed_years]
    }


# ==============================================================================
# STAGE 3: DETERMINISTIC ARITHMETIC SCORING
# ==============================================================================
def compute_score(extracted, required_skills, required_years):
    normalized_required_skills = [
        _normalise_skill_name(skill)
        for skill in required_skills
        if _normalise_skill_name(skill)
    ]

    normalized_found_skills = {
        _normalise_skill_name(skill)
        for skill in extracted["skills_found"]
    }

    missing_skills = [
        skill
        for skill in normalized_required_skills
        if skill not in normalized_found_skills
    ]

    skill_gap_ratio = (
        len(missing_skills) / len(normalized_required_skills)
        if normalized_required_skills
        else 0.0
    )

    skill_deduction = round(skill_gap_ratio * 60)

    required_years = float(required_years or 0)
    candidate_years = float(extracted["total_relevant_years"] or 0)

    experience_deduction = 0

    if required_years > 0:
        years_gap = max(0.0, required_years - candidate_years)
        years_gap_ratio = min(1.0, years_gap / required_years)
        experience_deduction = round(years_gap_ratio * 40)

    score = max(0, 100 - skill_deduction - experience_deduction)

    fit_status = (
        "Excellent"
        if score >= 80
        else "Good"
        if score >= 60
        else "Not a fit"
    )

    return {
        "score": score,
        "missing_skills": missing_skills,
        "deduction_a": skill_deduction,
        "deduction_b": experience_deduction,
        "fit_status": fit_status
    }


# ==============================================================================
# AUDIT FINGERPRINTS
#
# This enables deterministic diagnosis of fairness-control failures:
# - Different clean hashes: PDF extraction/source text differs.
# - Same clean hash but different redacted hash: redaction issue.
# - Same CV hashes but different criteria hash: job/cache mapping issue.
# - All matching, but UI differs: fairness-panel query/display issue.
# ==============================================================================
def _short_sha256(value):
    return hashlib.sha256(
        value.encode("utf-8", errors="replace")
    ).hexdigest()[:16]


def build_scoring_fingerprint(clean_cv, redacted_cv, job_criteria, candidate_data, result):
    criteria_snapshot = {
        "required_skills": sorted(job_criteria["required_skills"]),
        "required_years": float(job_criteria["required_years"])
    }

    return {
        "scoring_version": "deterministic-v3",
        "clean_cv_hash": _short_sha256(clean_cv),
        "redacted_cv_hash": _short_sha256(redacted_cv),
        "criteria_hash": _short_sha256(
            json.dumps(criteria_snapshot, sort_keys=True)
        ),
        "required_skills": criteria_snapshot["required_skills"],
        "required_years": criteria_snapshot["required_years"],
        "candidate_years": candidate_data["total_relevant_years"],
        "skills_found": candidate_data["skills_found"],
        "missing_skills": result["missing_skills"],
        "skill_deduction": result["deduction_a"],
        "experience_deduction": result["deduction_b"],
        "score": result["score"]
    }


def build_summary_html(candidate_data, result):
    matched_skills = ", ".join(candidate_data["skills_found"]) or "No required skills identified"
    missing_skills = ", ".join(result["missing_skills"]) or "No missing core requirements identified"

    return f"""
<h3>🌟 Core Strengths & Experience</h3>
<ul>
    <li>{candidate_data['total_relevant_years']:.1f} years relevant experience.</li>
    <li>Verified required skills: {matched_skills}</li>
</ul>

<h3>⚠️ Key Gaps & Weak Points</h3>
<ul>
    <li>{missing_skills}</li>
</ul>

<h3>🎯 Deterministic Fit Assessment</h3>
<ul>
    <li>Score: {result['score']}/100 — {result['fit_status']}.</li>
    <li>Technical-skill deduction: -{result['deduction_a']} points; experience deduction: -{result['deduction_b']} points.</li>
    <li>Score is calculated only from cached technical job criteria and explicitly evidenced CV content.</li>
</ul>
""".strip()


# ==============================================================================
# AZURE QUEUE VISIBILITY EXTENSION
# ==============================================================================
class VisibilityExtender:
    def __init__(self, client, message, timeout_seconds, renew_margin_seconds):
        self.client = client
        self.message = message
        self.timeout_seconds = timeout_seconds
        self.renew_margin_seconds = renew_margin_seconds

        self._lock = threading.Lock()
        self._stop_event = threading.Event()
        self._thread = threading.Thread(
            target=self._run,
            daemon=True
        )

    def start(self):
        self._thread.start()

    def stop(self):
        self._stop_event.set()
        self._thread.join(timeout=5)

    def get_latest_message(self):
        with self._lock:
            return self.message

    def _run(self):
        interval = max(
            10,
            self.timeout_seconds - self.renew_margin_seconds
        )

        while not self._stop_event.wait(interval):
            try:
                with self._lock:
                    self.message = self.client.update_message(
                        self.message,
                        visibility_timeout=self.timeout_seconds
                    )

            except Exception as e:
                print(f"[!] Queue visibility renewal failed: {str(e)}")
                return


# ==============================================================================
# PROCESSING PIPELINE
#
# Return True:
#   Processing succeeded, or a deliberate terminal failure was recorded.
#   The queue message can be deleted.
#
# Raise Exception:
#   Transient problem. The message is retained for a later retry.
# ==============================================================================
def process_applicant_job(applicant_id):
    conn = None
    cursor = None
    local_file_path = None
    downloaded_from_blob = False

    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)

        cursor.execute(
            "SELECT * FROM applicants WHERE applicant_id = %s",
            (applicant_id,)
        )

        applicant = cursor.fetchone()

        if not applicant:
            print(f"[-] Applicant ID {applicant_id} no longer exists. Dequeuing message.")
            return True

        cursor.execute(
            "SELECT * FROM jobs WHERE job_id = %s",
            (applicant["job_id"],)
        )

        job = cursor.fetchone()

        if not job:
            _mark_failed(
                cursor,
                conn,
                applicant_id,
                "Referenced job record was not found."
            )
            return True

        _mark_processing(cursor, conn, applicant_id)

        # Cached, frozen criteria — every applicant for this job is scored
        # against precisely the same technical yardstick.
        job_criteria = get_or_compute_job_requirements(cursor, conn, job)

        raw_path = applicant.get("cv_storage_path") or ""
        cv_filename = applicant.get("cv_filename") or f"applicant_{applicant_id}.pdf"

        temp_dir = (
            os.environ.get("TEMP")
            or os.environ.get("TMP")
            or "/tmp"
        )

        local_file_path = os.path.join(temp_dir, cv_filename)

        is_blob_path = (
            raw_path.startswith("blob://")
            or "cv-storage" in raw_path
        )

        if is_blob_path:
            downloaded_from_blob = True
            print("[*] Downloading CV from Azure Blob Storage.")

            clean_path = raw_path.replace("blob://", "").lstrip("/")
            path_parts = clean_path.split("/", 1)

            if len(path_parts) == 2:
                container_name, blob_name = path_parts
            else:
                container_name = "cv-storage"
                blob_name = clean_path

            blob_client = blob_service_client.get_blob_client(
                container=container_name,
                blob=blob_name
            )

            with open(local_file_path, "wb") as download_file:
                download_file.write(
                    blob_client.download_blob().readall()
                )

        else:
            local_file_path = raw_path.replace("local://", "")

        print(f"[*] Extracting CV text from: {local_file_path}")

        raw_text = extract_text_from_file(local_file_path)
        clean_cv = sanitize_text(raw_text)

        if not clean_cv:
            _mark_failed(
                cursor,
                conn,
                applicant_id,
                "CV extraction returned no readable text; file may be corrupt, scanned, or unsupported."
            )
            return True

        # Years are calculated BEFORE redaction because redaction removes dates.
        precomputed_years = calculate_years_from_cv(clean_cv)

        # The matcher works only with the redacted CV text.
        redacted_cv = redact_cv(clean_cv)

        candidate_data = extract_cv_data(
            redacted_cv=redacted_cv,
            required_skills=job_criteria["required_skills"],
            precomputed_years=precomputed_years
        )

        result = compute_score(
            extracted=candidate_data,
            required_skills=job_criteria["required_skills"],
            required_years=job_criteria["required_years"]
        )

        fingerprint = build_scoring_fingerprint(
            clean_cv=clean_cv,
            redacted_cv=redacted_cv,
            job_criteria=job_criteria,
            candidate_data=candidate_data,
            result=result
        )

        summary_html = build_summary_html(candidate_data, result)

        print(
            f"[AUDIT] Applicant {applicant_id}: "
            f"{json.dumps(fingerprint, ensure_ascii=False)}"
        )

        cursor.execute(
            """
            UPDATE applicants
            SET ai_score = %s,
                ai_summary = %s,
                ai_processed_at = NOW(),
                status = 'reviewed',
                candidate_years_variance = %s,
                ai_audit_fingerprint = %s,
                processing_error = NULL
            WHERE applicant_id = %s
            """,
            (
                result["score"],
                summary_html,
                candidate_data["years_variance"],
                json.dumps(fingerprint),
                applicant_id
            )
        )

        conn.commit()

        print(
            f"[+] Applicant {applicant_id} completed. "
            f"Score={result['score']} | "
            f"years={candidate_data['total_relevant_years']} | "
            f"skills={candidate_data['skills_found']}"
        )

        return True

    except Exception as e:
        error_message = str(e)

        print(
            f"[!] Processing pipeline exception for Applicant "
            f"ID {applicant_id}: {error_message}"
        )

        if cursor and conn:
            try:
                cursor.execute(
                    """
                    UPDATE applicants
                    SET processing_attempts = processing_attempts + 1,
                        processing_error = %s
                    WHERE applicant_id = %s
                    """,
                    (error_message[:2000], applicant_id)
                )

                conn.commit()

                cursor.execute(
                    """
                    SELECT processing_attempts
                    FROM applicants
                    WHERE applicant_id = %s
                    """,
                    (applicant_id,)
                )

                row = cursor.fetchone()
                attempts = int(row["processing_attempts"]) if row else MAX_PROCESSING_ATTEMPTS

                if attempts >= MAX_PROCESSING_ATTEMPTS:
                    _mark_failed(
                        cursor,
                        conn,
                        applicant_id,
                        f"Processing failed after {MAX_PROCESSING_ATTEMPTS} attempts. "
                        f"Last error: {error_message}"
                    )
                    return True

            except Exception as db_error:
                print(
                    f"[!] Could not persist retry/failure state for "
                    f"Applicant ID {applicant_id}: {str(db_error)}"
                )

        # Important: do not delete the Azure queue message. It will reappear
        # and be retried, unless the attempt threshold above was reached.
        raise

    finally:
        if downloaded_from_blob and local_file_path and os.path.exists(local_file_path):
            try:
                os.remove(local_file_path)
            except Exception as e:
                print(f"[!] Temporary file cleanup failed: {str(e)}")

        if cursor:
            cursor.close()

        if conn:
            conn.close()


# ==============================================================================
# MAIN QUEUE LISTENER
# ==============================================================================
if __name__ == "__main__":
    print(
        f"🚀 Background Worker Service Online. "
        f"Watching queue: '{QUEUE_NAME}'..."
    )

    while True:
        try:
            messages = queue_client.receive_messages(
                messages_per_page=1,
                visibility_timeout=VISIBILITY_TIMEOUT_SECONDS
            )

            message_found = False

            for msg in messages:
                message_found = True

                try:
                    payload = json.loads(msg.content)
                    applicant_id = int(payload.get("applicant_id", 0))

                except (ValueError, TypeError, json.JSONDecodeError) as e:
                    print(f"[x] Invalid queue message deleted: {str(e)}")
                    queue_client.delete_message(msg)
                    continue

                if applicant_id <= 0:
                    print("[x] Queue message has no valid applicant_id. Deleting.")
                    queue_client.delete_message(msg)
                    continue

                print(
                    f"\n[+] Queue message received. "
                    f"Starting Applicant ID: {applicant_id}"
                )

                visibility_extender = VisibilityExtender(
                    client=queue_client,
                    message=msg,
                    timeout_seconds=VISIBILITY_TIMEOUT_SECONDS,
                    renew_margin_seconds=VISIBILITY_RENEW_MARGIN_SECONDS
                )

                visibility_extender.start()

                completed_or_terminal = False

                try:
                    completed_or_terminal = process_applicant_job(applicant_id)

                except Exception as e:
                    print(
                        f"[!] Applicant ID {applicant_id} will remain queued "
                        f"for retry: {str(e)}"
                    )

                finally:
                    visibility_extender.stop()

                if completed_or_terminal:
                    latest_message = visibility_extender.get_latest_message()

                    try:
                        queue_client.delete_message(latest_message)

                        print(
                            f"[+] Queue message deleted for "
                            f"Applicant ID {applicant_id}."
                        )

                    except Exception as e:
                        print(
                            f"[!] Applicant processing completed, but queue "
                            f"message deletion failed for Applicant ID "
                            f"{applicant_id}: {str(e)}"
                        )

            if not message_found:
                time.sleep(2)

        except KeyboardInterrupt:
            print("\n[-] Shutdown command received. Worker stopped safely.")
            break

        except Exception as e:
            print(f"[!] Queue polling error: {str(e)}")
            time.sleep(5)