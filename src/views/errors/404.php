<div class="container" style="max-width: 650px; margin: 4rem auto 6rem auto; text-align: center;">
    <div class="card" style="padding: 3.5rem 2.5rem; border: 1px solid rgba(139, 92, 246, 0.35); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6); border-radius: 16px; background: #111827;">
        <div style="font-size: 3.5rem; line-height: 1; margin-bottom: 1.25rem;">🔍</div>
        <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3); font-size: 0.85rem; margin-bottom: 1.25rem; padding: 0.35rem 0.85rem; display: inline-block;">
            Error 404 — Page Not Found
        </span>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.9rem; color: #ffffff; margin-bottom: 0.85rem; font-weight: 700;">
            We couldn't find that page
        </h1>
        <p style="color: var(--text-muted); font-size: 0.98rem; line-height: 1.6; margin-bottom: 2rem;">
            The link you followed may be expired, mistyped, or the page may have been moved.
        </p>
        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
            <a href="/" class="btn btn-primary" style="padding: 0.75rem 1.5rem; text-decoration: none;">
                🧭 Explore Available Jobs
            </a>
            <?php if (!empty($_SESSION['tenant_id'])): ?>
                <a href="/dashboard" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; text-decoration: none;">
                    📊 Recruiter Dashboard
                </a>
            <?php elseif (!empty($_SESSION['admin_logged_in'])): ?>
                <a href="/admin" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; text-decoration: none;">
                    ⚡ Admin Portal
                </a>
            <?php else: ?>
                <a href="/login" class="btn btn-ghost" style="padding: 0.75rem 1.5rem; text-decoration: none; border: 1px solid var(--border);">
                    🔒 Recruiter Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
