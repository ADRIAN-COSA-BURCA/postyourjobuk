# Technical Log: Project postyourjobuk

This log serves as a detailed record of the architectural, design, and implementation decisions made throughout the project's SDLC. It is designed to support the dissertation chapters by documenting the "Why" and "How" behind the project's technical maturity.

---


adrian final year project disertation; 
final project for level 6 computing; 
analyse my project ; 
analyse below the work has been done for this project; 


Level 6 final project
Title that was chosen for this project: 50-70 words:
Next-Generation Recruitment Analytics: A Scalable, Multi-Tenant SaaS Platform Leveraging Azure Serverless Architecture and Event-Driven AI. Developing an enterprise-grade recruitment ecosystem that utilizes Azure App Service for scalable frontend delivery alongside Azure Functions to facilitate asynchronous, event-driven CV processing, while simultaneously leveraging Azure AI to interpret, rank, and select top-tier candidates to deliver real-time, data-driven insights.

Project Overview
•	What it does: Automates the processing and scoring of CVs against job descriptions, providing recruiters with instant, data-driven candidate rankings.
•	Structure: A hybrid-modular architecture consisting of a PHP-based web frontend and an event-driven "serverless" analytical backend.
•	Core Services & Connections:
o	Frontend (PHP on Azure App Service): Manages user interactions, job postings, and applicant tracking.
o	Analytical Engine (Azure Functions with Python): Acts as the "intelligence" layer, triggered automatically when a file is uploaded to perform deep-text analysis.
o	Data Storage (Azure Database for MySQL flexible servers): Central repository for job data, tenant information, and candidate rankings.
o	File Storage (Azure Blob Storage): A secure, cloud-based vault for storing CVs and resumes, replacing local server storage.

Azure’s Role
Azure acts as the "backbone" of your platform, providing:
1.	High Availability: Database and storage systems that are accessible 24/7.
2.	Serverless Processing: Azure Functions execute your Python AI code on-demand, ensuring costs are low and performance is high.
3.	Security & Scalability: Built-in enterprise-grade firewall protection and the ability to scale seamlessly as your user base grows toward 2027.



Summary of Workflow
1.	User Input: A candidate applies via your PHP portal.
2.	Data Persistence: PHP stores metadata in Azure Database for MySQL flexible servers
and the file in Azure Blob Storage.
3.	Automated Trigger: An event triggers an Azure Function.
4.	Intelligence: The Python service analyzes the document and computes a ranking.
5.	Output: The ranking is saved back to Azure Database for MySQL flexible servers
and displayed instantly on the recruiter's dashboard.










To ensure your project remains modular, scalable, and easy to transition from local development to your existing Azure infrastructure, you should treat your code as Environment Agnostic.
Here is the analysis and the strategy for your requirements, followed by the recommended development workflow.
1. Technical Requirements & Implementation Strategy
Requirement	Implementation Strategy
Modularity & OOP	Use a Repository Pattern or Service Layer. Your controllers should never contain raw SQL. Create a Database interface; locally, the implementation connects to XAMPP, while in Azure, it uses your MySQL Flexible Server credentials.
Scalability (UI)	Use CSS Flexbox and Grid. Define a standard gutter and unit system (e.g., --spacing-unit: 8px). Use media queries only to switch column counts, not to rewrite entire page logic.
Uniformity	Use a Template Engine (like Twig or simple PHP require blocks for header.php, footer.php, nav.php). Place all global styles in global.css and page-specific logic in page-name.css.
Azure Readiness	Use Environment Variables (.env). Never hardcode database connection strings. Your app should read these from the system environment. Locally, this is a .env file; in Azure, it is the "Configuration" blade in the App Service.
Code Simplicity	Adhere to the "Fat Models, Skinny Controllers" approach. Keep your PHP files clean by offloading logic to classes. If a file exceeds 120 lines, it is a signal to split it into a separate class or partial view.



Local vs. Direct Deployment Strategy
The Recommendation: Local-First Development.
Building directly in Azure will slow down your development cycle because of build times, deployment latency, and debugging challenges. A local-first approach using XAMPP is standard, provided you structure the code for easy migration.
Strategies to make switching to Azure "Super Easy":
1.	Abstraction of Services: Create a Config class that checks if it is running in Azure or Local.
o	Example: If $_SERVER['AZURE_ENVIRONMENT'] is set, use managed identity credentials; otherwise, use local MySQL user/pass.
2.	Relative Pathing: Never use absolute paths (e.g., C:\xampp\htdocs\...). Use relative paths (__DIR__ . '/../storage/cvs') to ensure your file system logic works on both Windows (local) and Linux (Azure App Service).
3.	Dockerize Early: Since you mentioned Docker, use it locally to replicate the Azure environment. By running your local MySQL and PHP inside containers (matching your Azure setup), you eliminate the "it works on XAMPP but not on Azure" problem.
4.	Blob Storage Wrapper: Create a simple PHP class StorageInterface. It should have methods like uploadFile() and getFile(). Initially, these methods write to a local folder. When you move to the cloud, you only update the class to use the Azure SDK for PHP to talk to Blob Storage. The rest of your application remains untouched.


Implementation Roadmap
•	Phase 1 (Design & Skeleton): Define your global CSS grid, color palette, and PHP base classes. Set up the local Docker environment to mimic your final cloud production environment.
•	Phase 2 (Core Logic): Develop the UI components (forms, dashboard, tables) using OOP. Implement the local storage/database interaction using your abstraction wrappers.
•	Phase 3 (Azure Integration): Swap the local storage/database drivers for the Azure SDK drivers. Since your code uses interfaces (e.g., StorageInterface), this is a seamless "plug-and-play" transition.
•	Phase 4 (Validation & Polish): Test responsiveness across breakpoints (320px for mobile, 768px for tablet, 1200px+ for desktop).








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

| Date | Decision | Rationale | Outcome |
| :--- | :--- | :--- | :--- |
| 2025-03-14 | Migration to Docker | Eliminate XAMPP dependency & ensure Azure parity. | Successful environment isolation. |
| 2025-03-14 | Composer Integration | Modularize dependency management. | Enabled secure, automated library installs. |
| 2025-03-14 | Singleton Database | Standardize DB connection across the app. | Improved performance and code clarity. |


## Technical Decision Log (Live Tracking)

| Date | Decision | Rationale | Outcome |
| :--- | :--- | :--- | :--- |
| 2025-03-14 | Migration to Docker | Eliminate XAMPP dependency & ensure Azure parity. | Successful environment isolation. |
| 2025-03-14 | Composer Integration | Modularize dependency management. | Enabled secure, automated library installs. |
| 2025-03-14 | Singleton Database | Standardize DB connection across the app. | Improved performance and code clarity. |
| 2025-06-08 | Service Layer Adoption | Decoupled storage logic (Azure vs Local) into StorageService. | Enhanced system extensibility and maintainability. |
| 2025-06-08 | Front Controller Pattern | Transitioned from legacy page-based routing to MVC architecture. | Centralized security, improved routing control, and cleaner URL structures. |

By refactoring the business logic into distinct Model, View, and Controller (MVC) components, the system achieved a high level of Separation of Concerns (SoC).

Key improvements included the introduction of a StorageService to abstract cloud-based Azure Blob storage from local file handling, and the implementation of a Front Controller pattern. 
This allowed the application to consolidate routing logic into a single entry point, 
facilitating enhanced security controls and laying the groundwork for a scalable, production-ready system suitable for enterprise deployment on Azure."


Project Update Documentation: Architecture Migration
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





Up until now, we have focused on Infrastructure:

Routing: (public/index.php) – Traffic is handled centrally.

Logic: (HomeController) – Requests are processed cleanly.

Persistence: (Database.php + bootstrap.php) – Secure, singleton-based connectivity is ready.

Presentation: (views/home/index.php) – The UI is separated from the logic



Architectural Summary
To finalize your dissertation documentation for this stage, consider the flow we have established:

Request Flow: public/index.php (Router) → HomeController (Orchestrator).

Model Interaction: HomeController queries the Job model, which uses the BaseModel (Inheritance) to communicate with the Database (Singleton).

Data Presentation: The data is passed back to views/home/index.php, which remains purely focused on HTML/CSS.


We have successfully built the Security Foundation (BaseController), the Authentication Layer (AuthController & Model), and the Router (public/index.php).


update:
Architectural Migration
Summary of Changes:
Migrated the application from a procedural structure to a modern Model-View-Controller (MVC) design pattern. This shift was motivated by the need to centralize security controls and decouple business logic from presentation layers.

Key Technical Enhancements:

Centralized Security Middleware: Implemented a BaseController that enforces authentication globally. This eliminates the risk of "forgotten" authentication checks on new pages—a known vulnerability in the previous version.

Zero-Trust Data Access: Transitioned database operations to dedicated Model classes. This ensures that every data request is validated against the tenant's context, preventing cross-tenant data leakage.

Separation of Concerns: Decoupled business logic (Controllers) from UI presentation (Views). This improves code maintainability and facilitates future scalability.

Proactive Security Layering: Integrated CSRF validation and rate-limiting (velocity monitoring) directly into the Controller layer. This creates a proactive "gatekeeper" that blocks malicious requests before they interact with the database.



update:
We have successfully transitioned your application from a scattered procedural monolith to a hardened MVC framework.

MVC Architecture: We implemented a clear separation of concerns using BaseController (infrastructure), JobController/DashboardController (business logic), and Job model (data access).

Zero-Trust Security: We moved from "per-file" security checks to an "architectural gatekeeper" in BaseController. Every request is now validated for session integrity and tenant state before logic is even executed.

Proactive Monitoring: We integrated the SecurityLogger directly into the authentication and request flow, ensuring that anomalies like session hijacking or unauthorized workspace access are logged for SIEM (Security Information and Event Management) analysis.


update:
Project Status: MVC Architectural Migration
Accomplishments:

Hardened Infrastructure: Implemented a BaseController with Zero-Trust session pinning and dynamic tenant state validation.

Security Gates: Integrated proactive monitoring for velocity (rate-limiting) and CSRF protection on all data-mutation routes.

Architectural Refactor: Shifted from procedural file-based routing to a centralized MVC routing system using public/index.php.

UI/UX Consistency: Migrated legacy styling to a unified Layout system (header/footer) with modular CSS utility classes, ensuring visual and functional consistency.

Data Isolation: Enforced tenant-scoped queries across all model-level operations to prevent cross-tenant data leakage.


update:

Fix: Resolved "Undefined array key" error in the recruitment dashboard by synchronizing controller data fetching with model logic.

Refactor: Migrated statistic retrieval from Recruiter model to Job model to ensure correct data mapping (total_jobs, active_jobs, total_applicants).

Security: Validated tenant_id session integrity checks for dashboard access.



update:
You have built a secure, high-quality multi-tenant MVC framework core.

Backend Engine: Your centralized routing loop (Router.php), database singletons, and query abstraction tools (BaseModel.php) are complete and functioning correctly.

Zero-Trust Security: Your authentication handling is industry-grade. You have proactive session footprint tracking (user_agent and client_ip_hash validations) implemented natively into your BaseController lifecycle to block session-hijacking.

Unified Layout Wrapper: Your rendering logic cleanly splits presentation fragments from layout structures. The browser now receives structural components uniformly via main.php.


update:

You have successfully built the "Engine Room" of your MVC framework. You have successfully navigated:

Routing & Request Dispatching: The system correctly maps URLs to Controller actions.

Controller Logic: You have built secure, rate-limited, and authenticated pathways.

Model Persistence: You have robust BaseModel and Job models that handle SQL prepared statements correctly.

Security Foundations: CSRF protection, data boundary gatekeeping, and error handling are now active.

Analysis of Current Status
DONE: Recruiter Dashboard, Job Posting, CSRF protection, Database abstraction (BaseModel), and security logging.

BROKEN/INCOMPLETE:

Database Helper Methods: You currently have Fatal errors because Database.php lacks the fetchOne, fetchAll, etc., wrapper methods needed by BaseModel.

Public Job Board: There is no "Front Door" for candidates to view jobs.

Application Flow: You cannot yet test the "Applicants" list because no users have applied yet.



update:
Analysis of Current Status
Design System: Unified across Admin and Recruiter interfaces.

Authentication: Both login portals are functionally stable, secure, and styled consistently.

Data Input: The jobs/create interface is now ready to capture structured data (Location, Salary, Type).

Missing Core: We lack the Public-Facing Job Board (the landing page index) and the Application Flow (CV upload, candidate data).





update:
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




UPDATE:
You have successfully resolved the routing, authentication, and database persistence layers. 
Main dasboard index was implemented; job posted by recruiters will populate the main dashboard index where candidates can apply for jobs;



update:
hase 1: Core Architecture (COMPLETED)
Docker Environment: Web server, database, and PHPMyAdmin are fully containerized and communicating.

Routing Engine: The MVC router (index.php, Router.php, BaseController.php) cleanly separates public endpoints from protected recruiter workspaces.

Database & Persistence: The PDO wrapper (Database.php) and BaseModel.php are successfully communicating and resolving IDs.

Authentication & Isolation: Multi-tenant architecture is active. Recruiter sessions are protected, IP/User-Agent hijacking defenses are in place, and users can only see their own company's data.

✅ Phase 2: Job Board & Dashboard (COMPLETED)
Recruiter UI: The dashboard loads correctly, calculating live metrics (Total Jobs, Active Jobs) based on tenant ID.

Job Creation: The JobController::store method successfully validates data, checks rate limits, sanitizes inputs, and saves jobs to the database.

UI/UX: The dark-mode interface and form contrast issues have been stabilized.

🚧 Phase 3: The Applicant Tracking System (NEXT)
Build ApplicantController.php: To intercept the "Apply" form submission.

Build StorageService.php: To securely connect to Azure Blob and upload the CV documents (PDF/DOCX).

Build the Candidate Review UI: The /applicants route needs a view for recruiters to download those CVs from Azure and review the candidates.

🚧 Phase 4: Job Management Completion (PENDING)
Edit & Delete Jobs: We need to build the edit() and delete() methods in JobController.php so recruiters can manage their existing postings.

🚧 Phase 5: Super Admin Portal (PENDING)
Tenant Management: The /admin routes are mapped, but we need to ensure the admin can suspend or activate corporate tenants to control platform access.




update:
StorageService.php so it acts as the master switchboard, utilizing the AzureBlobStorage.php worker
ero Downtime: If your Azure subscription expires or the API goes down, ApplicantController won't crash.
 StorageService will automatically catch the Azure error, switch to local storage, 
 save the CV to the Docker container, and the candidate's application will still go through!
Storage Layer: Your StorageService.php is complete, featuring a primary cloud-first strategy with a reliable local filesystem fallback, now protected against file collisions.

Application Layer: Your ApplicantController.php handles multi-tenant authorization, file validation, and secures the process against BOLA (Broken Object Level Authorization) attacks.

View Layer: Your details.php handles form state and provides a secure user experience.


Creating a local fallback in your StorageService.php is a standard enterprise pattern known as Defensive Engineering or Tiered Availability. Even if your production destination is Azure, the local fallback is not "extra" work—it is an insurance policy.

Here is why this is the best architectural choice for your project:

By having a local fallback, you can work on the bus, on a plane, or during an internet outage without breaking your flow. You can test the logic of the upload pipeline without needing cloud infrastructure credentials.

Infrastructure Decoupling (The "Zero-Trust" Benefit):

If you ever need to switch cloud providers (e.g., from Azure to AWS or Google Cloud) or if Azure has a regional outage, your application does not stop working. The code is abstracted via the StorageService, meaning you only have to update the service layer, not every controller in your app.





git status
git add .
git commit -m "......."
git push origin main



docker compose down
docker compose up --build -d
docker compose up -d



C:\xampp\php_x86\php.exe -S localhost:8000 -t public

docker exec -it postyourjobuk-db-1 mysql -u root -p -e



http://localhost:8080/index.php?route=/&db=postyourjobhere

http://localhost:8000


http://localhost:8000/login
Email: recruiter@test.com
Password: password


http://localhost:8000/admin/login
ADMIN_EMAIL=admin@postyourjobhere.com
ADMIN_PASSWORD_HASH= password







file structure at this point:

C:.
|   .env
|   .gitignore
|   azure_database.sql
|   bootstrap.php
|   composer.json
|   composer.lock
|   DigiCertGlobalRootG2.crt.pem
|   docker-compose.yml
|   Dockerfile
|   TechnicalLog.md
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
|   +---Controllers
|   |       AdminController.php
|   |       ApplicantController.php
|   |       AuthController.php
|   |       Controller.php
|   |       DashboardController.php
|   |       HomeController.php
|   |       JobController.php
|   |
|   +---Core
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
|   |       StorageService.php
|   |
|   \---views
|       +---admin
|       |       dashboard.php
|       |       login.php
|       |       view_tenant.php
|       |
|       +---auth
|       |       login.php
|       |
|       +---dashboard
|       |       index.php
|       |
|       +---jobs
|       |       create.php
|       |       details.php
|       |
|       +---layouts
|       |       footer.php
|       |       header.php
|       |
|       \---portal
|               dashboard.php
|
+---vendor
|   |   autoload.php
|   |
|   +---composer
|   |       autoload_classmap.php
|   |       autoload_files.php
|   |       autoload_namespaces.php
|   |       autoload_psr4.php
|   |       autoload_real.php
|   |       autoload_static.php
|   |       ClassLoader.php
|   |       installed.json
|   |       installed.php
|   |       InstalledVersions.php
|   |       LICENSE
|   |       platform_check.php
|   |
|   +---graham-campbell
|   |   \---result-type
|   |       |   composer.json
|   |       |   LICENSE
|   |       |
|   |       \---src
|   |               Error.php
|   |               Result.php
|   |               Success.php
|   |
|   +---guzzlehttp
|   |   +---guzzle
|   |   |   |   CHANGELOG.md
|   |   |   |   composer.json
|   |   |   |   LICENSE
|   |   |   |   package-lock.json
|   |   |   |   README.md
|   |   |   |   UPGRADING.md
|   |   |   |
|   |   |   \---src
|   |   |       |   BodySummarizer.php
|   |   |       |   BodySummarizerInterface.php
|   |   |       |   Client.php
|   |   |       |   ClientInterface.php
|   |   |       |   ClientTrait.php
|   |   |       |   functions.php
|   |   |       |   functions_include.php
|   |   |       |   HandlerStack.php
|   |   |       |   MessageFormatter.php
|   |   |       |   MessageFormatterInterface.php
|   |   |       |   Middleware.php
|   |   |       |   Pool.php
|   |   |       |   PrepareBodyMiddleware.php
|   |   |       |   RedirectMiddleware.php
|   |   |       |   RequestOptions.php
|   |   |       |   RetryMiddleware.php
|   |   |       |   TransferStats.php
|   |   |       |   TransportSharing.php
|   |   |       |   Utils.php
|   |   |       |
|   |   |       +---Cookie
|   |   |       |       CookieJar.php
|   |   |       |       CookieJarInterface.php
|   |   |       |       FileCookieJar.php
|   |   |       |       SessionCookieJar.php
|   |   |       |       SetCookie.php
|   |   |       |
|   |   |       +---Exception
|   |   |       |       BadResponseException.php
|   |   |       |       ClientException.php
|   |   |       |       ConnectException.php
|   |   |       |       GuzzleException.php
|   |   |       |       InvalidArgumentException.php
|   |   |       |       RequestException.php
|   |   |       |       ServerException.php
|   |   |       |       TooManyRedirectsException.php
|   |   |       |       TransferException.php
|   |   |       |
|   |   |       \---Handler
|   |   |               CurlFactory.php
|   |   |               CurlFactoryInterface.php
|   |   |               CurlHandler.php
|   |   |               CurlMultiHandler.php
|   |   |               CurlShareHandleState.php
|   |   |               EasyHandle.php
|   |   |               HeaderProcessor.php
|   |   |               MockHandler.php
|   |   |               Proxy.php
|   |   |               StreamHandler.php
|   |   |
|   |   +---promises
|   |   |   |   CHANGELOG.md
|   |   |   |   composer.json
|   |   |   |   LICENSE
|   |   |   |   README.md
|   |   |   |   UPGRADING.md
|   |   |   |
|   |   |   \---src
|   |   |           AggregateException.php
|   |   |           CancellationException.php
|   |   |           Coroutine.php
|   |   |           Create.php
|   |   |           Each.php
|   |   |           EachPromise.php
|   |   |           FulfilledPromise.php
|   |   |           Is.php
|   |   |           Promise.php
|   |   |           PromiseInterface.php
|   |   |           PromisorInterface.php
|   |   |           RejectedPromise.php
|   |   |           RejectionException.php
|   |   |           TaskQueue.php
|   |   |           TaskQueueInterface.php
|   |   |           Utils.php
|   |   |
|   |   \---psr7
|   |       |   CHANGELOG.md
|   |       |   composer.json
|   |       |   LICENSE
|   |       |   README.md
|   |       |   UPGRADING.md
|   |       |
|   |       \---src
|   |           |   AppendStream.php
|   |           |   BufferStream.php
|   |           |   CachingStream.php
|   |           |   DroppingStream.php
|   |           |   FnStream.php
|   |           |   Header.php
|   |           |   HttpFactory.php
|   |           |   InflateStream.php
|   |           |   LazyOpenStream.php
|   |           |   LimitStream.php
|   |           |   Message.php
|   |           |   MessageTrait.php
|   |           |   MimeType.php
|   |           |   MultipartStream.php
|   |           |   NoSeekStream.php
|   |           |   PumpStream.php
|   |           |   Query.php
|   |           |   Request.php
|   |           |   Response.php
|   |           |   Rfc3986.php
|   |           |   Rfc7230.php
|   |           |   ServerRequest.php
|   |           |   Stream.php
|   |           |   StreamDecoratorTrait.php
|   |           |   StreamWrapper.php
|   |           |   UploadedFile.php
|   |           |   Uri.php
|   |           |   UriComparator.php
|   |           |   UriNormalizer.php
|   |           |   UriResolver.php
|   |           |   Utils.php
|   |           |
|   |           \---Exception
|   |                   MalformedUriException.php
|   |
|   +---microsoft
|   |   +---azure-storage-blob
|   |   |   |   BreakingChanges.md
|   |   |   |   ChangeLog.md
|   |   |   |   composer.json
|   |   |   |   CONTRIBUTING.md
|   |   |   |   LICENSE
|   |   |   |   README.md
|   |   |   |
|   |   |   \---src
|   |   |       \---Blob
|   |   |           |   BlobRestProxy.php
|   |   |           |   BlobSharedAccessSignatureHelper.php
|   |   |           |
|   |   |           +---Internal
|   |   |           |       BlobResources.php
|   |   |           |       IBlob.php
|   |   |           |
|   |   |           \---Models
|   |   |                   AccessCondition.php
|   |   |                   AccessTierTrait.php
|   |   |                   AppendBlockOptions.php
|   |   |                   AppendBlockResult.php
|   |   |                   Blob.php
|   |   |                   BlobAccessPolicy.php
|   |   |                   BlobBlockType.php
|   |   |                   BlobPrefix.php
|   |   |                   BlobProperties.php
|   |   |                   BlobServiceOptions.php
|   |   |                   BlobType.php
|   |   |                   Block.php
|   |   |                   BlockList.php
|   |   |                   BreakLeaseResult.php
|   |   |                   CommitBlobBlocksOptions.php
|   |   |                   Container.php
|   |   |                   ContainerAccessPolicy.php
|   |   |                   ContainerACL.php
|   |   |                   ContainerProperties.php
|   |   |                   CopyBlobFromURLOptions.php
|   |   |                   CopyBlobOptions.php
|   |   |                   CopyBlobResult.php
|   |   |                   CopyState.php
|   |   |                   CreateBlobBlockOptions.php
|   |   |                   CreateBlobOptions.php
|   |   |                   CreateBlobPagesOptions.php
|   |   |                   CreateBlobPagesResult.php
|   |   |                   CreateBlobSnapshotOptions.php
|   |   |                   CreateBlobSnapshotResult.php
|   |   |                   CreateBlockBlobOptions.php
|   |   |                   CreateContainerOptions.php
|   |   |                   CreatePageBlobFromContentOptions.php
|   |   |                   CreatePageBlobOptions.php
|   |   |                   DeleteBlobOptions.php
|   |   |                   GetBlobMetadataOptions.php
|   |   |                   GetBlobMetadataResult.php
|   |   |                   GetBlobOptions.php
|   |   |                   GetBlobPropertiesOptions.php
|   |   |                   GetBlobPropertiesResult.php
|   |   |                   GetBlobResult.php
|   |   |                   GetContainerACLResult.php
|   |   |                   GetContainerPropertiesResult.php
|   |   |                   LeaseMode.php
|   |   |                   LeaseResult.php
|   |   |                   ListBlobBlocksOptions.php
|   |   |                   ListBlobBlocksResult.php
|   |   |                   ListBlobsOptions.php
|   |   |                   ListBlobsResult.php
|   |   |                   ListContainersOptions.php
|   |   |                   ListContainersResult.php
|   |   |                   ListPageBlobRangesDiffResult.php
|   |   |                   ListPageBlobRangesOptions.php
|   |   |                   ListPageBlobRangesResult.php
|   |   |                   PageWriteOption.php
|   |   |                   PublicAccessType.php
|   |   |                   PutBlobResult.php
|   |   |                   PutBlockResult.php
|   |   |                   SetBlobMetadataResult.php
|   |   |                   SetBlobPropertiesOptions.php
|   |   |                   SetBlobPropertiesResult.php
|   |   |                   SetBlobTierOptions.php
|   |   |                   UndeleteBlobOptions.php
|   |   |
|   |   \---azure-storage-common
|   |       |   BreakingChanges.md
|   |       |   ChangeLog.md
|   |       |   composer.json
|   |       |   CONTRIBUTING.md
|   |       |   LICENSE
|   |       |   README.md
|   |       |
|   |       \---src
|   |           \---Common
|   |               |   CloudConfigurationManager.php
|   |               |   LocationMode.php
|   |               |   Logger.php
|   |               |   MarkerContinuationTokenTrait.php
|   |               |   SharedAccessSignatureHelper.php
|   |               |
|   |               +---Exceptions
|   |               |       InvalidArgumentTypeException.php
|   |               |       ServiceException.php
|   |               |
|   |               +---Internal
|   |               |   |   ACLBase.php
|   |               |   |   ConnectionStringParser.php
|   |               |   |   ConnectionStringSource.php
|   |               |   |   MetadataTrait.php
|   |               |   |   Resources.php
|   |               |   |   RestProxy.php
|   |               |   |   ServiceRestProxy.php
|   |               |   |   ServiceRestTrait.php
|   |               |   |   ServiceSettings.php
|   |               |   |   StorageServiceSettings.php
|   |               |   |   Utilities.php
|   |               |   |   Validate.php
|   |               |   |
|   |               |   +---Authentication
|   |               |   |       IAuthScheme.php
|   |               |   |       SharedAccessSignatureAuthScheme.php
|   |               |   |       SharedKeyAuthScheme.php
|   |               |   |       TokenAuthScheme.php
|   |               |   |
|   |               |   +---Http
|   |               |   |       HttpCallContext.php
|   |               |   |       HttpFormatter.php
|   |               |   |
|   |               |   +---Middlewares
|   |               |   |       CommonRequestMiddleware.php
|   |               |   |
|   |               |   \---Serialization
|   |               |           ISerializer.php
|   |               |           JsonSerializer.php
|   |               |           MessageSerializer.php
|   |               |           XmlSerializer.php
|   |               |
|   |               +---Middlewares
|   |               |       HistoryMiddleware.php
|   |               |       IMiddleware.php
|   |               |       MiddlewareBase.php
|   |               |       MiddlewareStack.php
|   |               |       RetryMiddleware.php
|   |               |       RetryMiddlewareFactory.php
|   |               |
|   |               \---Models
|   |                       AccessPolicy.php
|   |                       ContinuationToken.php
|   |                       CORS.php
|   |                       GetServicePropertiesResult.php
|   |                       GetServiceStatsResult.php
|   |                       Logging.php
|   |                       MarkerContinuationToken.php
|   |                       Metrics.php
|   |                       Range.php
|   |                       RangeDiff.php
|   |                       RetentionPolicy.php
|   |                       ServiceOptions.php
|   |                       ServiceProperties.php
|   |                       SignedIdentifier.php
|   |                       TransactionalMD5Trait.php
|   |
|   +---phpoption
|   |   \---phpoption
|   |       |   composer.json
|   |       |   LICENSE
|   |       |
|   |       \---src
|   |           \---PhpOption
|   |                   LazyOption.php
|   |                   None.php
|   |                   Option.php
|   |                   Some.php
|   |
|   +---psr
|   |   +---http-client
|   |   |   |   CHANGELOG.md
|   |   |   |   composer.json
|   |   |   |   LICENSE
|   |   |   |   README.md
|   |   |   |
|   |   |   \---src
|   |   |           ClientExceptionInterface.php
|   |   |           ClientInterface.php
|   |   |           NetworkExceptionInterface.php
|   |   |           RequestExceptionInterface.php
|   |   |
|   |   +---http-factory
|   |   |   |   composer.json
|   |   |   |   LICENSE
|   |   |   |   README.md
|   |   |   |
|   |   |   \---src
|   |   |           RequestFactoryInterface.php
|   |   |           ResponseFactoryInterface.php
|   |   |           ServerRequestFactoryInterface.php
|   |   |           StreamFactoryInterface.php
|   |   |           UploadedFileFactoryInterface.php
|   |   |           UriFactoryInterface.php
|   |   |
|   |   \---http-message
|   |       |   CHANGELOG.md
|   |       |   composer.json
|   |       |   LICENSE
|   |       |   README.md
|   |       |
|   |       +---docs
|   |       |       PSR7-Interfaces.md
|   |       |       PSR7-Usage.md
|   |       |
|   |       \---src
|   |               MessageInterface.php
|   |               RequestInterface.php
|   |               ResponseInterface.php
|   |               ServerRequestInterface.php
|   |               StreamInterface.php
|   |               UploadedFileInterface.php
|   |               UriInterface.php
|   |
|   +---ralouphie
|   |   \---getallheaders
|   |       |   composer.json
|   |       |   LICENSE
|   |       |   README.md
|   |       |
|   |       \---src
|   |               getallheaders.php
|   |
|   +---symfony
|   |   +---deprecation-contracts
|   |   |       CHANGELOG.md
|   |   |       composer.json
|   |   |       function.php
|   |   |       LICENSE
|   |   |       README.md
|   |   |
|   |   +---polyfill-ctype
|   |   |       bootstrap.php
|   |   |       bootstrap80.php
|   |   |       composer.json
|   |   |       Ctype.php
|   |   |       LICENSE
|   |   |       README.md
|   |   |
|   |   +---polyfill-mbstring
|   |   |   |   bootstrap.php
|   |   |   |   bootstrap72.php
|   |   |   |   bootstrap80.php
|   |   |   |   composer.json
|   |   |   |   LICENSE
|   |   |   |   Mbstring.php
|   |   |   |   README.md
|   |   |   |
|   |   |   \---Resources
|   |   |       \---unidata
|   |   |               caseFolding.php
|   |   |               lowerCase.php
|   |   |               titleCaseRegexp.php
|   |   |               upperCase.php
|   |   |
|   |   \---polyfill-php80
|   |       |   bootstrap.php
|   |       |   composer.json
|   |       |   LICENSE
|   |       |   Php80.php
|   |       |   PhpToken.php
|   |       |   README.md
|   |       |
|   |       \---Resources
|   |           \---stubs
|   |                   Attribute.php
|   |                   PhpToken.php
|   |                   Stringable.php
|   |                   UnhandledMatchError.php
|   |                   ValueError.php
|   |
|   \---vlucas
|       \---phpdotenv
|           |   composer.json
|           |   LICENSE
|           |
|           \---src
|               |   Dotenv.php
|               |   Validator.php
|               |
|               +---Exception
|               |       ExceptionInterface.php
|               |       InvalidEncodingException.php
|               |       InvalidFileException.php
|               |       InvalidPathException.php
|               |       ValidationException.php
|               |
|               +---Loader
|               |       Loader.php
|               |       LoaderInterface.php
|               |       Resolver.php
|               |
|               +---Parser
|               |       Entry.php
|               |       EntryParser.php
|               |       Lexer.php
|               |       Lines.php
|               |       Parser.php
|               |       ParserInterface.php
|               |       Value.php
|               |
|               +---Repository
|               |   |   AdapterRepository.php
|               |   |   RepositoryBuilder.php
|               |   |   RepositoryInterface.php
|               |   |
|               |   \---Adapter
|               |           AdapterInterface.php
|               |           ApacheAdapter.php
|               |           ArrayAdapter.php
|               |           EnvConstAdapter.php
|               |           GuardedWriter.php
|               |           ImmutableWriter.php
|               |           MultiReader.php
|               |           MultiWriter.php
|               |           PutenvAdapter.php
|               |           ReaderInterface.php
|               |           ReplacingWriter.php
|               |           ServerConstAdapter.php
|               |           WriterInterface.php
|               |
|               +---Store
|               |   |   FileStore.php
|               |   |   StoreBuilder.php
|               |   |   StoreInterface.php
|               |   |   StringStore.php
|               |   |
|               |   \---File
|               |           Paths.php
|               |           Reader.php
|               |
|               \---Util
|                       Regex.php
|                       Str.php
|
\---views
    +---auth
    \---home
            index.php