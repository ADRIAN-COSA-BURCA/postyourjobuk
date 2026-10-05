<div class="container page-wrapper">

    <p class="about-eyebrow">About the Platform</p>
    <h1 class="about-title">The Engineering Behind postyourjobuk</h1>
    <p class="about-lead">
        Postyourjobuk is a cloud-native, multi-tenant AI recruitment SaaS ecosystem engineered as an academic dissertation project. It was designed to solve two critical industry challenges: prohibitive multi-tenant subscription costs for SMEs and black-box algorithmic bias in automated resume screening.
    </p>
    <hr class="about-divider">

    <div class="about-grid">

        <div class="card">
            <h4 class="about-card-title">Multi-Tenant Cloud Isolation</h4>
            <p class="about-card-text">
                Built on a custom PHP 8.3 MVC architecture with strict dynamic tenant scoping, Zero-Trust session fingerprinting, and isolated workspace data boundaries.
            </p>
        </div>

        <div class="card">
            <h4 class="about-card-title">Algorithmic Bias Mitigation</h4>
            <p class="about-card-text">
                Candidate CVs undergo regex PII redaction before evaluation using a deterministic 100-baseline subtractive rubric via Google Gemini 2.5 Flash.
            </p>
        </div>

        <div class="card">
            <h4 class="about-card-title">Event-Driven Asynchronous Pipeline</h4>
            <p class="about-card-text">
                Web ingestion is decoupled from AI compute using Azure Storage Queues and serverless Azure Functions, guaranteeing zero user-facing page timeouts.
            </p>
        </div>

        <div class="card">
            <h4 class="about-card-title">Transparent & Auditable</h4>
            <p class="about-card-text">
                Every AI evaluation generates a SHA-256 cryptographic audit fingerprint and structured HTML feedback, empowering recruiters with complete explainability.
            </p>
        </div>

    </div>

    <div class="about-quote">
        <p>"	Postyourjobuk was engineered to demonstrate that automated AI recruitment can be low-cost, scalable, and algorithmically transparent."</p>
    </div>

</div>