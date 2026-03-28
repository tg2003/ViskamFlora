<?php
// viskam_flora_full/orders/wedding_page.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($conn, $_POST['name']);
    $email = sanitize_input($conn, $_POST['email']);
    $phone = sanitize_input($conn, $_POST['phone']);
    $event_date = sanitize_input($conn, $_POST['event_date']);
    $venue = sanitize_input($conn, $_POST['venue']);
    $details = sanitize_input($conn, $_POST['details']);
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if (empty($name) || empty($phone) || empty($event_date)) {
        $error = "Name, Phone, and Event Date are required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO wedding_arrangements (user_id, name, email, phone, event_date, venue, details) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $name, $email, $phone, $event_date, $venue, $details);
        
        if ($stmt->execute()) {
            $success = "Thank you! Your inquiry has been submitted. Our team will contact you shortly to discuss your big day.";
        } else {
            $error = "Something went wrong. Please try again or contact us directly.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weddings & Events | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Test%20by%20antigravity/viskam_flora_full/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <!-- Event Hero -->
    <header class="hero" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1519225421980-715cb0215aed?q=80&w=1200&auto=format&fit=crop') center/cover; color: white;">
        <div class="container">
            <h1 style="color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Bespoke Weddings & Events</h1>
            <p style="color: #f1f1f1; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">Let us transform your special moments with breathtaking floral artistry and elegant decor.</p>
        </div>
    </header>

    <div class="container" style="max-width: 800px; margin: 60px auto; background: #fff; padding: 40px; border-radius: var(--border-radius-md); box-shadow: var(--shadow-md);">
        <h2 class="text-center mb-4">Request a Consultation</h2>
        <p class="text-center text-muted mb-4">Fill out the form below and our event specialists will get in touch with you.</p>

        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Success!</strong> <?= $success ?>
            </div>
        <?php else: ?>

        <form action="" method="POST">
            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:300px;">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?= isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : '' ?>">
                </div>
                
                <div class="form-group" style="flex:1; min-width:300px;">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
            </div>

            <div style="display:flex; gap:20px; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:300px;">
                    <label for="phone">Phone Number *</label>
                    <input type="text" id="phone" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group" style="flex:1; min-width:300px;">
                    <label for="event_date">Event Date *</label>
                    <input type="date" id="event_date" name="event_date" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="venue">Event Venue / Location</label>
                <input type="text" id="venue" name="venue" class="form-control">
            </div>

            <div class="form-group">
                <label for="details">Tell us about your vision, themes, or specific arrangements needed</label>
                <textarea id="details" name="details" class="form-control" rows="5"></textarea>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary" style="padding: 15px 40px; font-size: 1.1rem;">Submit Inquiry</button>
            </div>
        </form>
        
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

