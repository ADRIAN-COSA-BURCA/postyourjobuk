# Technical Log: Project postyourjobuk

This log serves as a detailed record of the architectural, design, and implementation decisions made throughout the project's SDLC. It is designed to support the dissertation chapters by documenting the "Why" and "How" behind the project's technical maturity.

---

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



