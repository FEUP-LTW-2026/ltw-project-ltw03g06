<?php
$pageTitle = 'LTW GYM - Edit Profile';
$pageCss = 'profile.css';
$activePage = 'profile';
require_once 'templates/header.php';
?>

<main class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="avatar-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <h1>My Account</h1>
            <p>Update your personal details and membership settings</p>
        </div>

        <form action="#" method="post" class="profile-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="Alex Smith">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="alex.smith@example.com">
                </div>
            </div>

            <div class="form-group">
                <label for="plan">Membership Plan</label>
                <select id="plan" name="plan">
                    <option value="monthly">Monthly - €29.99</option>
                    <option value="quarterly" selected>Quarterly - €79.99</option>
                    <option value="annual">Annual - €299.99</option>
                </select>
            </div>

            <div class="profile-actions">
                <button type="submit" class="btn btn--gold u-lift">Save Changes</button>
                <a href="index.php" class="btn btn--outline u-lift">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php require_once 'templates/footer.php'; ?>
