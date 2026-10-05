PostYourJobUK - Platform Operations Guide

PostYourJobUK is a cloud-native, multi-tenant AI recruitment platform powered by a fully integrated ecosystem of Azure services (App Service, Container Registry, Blob Storage, Storage Queues, Serverless Functions, and MySQL Flexible Server), engineered to eliminate algorithmic bias and reduce SaaS costs for SMEs.

The Core Recruitment Cycle

Job Creation: A recruiter logs into their secure workspace and creates a new job posting.

Public Listing: The job immediately appears on the platform's public main index, making it searchable for all visitors.

Candidate Application: A candidate finds the listing and applies by uploading their CV (PDF or DOCX).

AI Evaluation: The platform securely routes the CV to Google Gemini AI, which evaluates the document against the job requirements and generates a bias-free score.

Recruiter Review: The candidate's profile and AI evaluation score automatically populate in the recruiter's dashboard under that specific job posting, allowing the recruiter to make data-driven hiring decisions.


Behind the Scenes: The Azure Infrastructure

To ensure the platform is highly scalable and never crashes during heavy processing, it utilizes a decoupled cloud architecture:

Azure Web App (Docker/PHP): Hosts the main frontend application where candidates browse jobs and recruiters manage their dashboards, deployed via Azure Container Registry.

Azure Blob Storage & Queues: When a candidate applies, their physical CV is securely saved in Blob Storage. An event message is then dropped into a Queue, allowing the Web App to instantly confirm the application without making the candidate wait for the AI to finish.

Azure Functions (Serverless): A Python background worker constantly monitors the Queue. When it sees a new application, it wakes up, retrieves the CV, and sends it to the Gemini AI API for evaluation.

Azure Database (MySQL Flexible Server): Stores all relational system data. Once the Azure Function receives the AI score, it saves the evaluation here so it can instantly populate the recruiter's dashboard.


Role Capabilities

Candidates (Public Users): Browse active jobs from validated recruiters, search for specific roles, apply by uploading a CV, read industry news, and submit inquiries via the contact page.

Recruiters (Tenants): Manage job postings (create, edit, delete), view the centralized list of applicants for their specific jobs, analyze the AI-generated CV scores, and delete candidate data when it is no longer needed.

Administrators: Provision new recruiter accounts, block or suspend existing recruiters (instantly hiding their jobs from the public), view system health logs, and administrate overarching platform operations.