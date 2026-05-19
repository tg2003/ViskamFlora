<?php
// viskam_flora_full/contact.php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Viskam Flora</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .contact-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .contact-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .contact-header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        .contact-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
        }
        .info-box {
            flex: 1 1 calc(33.333% - 20px);
            text-align: center;
            padding: 20px;
            background: #fdfaf6;
            border-radius: 8px;
            border: 1px solid #f0e6d2;
        }
        .info-box h3 {
            margin-bottom: 10px;
            color: var(--text-main);
            font-family: 'Playfair Display', serif;
        }
        .info-box p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .contact-form input, .contact-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e1d8c9;
            border-radius: 5px;
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        .contact-form input:focus, .contact-form textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .contact-form button {
            align-self: flex-start;
            padding: 12px 30px;
            font-size: 1.05rem;
        }
        
        @media (max-width: 768px) {
            .info-box {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <div class="container">
        <div class="contact-container">
            <div class="contact-header">
                <h1>Contact Us</h1>
                <p>We'd love to hear from you! Please reach out with any questions or feedback.</p>
            </div>
            
            <div class="contact-info">
                <div class="info-box">
                    <h3>📍 Address</h3>
                    <p>123 Flower Street<br>Colombo, Sri Lanka</p>
                </div>
                <div class="info-box">
                    <h3>📞 Phone</h3>
                    <p>+94 77 123 4567<br>+94 11 234 5678</p>
                </div>
                <div class="info-box">
                    <h3>✉️ Email</h3>
                    <p>info@viskamflora.com<br>support@viskamflora.com</p>
                </div>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo '<div style="background: #2e7d32; color: #fff; padding: 15px 20px; text-align: center; border-radius: 5px; margin-bottom: 25px; font-weight: 600;">✨ Thank you for contacting us! We will get back to you shortly.</div>';
            }
            ?>

            <form class="contact-form" method="POST" action="contact.php">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <input type="text" name="subject" placeholder="Subject">
                <textarea name="message" rows="6" placeholder="Your Message" required></textarea>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
