<?php
$pageTitle = "LTW GYM - Contact";
$pageCss = "contact.css";
$activePage = "contact";

require_once 'templates/header.php';
?>

<main class="contact-page">

    <div class="contact-header">
        <h1>Contact Us</h1>
        <p>Get in touch with our team.</p>
    </div>

    <div class="contact-container">

        <section class="contact-form-section">
            <form class="contact-form">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" placeholder="Your name">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" placeholder="Your email">
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" rows="6" placeholder="Your message"></textarea>
                </div>

                <button type="submit" class="submit-btn u-lift">
                    Send Message
                </button>

            </form>
        </section>

        <section class="contact-info">

            <div class="info-card">
                <h3>Email</h3>
                <p>ltwgym@email.com</p>
            </div>

            <div class="info-card">
                <h3>Phone</h3>
                <p>+351 912 345 678</p>
            </div>

            <div class="info-card">
                <h3>Location</h3>
                <p>Porto, Portugal</p>
            </div>

        </section>

    </div>

</main>

<?php require_once 'templates/footer.php'; ?>