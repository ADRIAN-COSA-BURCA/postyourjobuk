# Technical Log: Project postyourjobuk

This log serves as a detailed record of the architectural, design, and implementation decisions made throughout the project's SDLC. It is designed to support the dissertation chapters by documenting the "Why" and "How" behind the project's technical maturity.

---


adrian final year project disertation; 
final project for level 6 computing; 

** this project starts initially from implementation with local machine using XAMPP then upgrading and adding new features , moving later and updating to a Docker envinronment + azure integration + Google Gemini AI 


Level 6 final project
## Title :
Next-Generation Recruitment Analytics: A Scalable, Multi-Tenant SaaS Platform Leveraging Azure Serverless Architecture
 and Event-Driven AI. Developing an enterprise-grade recruitment ecosystem that utilizes 
 Azure App Service for scalable frontend delivery alongside Azure Functions 
 to facilitate asynchronous, event-driven CV processing, while simultaneously leveraging AI
 to interpret, rank, and select top-tier candidates to deliver real-time, data-driven insights.

## Project Overview
•	What it does: Automates the processing and scoring of CVs against job descriptions, providing recruiters with instant, data-driven candidate rankings.
•	Structure: A hybrid-modular architecture consisting of a PHP-based web frontend and an event-driven "serverless" analytical backend.
•	Core Services & Connections:
o	Frontend (PHP on Azure App Service): Manages user interactions, job postings, and applicant tracking.
o	Analytical Engine (Azure Functions with Python): Acts as the "intelligence" layer, triggered automatically when a file is uploaded to perform deep-text analysis.
o	Data Storage (Azure Database for MySQL flexible servers): Central repository for job data, tenant information, and candidate rankings.
o	File Storage (Azure Blob Storage): A secure, cloud-based vault for storing CVs and resumes, replacing local server storage.

## Azure’s Role
Azure acts as the "backbone" of your platform, providing:
1.	High Availability: Database and storage systems that are accessible 24/7.
2.	Serverless Processing: Azure Functions execute your Python AI code on-demand, ensuring costs are low and performance is high.
3.	Security & Scalability: Built-in enterprise-grade firewall protection and the ability to scale seamlessly as your user base grows toward 2027.



## Summary of Workflow
1.	User Input: A candidate applies via your PHP portal.
2.	Data Persistence: PHP stores metadata in Azure Database for MySQL flexible servers
and the file in Azure Blob Storage.
3.	Automated Trigger: An event triggers an Azure Function.
4.	Intelligence: The Python service analyzes the document and computes a ranking.
5.	Output: The ranking is saved back to Azure Database for MySQL flexible servers
and displayed instantly on the recruiter's dashboard.


** ensure project remains modular, scalable, and easy to transition from local development to existing Azure infrastructure,  treat code as Environment Agnostic.


## 1. Chapter 1: Introduction & Rationale
* **Problem Statement:** Addressing the inefficiency and potential human bias in manual CV screening.
* **Justification:** Utilizing Azure-based AI/ML services to provide objective, standardized candidate ranking.
* **Ethics & GDPR:** Implementation of "Fake CV" generation to ensure data privacy while maintaining functional testing integrity.

## 2. Chapter 2: System Design & Architecture
* **Architecture Choice:** Transition to a cloud-native, service-oriented architecture (SOA).
* **Infrastructure as Code (IaC):** Use of Docker for environment parity (Dev vs. Production).
* **Security Design:** Implementation of the Front Controller pattern (index.php) to centralize access control and authentication.
* **Database Design:** Normalization strategy for Recruiter/Job/Applicant/CV metadata entities.

## 3. Chapter 3: Implementation Details
* **Environment Setup:** Configuration of Docker containers (Web/DB) and automated dependency management via Composer.
* **Core Logic:** Implementation of Singleton-pattern `Database.php` for modular, secure PDO interactions.
* **Azure Integration:**
    * Blob Storage for secure, scalable CV document management.
    * AI/Cognitive Services integration for automated skill extraction and candidate scoring.
* **SecurityLogger:** Development of the audit trail class to support non-repudiation and compliance requirements.

## 4. Chapter 4: Testing & Quality Assurance
* **Functional Testing:** Validation of the CV upload -> AI Analysis -> Ranking pipeline.
* **Performance Metrics:** Documenting API response times and AI processing duration.
* **Security Validation:** Audit logs for unauthorized attempts and SQL injection prevention testing.

## 5. Chapter 5: Conclusion & Reflection
* **Summary of Achievements:** Successful deployment of a secure, cloud-native recruitment analytics platform.
* **Limitations:** Addressing AI accuracy constraints and the use of synthetic data.
* **Future Scope:** Potential for full multi-tenancy and advanced ML model training.

---

## Technical Decision Log (Live Tracking)

Decision | Rationale | Outcome |

Migration to Docker | Eliminate XAMPP dependency & ensure Azure parity. | Successful environment isolation. |
Composer Integration | Modularize dependency management. | Enabled secure, automated library installs. |
Singleton Database | Standardize DB connection across the app. | Improved performance and code clarity. |


## Technical Decision Log (Live Tracking)

Decision | Rationale | Outcome |

 Migration to Docker | Eliminate XAMPP dependency & ensure Azure parity. | Successful environment isolation. |
 Composer Integration | Modularize dependency management. | Enabled secure, automated library installs. |
 Singleton Database | Standardize DB connection across the app. | Improved performance and code clarity. |
 Service Layer Adoption | Decoupled storage logic (Azure vs Local) into StorageService. | Enhanced system extensibility and maintainability. |
 Front Controller Pattern | Transitioned from legacy page-based routing to MVC architecture. | Centralized security, improved routing control, and cleaner URL structures. |

**By refactoring the business logic into distinct Model, View, and Controller (MVC) components, the system achieved a high level of Separation of Concerns (SoC).

--Key improvements: included the introduction of a StorageService to abstract cloud-based Azure Blob storage from local file handling, and the implementation of a Front Controller pattern. 
This allowed the application to consolidate routing logic into a single entry point, 
facilitating enhanced security controls and laying the groundwork for a scalable, production-ready system suitable for enterprise deployment on Azure."

** projects goes through several stages of Development, update sections are described below; 

## Project Update Documentation: Architecture Migration
1. Decoupling and Routing
Front Controller Pattern: Implemented a central routing entry point in public/index.php. This removes the need for individual PHP files to handle their own logic, improving maintainability and security.

Separation of Concerns: Successfully moved application logic from view files into the App\Controllers namespace, ensuring that views are now strictly for presentation.

2. Centralized Bootstrapping
Initialization Pipeline: Created bootstrap.php to handle the environment configuration, session management, and global helper functions. This ensures the application is in a known, stable state before any controller or model is executed.

Environment Agnostic Design: Utilized phpdotenv to load environment variables, facilitating seamless deployment across different environments (e.g., local development vs. Azure cloud).

3. Database Security & Infrastructure
Singleton Pattern: Implemented a robust Database.php class using the Singleton design pattern. This optimizes performance by maintaining a single, shared database connection throughout the request lifecycle.

Production-Grade Security:

Prepared Statements: Enforced PDO::ATTR_EMULATE_PREPARES => false to mitigate SQL injection risks.

SSL/TLS Enforcement: Configured Azure-specific SSL requirements (PDO::MYSQL_ATTR_SSL_CA) to ensure secure communication between the application and the database server.

4. Future-Proofing
Helper Utilities: Developed global functions like e() for context-aware output escaping (XSS prevention) and time_ago() for human-readable data presentation.




## Architectural Summary
To finalize your dissertation documentation for this stage, consider the flow we have established:

Request Flow: public/index.php (Router) → HomeController (Orchestrator).

Model Interaction: HomeController queries the Job model, which uses the BaseModel (Inheritance) to communicate with the Database (Singleton).

Data Presentation: The data is passed back to views/home/index.php, which remains purely focused on HTML/CSS.

** We have successfully built the Security Foundation (BaseController), the Authentication Layer (AuthController & Model), and the Router (public/index.php).


## update:
Architectural Migration
Summary of Changes:
Migrated the application from a procedural structure to a modern Model-View-Controller (MVC) design pattern. This shift was motivated by the need to centralize security controls and decouple business logic from presentation layers.

Key Technical Enhancements:

Centralized Security Middleware: Implemented a BaseController that enforces authentication globally. This eliminates the risk of "forgotten" authentication checks on new pages—a known vulnerability in the previous version.

Zero-Trust Data Access: Transitioned database operations to dedicated Model classes. This ensures that every data request is validated against the tenant's context, preventing cross-tenant data leakage.

Separation of Concerns: Decoupled business logic (Controllers) from UI presentation (Views). This improves code maintainability and facilitates future scalability.

Proactive Security Layering: Integrated CSRF validation and rate-limiting (velocity monitoring) directly into the Controller layer. This creates a proactive "gatekeeper" that blocks malicious requests before they interact with the database.



## update:
We have successfully transitioned your application from a scattered procedural monolith to a hardened MVC framework.

MVC Architecture: We implemented a clear separation of concerns using BaseController (infrastructure), JobController/DashboardController (business logic), and Job model (data access).

Zero-Trust Security: We moved from "per-file" security checks to an "architectural gatekeeper" in BaseController. Every request is now validated for session integrity and tenant state before logic is even executed.

Proactive Monitoring: We integrated the SecurityLogger directly into the authentication and request flow, ensuring that anomalies like session hijacking or unauthorized workspace access are logged for SIEM (Security Information and Event Management) analysis.


## update:
Project Status: MVC Architectural Migration
Accomplishments:

Hardened Infrastructure: Implemented a BaseController with Zero-Trust session pinning and dynamic tenant state validation.

Security Gates: Integrated proactive monitoring for velocity (rate-limiting) and CSRF protection on all data-mutation routes.

Architectural Refactor: Shifted from procedural file-based routing to a centralized MVC routing system using public/index.php.

UI/UX Consistency: Migrated legacy styling to a unified Layout system (header/footer) with modular CSS utility classes, ensuring visual and functional consistency.

Data Isolation: Enforced tenant-scoped queries across all model-level operations to prevent cross-tenant data leakage.


## update:
Fix: Resolved "Undefined array key" error in the recruitment dashboard by synchronizing controller data fetching with model logic.

Refactor: Migrated statistic retrieval from Recruiter model to Job model to ensure correct data mapping (total_jobs, active_jobs, total_applicants).

Security: Validated tenant_id session integrity checks for dashboard access.



## update:
You have built a secure, high-quality multi-tenant MVC framework core.

Backend Engine: Your centralized routing loop (Router.php), database singletons, and query abstraction tools (BaseModel.php) are complete and functioning correctly.

Zero-Trust Security: Your authentication handling is industry-grade. You have proactive session footprint tracking (user_agent and client_ip_hash validations) implemented natively into your BaseController lifecycle to block session-hijacking.

Unified Layout Wrapper: Your rendering logic cleanly splits presentation fragments from layout structures. The browser now receives structural components uniformly via main.php.


## update:
You have successfully built the "Engine Room" of your MVC framework. You have successfully navigated:

Routing & Request Dispatching: The system correctly maps URLs to Controller actions.

Controller Logic: You have built secure, rate-limited, and authenticated pathways.

Model Persistence: You have robust BaseModel and Job models that handle SQL prepared statements correctly.

Security Foundations: CSRF protection, data boundary gatekeeping, and error handling are now active.


** Analysis of Current Status
DONE: Recruiter Dashboard, Job Posting, CSRF protection, Database abstraction (BaseModel), and security logging.


## update:
Analysis of Current Status
Design System: Unified across Admin and Recruiter interfaces.

Authentication: Both login portals are functionally stable, secure, and styled consistently.

Data Input: The jobs/create interface is now ready to capture structured data (Location, Salary, Type).

Missing Core: We lack the Public-Facing Job Board (the landing page index) and the Application Flow (CV upload, candidate data).





## update:
Design & Theming
Unified Design System: Replaced fragmented inline styles with a centralized :root CSS variable system in main.css.

Modern Aesthetics: Integrated Google Fonts (Outfit & Inter) and implemented a dark-themed radial-gradient background with backdrop-filter effects.

Contrast Optimization: Resolved visual interference by standardizing surface colors, borders, and text contrasts, ensuring that data-heavy components (like tables) remain readable.

Authentication Layouts
Structural Uniformity: Standardized Admin and Recruiter login templates.

Layout Standardization: Utilized flexbox-based centering (min-height: 80vh) to ensure consistent "top-space" and centering across all screen sizes.

Functional Parity: Refactored form components (.form-control, .btn) to maintain exact visual dimensions across both login portals while preserving necessary security logic (CSRF tokens, form actions).

Recruitment Workflow (Job Posting)
Form Evolution: Transformed the basic job creation form into a structured, professional interface with grouped sections (Basic Info, Compensation, Location, Content).

Data Consistency: Standardized input types (Dropdowns, Range-ready inputs) to ensure high-quality data collection.

High-End Display: Updated the Job View interface to use a professional metadata bar with badge-styled indicators for Location, Salary, and Employment Type




## update:
You have successfully resolved the routing, authentication, and database persistence layers. 
Main dasboard index was implemented; job posted by recruiters will populate the main dashboard index where candidates can apply for jobs;



## update:
--phase 1: Core Architecture (COMPLETED)
Docker Environment: Web server, database, and PHPMyAdmin are fully containerized and communicating.

Routing Engine: The MVC router (index.php, Router.php, BaseController.php) cleanly separates public endpoints from protected recruiter workspaces.

Database & Persistence: The PDO wrapper (Database.php) and BaseModel.php are successfully communicating and resolving IDs.

Authentication & Isolation: Multi-tenant architecture is active. Recruiter sessions are protected, IP/User-Agent hijacking defenses are in place, and users can only see their own company's data.

--Phase 2: Job Board & Dashboard (COMPLETED)
Recruiter UI: The dashboard loads correctly, calculating live metrics (Total Jobs, Active Jobs) based on tenant ID.

Job Creation: The JobController::store method successfully validates data, checks rate limits, sanitizes inputs, and saves jobs to the database.

UI/UX: The dark-mode interface and form contrast issues have been stabilized.

--Phase 3: The Applicant Tracking System (NEXT)
Build ApplicantController.php: To intercept the "Apply" form submission.

Build StorageService.php: To securely connect to Azure Blob and upload the CV documents (PDF/DOCX).

Build the Candidate Review UI: The /applicants route needs a view for recruiters to download those CVs from Azure and review the candidates.

--Phase 4: Job Management Completion (PENDING)
Edit & Delete Jobs: We need to build the edit() and delete() methods in JobController.php so recruiters can manage their existing postings.

--Phase 5: Super Admin Portal (PENDING)
Tenant Management: The /admin routes are mapped, but we need to ensure the admin can suspend or activate corporate tenants to control platform access.




## update:
StorageService.php so it acts as the master switchboard, utilizing the AzureBlobStorage.php worker
zero Downtime: If your Azure subscription expires or the API goes down, ApplicantController won't crash.
 StorageService will automatically catch the Azure error, switch to local storage, 
 save the CV to the Docker container, and the candidate's application will still go through!
Storage Layer: Your StorageService.php is complete, featuring a primary cloud-first strategy with a reliable local filesystem fallback, now protected against file collisions.

Application Layer: Your ApplicantController.php handles multi-tenant authorization, 
file validation, and secures the process against BOLA (Broken Object Level Authorization) attacks.


** Creating a local fallback in your StorageService.php is a standard enterprise pattern known as Defensive Engineering or Tiered Availability. Even if your production destination is Azure, the local fallback is not "extra" work—it is an insurance policy.

By having a local fallback, you can work on the bus, on a plane, or during an internet outage without breaking your flow. 
You can test the logic of the upload pipeline without needing cloud infrastructure credentials.

Infrastructure Decoupling (The "Zero-Trust" Benefit):
If you ever need to switch cloud providers (e.g., from Azure to AWS or Google Cloud) 
or if Azure has a regional outage, your application does not stop working. 
The code is abstracted via the StorageService, meaning you only have to update the service layer,
 not every controller in your app.



## update:
Synchronized the global navigation layout with your existing framework route configuration.
 The Recruiter Portal now links directly to the independent /login endpoint managed by the AuthController, 
 while Admin Entry correctly targets /admin/login managed by the AdminController; 
more features added to main dashboard index;


## update:
core environment database and storage is completely migrated, stable, and working with Azure,


## update:
Recruiter Engine & Security Hardening
local AI INTEGRATION - gemini-2.5-flash

🤖 AI Recruiter Evaluation Refactoring
- **Structured Scannability Migration:** Updated the `worker.py` processing pipeline engine to enforce strict JSON schemas via the Google GenAI SDK typing configurations.
- **Recruiter UI Formatting Optimization:** Shifted the generic `gemini-2.5-flash` text block payload into targeted, actionable candidate evaluation blocks (`<h3>`, `<ul>`, `<li>`):
  1. 🌟 Core Strengths & Experience
  2. ⚠️ Key Gaps & Weak points
  3. 🎯 Structural Fit Assessment
- **File System Stability:** Relocated temp file unlinking operations down to the thread execution execution context blocks to guarantee I/O data sequence integrity.


## update:
Infrastructure Containerization & Portability Integration
- **Decoupled Python Worker Service:** Migrated the background queue processor out of the Windows host environment,
 and into a dedicated, isolated Docker container (`postyourjobuk-ai-worker-1`).
- **Multi-Container Architecture:** Updated `docker-compose.yml` to orchestrate both the PHP web server 
and the Python background daemon simultaneously, using shared environmental credentials (`.env`) and volume mappings.
- **Environment Isolation:** Standardized system runtime requirements via a dedicated `Dockerfile.worker` and `requirements.txt`, 
making the entire application 100% portable across any machine running Docker without requiring local language installations.



## update:
work done:
implementation of Standardized Page Architecture
Action: Created new instances of about.php, contact.php, terms.php, and vision.php


## Analysis of Work Accomplished
Your web application has matured into a sophisticated system with the following pillars:
a) Dockerized Infrastructure: By containerizing the environment, you have ensured "write once, run anywhere" 
parity between your local machine and the eventual Azure cloud deployment.
 This handles environment consistency, package dependencies, and server configuration.  
b) Persistent Cloud Data Layer: By incorporating Azure Database, 
 you have moved away from local, volatile storage to a managed, scalable relational database. 
 Your integration with Azure Blob Storage allows the platform to handle candidate documents
 (resumes, certifications) securely and at scale, separating heavy binary data from your primary database.  
c) Architectural Uniformity: Through the recent refactoring, 
 you have implemented a robust BaseController that acts as a central security gatekeeper, 
 ensuring all routes—public or private—adhere to strict authentication and tenancy standards. 
d) Design System Parity: You have moved from fragmented, inline-styled pages to a unified design system using main.css. 
 This ensures the UI remains consistent across all modules, including the Dashboard and new informational pages. 
e) AI Integration: Your current implementation of local AI capabilities has served as a foundational proof-of-concept 
 for automated screening and intelligent data processing


## Model-View-Controller (MVC)
The Current Workings of Your WebappYour application operates on a Model-View-Controller (MVC) pattern 
designed for enterprise scalability:Security Gatekeeping: 
Every request is intercepted by the BaseController constructor, which validates sessions and tenant activity before allowing access. 
Decoupled Logic: controllers (e.g., DashboardController, HomeController) focus solely on application flow,
while models manage the data interactions with your Azure Database, and views focus on rendering the UI via the global layout


## update:
Corporate Profile Dashboard Integration: Implemented a persistent "Business Card" section in the recruiter dashboard to display detailed tenant profile information, including industry, contact details, and headquarters.
Database-Level Tenant Data Retrieval: Optimized the Job model using SQL JOIN operations to securely and efficiently aggregate job postings with their corresponding tenant/company profile data for both internal and public-facing views.
Public Profile Transparency: Updated the job application view to include an "About the Employer" module.
Security & Data Integrity: Maintained strict multi-tenancy guards throughout the update.


## Update
Admin dashboard updates with sorting / filtering of tenants implemented; 

## Update
Security audit implementation is successfully completed:
Context-Aware Identity Attribution: We now capture the user_label (Company Name or Admin email) 
at the exact moment of every action. This ensures forensic integrity even after session destruction.

Proactive Threat Mitigation: Every authentication failure is logged with a specific reason code. 
This creates a data set for detecting brute-force attacks and supporting AI-driven threat monitoring.


## Update
Successfully implemented the dynamic severity badge system.
INFO events:
Categorization: If you trigger a WARNING (e.g., failed login), you will now see a Yellow badge.
Alerting: If you trigger a CRITICAL event (e.g., suspending a tenant), you will see a Red badge.



## going forward: 
shifting from a Synchronous/Local model to an Asynchronous/Cloud-Native model.
Current State:
 Your web app performs file processing within the same process that handles user requests. 
 This blocks the user and limits scalability.  Goal State: 
 The web app (Azure Web App) will receive a job/file and drop it into a queue (Azure Storage Queue). 
 An Azure Function will "trigger" on that message, 
 perform the heavy AI processing, and update the database, completely independent of the user's session





git status
git add .
git commit -m "......."
git push origin main



docker compose down
docker compose down --remove-orphans
docker network prune -f

docker compose up --build -d
docker compose up -d



C:\xampp\php_x86\php.exe -S localhost:8000 -t public

docker exec -it postyourjobuk-db-1 mysql -u root -p -e



http://localhost:8080/index.php?route=/&db=postyourjobhere

http://localhost:8000


http://localhost:8000/login

Email: recruiter@test.com
Password: password


Email: tech@postyourjobhere.com
Password: password

Email: test@company.com
Password: password


mycompany@jobs.com
1234567

test2@jobs.com
1234567

adrian@engine.com
919273
aabbcc

http://localhost:8000/admin/login
ADMIN_EMAIL=admin@postyourjobhere.com
ADMIN_PASSWORD_HASH= password




Azure cli
mysql -h hr-analytical-db.mysql.database.azure.com -u admindatabase -p'Gelutu@010380'

USE postyourjobhere;


cd C:\dev\postyourjobuk

"C:\Users\adita\AppData\Local\Programs\Python\Python313\python.exe" src/BackgroundWorkers/worker.py




## database:

Tables_in_postyourjobhere |
+---------------------------+
| applicants                |
| jobs                      |
| system_logs               |
| tenants   



DESCRIBE system_logs;
+---------------+--------------+------+-----+-------------------+-------------------+
| Field         | Type         | Null | Key | Default           | Extra             |
+---------------+--------------+------+-----+-------------------+-------------------+
| id            | bigint       | NO   | PRI | NULL              | auto_increment    |
| tenant_id     | int          | YES  |     | NULL              |                   |
| user_label    | varchar(255) | YES  |     | NULL              |                   |
| user_email    | varchar(255) | YES  |     | NULL              |                   |
| action_type   | varchar(50)  | YES  | MUL | NULL              |                   |
| severity      | varchar(20)  | YES  |     | INFO              |                   |
| resource_type | varchar(50)  | YES  |     | NULL              |                   |
| resource_id   | int          | YES  |     | NULL              |                   |
| ip_address    | varchar(45)  | YES  |     | NULL              |                   |
| details       | json         | YES  |     | NULL              |                   |
| created_at    | timestamp    | YES  | MUL | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+---------------+--------------+------+-----+-------------------+-------------------+
11 rows in set (0.034 sec)




DESCRIBE tenants;
+-----------------------+----------------------------+------+-----+-------------------+-----------------------------+
| Field                 | Type                       | Null | Key | Default           | Extra                       |
+-----------------------+----------------------------+------+-----+-------------------+-----------------------------+
| tenant_id             | int unsigned               | NO   | PRI | NULL              | auto_increment              |
| company_name          | varchar(100)               | NO   |     | NULL              |                             |
| contact_person        | varchar(100)               | YES  |     | NULL              |                             |
| phone_number          | varchar(20)                | YES  |     | NULL              |                             |
| website_url           | varchar(255)               | YES  |     | NULL              |                             |
| company_address       | text                       | YES  |     | NULL              |                             |
| industry              | varchar(100)               | YES  |     | NULL              |                             |
| is_active             | tinyint(1)                 | NO   | MUL | 1                 |                             |
| email                 | varchar(100)               | NO   | UNI | NULL              |                             |
| password_hash         | varchar(255)               | NO   |     | NULL              |                             |
| is_super_admin        | tinyint(1)                 | NO   | MUL | 0                 |                             |
| logo_url              | varchar(500)               | YES  |     | NULL              |                             |
| status                | enum('active','suspended') | YES  | MUL | active            |                             |
| created_at            | timestamp                  | NO   | MUL | CURRENT_TIMESTAMP | DEFAULT_GENERATED           |
| updated_at            | timestamp                  | YES  |     | NULL              | on update CURRENT_TIMESTAMP |
| last_login            | timestamp                  | YES  |     | NULL              |                             |
| failed_login_attempts | int                        | NO   |     | 0                 |                             |
| lockout_until         | datetime                   | YES  |     | NULL              |                             |
| deleted_at            | datetime                   | YES  |     | NULL              |                             |
| recovery_code         | varchar(16)                | YES  |     | NULL              |                             |
+-----------------------+----------------------------+------+-----+-------------------+-----------------------------+
20 rows in set (0.036 sec)




DESCRIBE jobs;
+-----------------+-------------------------------------------------------+------+-----+-------------------+-----------------------------------------------+
| Field           | Type                                                  | Null | Key | Default           | Extra                                         |
+-----------------+-------------------------------------------------------+------+-----+-------------------+-----------------------------------------------+
| job_id          | int unsigned                                          | NO   | PRI | NULL              | auto_increment                                |
| tenant_id       | int unsigned                                          | NO   | MUL | NULL              |                                               |
| title           | varchar(200)                                          | NO   |     | NULL              |                                               |
| description     | text                                                  | NO   |     | NULL              |                                               |
| requirements    | text                                                  | NO   |     | NULL              |                                               |
| location        | varchar(100)                                          | YES  |     | NULL              |                                               |
| salary          | varchar(50)                                           | YES  |     | NULL              |                                               |
| employment_type | enum('full-time','part-time','contract','internship') | YES  | MUL | full-time         |                                               |
| status          | enum('active','inactive','closed')                    | YES  | MUL | active            |                                               |
| created_at      | timestamp                                             | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED                             |
| updated_at      | timestamp                                             | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED on update CURRENT_TIMESTAMP |
| closed_at       | timestamp                                             | YES  |     | NULL              |                                               |
| is_active       | tinyint(1)                                            | NO   |     | 1                 |                                               |
+-----------------+-------------------------------------------------------+------+-----+-------------------+-----------------------------------------------+
13 rows in set (0.035 sec)

DESCRIBE applicants;
+-----------------+-------------------------------------------------+------+-----+-------------------+-------------------+
| Field           | Type                                            | Null | Key | Default           | Extra             |
+-----------------+-------------------------------------------------+------+-----+-------------------+-------------------+
| applicant_id    | int unsigned                                    | NO   | PRI | NULL              | auto_increment    |
| job_id          | int unsigned                                    | NO   | MUL | NULL              |                   |
| tenant_id       | int unsigned                                    | NO   | MUL | NULL              |                   |
| name            | varchar(100)                                    | NO   |     | NULL              |                   |
| email           | varchar(100)                                    | NO   | MUL | NULL              |                   |
| phone           | varchar(20)                                     | YES  |     | NULL              |                   |
| cv_filename     | varchar(255)                                    | NO   |     | NULL              |                   |
| cv_storage_path | varchar(500)                                    | NO   |     | NULL              |                   |
| cv_file_size    | int unsigned                                    | YES  |     | NULL              |                   |
| ai_score        | int unsigned                                    | YES  | MUL | 0                 |                   |
| ai_summary      | mediumtext                                      | YES  |     | NULL              |                   |
| ai_processed_at | timestamp                                       | YES  |     | NULL              |                   |
| status          | enum('new','reviewed','shortlisted','rejected') | NO   | MUL | new               |                   |
| applied_at      | timestamp                                       | NO   |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
| ip_address      | varchar(45)                                     | YES  |     | NULL              |                   |
| created_at      | timestamp                                       | YES  |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+-----------------+-------------------------------------------------+------+-----+-------------------+-------------------+



## Update
analytics implemented for recruiters and admin; 
files and charts necessary created; 


## project file structure at this point:

|   .env
|   .gitignore
|   bootstrap.php
|   composer.json
|   composer.lock
|   DigiCertGlobalRootG2.crt.pem
|   docker
|   docker-compose.yml
|   Dockerfile
|   Dockerfile.worker
|   live_azure_dump.sql
|   requirements.txt
|   SECURITY_LOGGING_README.md
|   TechnicalLog.md
|
+---.git
|   |   COMMIT_EDITMSG
|   |   config
|   |   description
|   |   HEAD
|   |   index

+---azure_function_project
|       function_app.py
|       host.json
|       requirements.txt
|
+---public
|   |   .htaccess
|   |   index.php
|   |
|   \---assets
|       \---css
|               main.css
|
+---src
|   +---BackgroundWorkers
|   |       worker.py
|   |
|   +---Controllers
|   |       AdminController.php
|   |       ApplicantController.php
|   |       AuditController.php
|   |       AuthController.php
|   |       Controller.php
|   |       DashboardController.php
|   |       HomeController.php
|   |       JobController.php
|   |
|   +---Core
|   |       AuditLogger.php
|   |       Auth.php
|   |       BaseController.php
|   |       BaseModel.php
|   |       Database.php
|   |       Helpers.php
|   |       Router.php
|   |       SecurityLogger.php
|   |
|   +---Middleware
|   |       AdminAuth.php
|   |
|   +---Models
|   |       Admin.php
|   |       Applicant.php
|   |       Job.php
|   |       Recruiter.php
|   |
|   +---Services
|   |       AzureBlobStorage.php
|   |       AzureQueueService.php
|   |       StorageService.php
|   |
|   \---views
|       +---admin
|       |       analytics.php
|       |       dashboard.php
|       |       edit_tenant.php
|       |       login.php
|       |       logs.php
|       |       view_tenant.php
|       |
|       +---applicants
|       |       list.php
|       |       view.php
|       |
|       +---auth
|       |       login.php
|       |       reset-password.php
|       |
|       +---dashboard
|       |       analytics.php
|       |       index.php
|       |
|       +---home
|       |       about.php
|       |       contact.php
|       |       index.php
|       |       terms.php
|       |       vision.php
|       |
|       +---jobs
|       |       create.php
|       |       details.php
|       |       edit.php
|       |
|       +---layouts
|       |       footer.php
|       |       header.php
|       |       login_layout.php
|       |       main.php
|       |
|       \---portal
|               dashboard.php
|
+---uploads
|   \---cvs




update:
implementing the RSS NEWS feed;
new files created for this: src/Models/NewsArticle.php; src/Controllers/NewsController.php;

updating database in azure: 

Tables_in_postyourjobhere |
+---------------------------+
| applicants                |
| jobs                      |
| news_articles             |
| system_logs               |
| tenants


describe news_articles;
+--------------+--------------+------+-----+-------------------+-------------------+
| Field        | Type         | Null | Key | Default           | Extra             |
+--------------+--------------+------+-----+-------------------+-------------------+
| article_id   | int          | NO   | PRI | NULL              | auto_increment    |
| title        | varchar(255) | NO   |     | NULL              |                   |
| link         | varchar(500) | NO   | UNI | NULL              |                   |
| description  | text         | YES  |     | NULL              |                   |
| source_name  | varchar(100) | NO   |     | NULL              |                   |
| published_at | datetime     | NO   |     | NULL              |                   |
| created_at   | timestamp    | YES  |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+--------------+--------------+------+-----+-------------------+-------------------+
7 rows in set (0.038 sec)


