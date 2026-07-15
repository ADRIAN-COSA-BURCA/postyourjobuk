<div class="card" style="max-width: 600px; margin: 2rem auto; padding: 2rem;">
    <h3>Edit Tenant: <?= htmlspecialchars($tenant['company_name']) ?></h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
        
        <div style="margin-bottom: 1rem;"><label>Company Name</label><input type="text" name="company_name" value="<?= htmlspecialchars($tenant['company_name']) ?>" required style="width: 100%;"></div>
        <div style="margin-bottom: 1rem;"><label>Contact Person</label><input type="text" name="contact_person" value="<?= htmlspecialchars($tenant['contact_person'] ?? '') ?>" style="width: 100%;"></div>
        <div style="margin-bottom: 1rem;"><label>Phone Number</label><input type="text" name="phone_number" value="<?= htmlspecialchars($tenant['phone_number'] ?? '') ?>" style="width: 100%;"></div>
        <div style="margin-bottom: 1rem;"><label>Website URL</label><input type="url" name="website_url" value="<?= htmlspecialchars($tenant['website_url'] ?? '') ?>" style="width: 100%;"></div>
        <div style="margin-bottom: 1rem;"><label>Industry</label><input type="text" name="industry" value="<?= htmlspecialchars($tenant['industry'] ?? '') ?>" style="width: 100%;"></div>
        <div style="margin-bottom: 1rem;"><label>Address</label><textarea name="company_address" style="width: 100%;"><?= htmlspecialchars($tenant['company_address'] ?? '') ?></textarea></div>
        
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="/admin/view-tenant?id=<?= $tenant['tenant_id'] ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>