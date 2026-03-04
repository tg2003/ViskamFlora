<?php
// orders/wedding_page.php
$pageTitle = 'Wedding Arrangements – Viskam Flora';
require_once __DIR__ . '/../includes/navbar.php';
$user = currentUser();
?>

<div class="hero" style="margin-bottom:30px;">
    <h1>💐 Wedding Floral Arrangements</h1>
    <p>Let us make your special day even more beautiful with handcrafted floral designs</p>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;flex-wrap:wrap;">
    <div>
        <h2 style="color:#2d5016;margin-bottom:16px;">Our Wedding Services</h2>
        <?php
        $services = [
            ['🌸', 'Bridal Bouquet', 'Custom bouquets matching your wedding theme'],
            ['🎀', 'Ceremony Decoration', 'Arch, aisle & altar flower arrangements'],
            ['🌿', 'Reception Centerpieces', 'Stunning table centerpieces for each table'],
            ['🚗', 'Car Decoration', 'Beautiful floral car decorations'],
            ['💐', 'Bridesmaids Bouquets', 'Coordinated bouquets for your bridal party'],
            ['🌺', 'Flower Walls', 'Instagram-worthy floral backdrops'],
        ];
        foreach ($services as [$icon, $title, $desc]):
        ?>
        <div style="display:flex;gap:12px;margin-bottom:16px;background:#fff;padding:14px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.06);">
            <span style="font-size:1.8rem;"><?= $icon ?></span>
            <div>
                <h4 style="color:#2d5016;margin-bottom:4px;"><?= $title ?></h4>
                <p style="color:#666;font-size:.92rem;"><?= $desc ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="color:#2d5016;margin-bottom:18px;">Request a Quote</h3>
            <form method="POST" action="/orders/wedding_backend.php">
                <?php csrfField(); ?>
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="contact_name" required value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="contact_email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="contact_phone" required placeholder="+94 77 000 0000" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Wedding Date</label>
                    <input type="date" name="wedding_date" required min="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                </div>
                <div class="form-group">
                    <label>Venue</label>
                    <input type="text" name="venue" placeholder="Hotel / Banquet hall name">
                </div>
                <div class="form-group">
                    <label>Estimated Guest Count</label>
                    <input type="number" name="guest_count" min="1" placeholder="e.g. 150">
                </div>
                <div class="form-group">
                    <label>Budget Range</label>
                    <select name="budget_range">
                        <option value="">Select budget</option>
                        <option value="under_50k">Under LKR 50,000</option>
                        <option value="50k_100k">LKR 50,000 – 100,000</option>
                        <option value="100k_250k">LKR 100,000 – 250,000</option>
                        <option value="250k_500k">LKR 250,000 – 500,000</option>
                        <option value="over_500k">Over LKR 500,000</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Arrangements Needed (check all that apply)</label>
                    <?php
                    $arrangements = ['Bridal Bouquet','Bridesmaids Bouquets','Ceremony Decoration','Reception Centerpieces','Car Decoration','Flower Wall','Corsages & Boutonnieres'];
                    foreach ($arrangements as $arr):
                    ?>
                    <label style="display:flex;align-items:center;gap:6px;font-weight:normal;margin-top:4px;">
                        <input type="checkbox" name="arrangements[]" value="<?= $arr ?>"> <?= $arr ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label>Color Preferences</label>
                    <input type="text" name="color_preferences" placeholder="e.g. Blush pink, white, gold">
                </div>
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests" placeholder="Any specific flowers, theme details or requirements..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Submit Wedding Inquiry</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
