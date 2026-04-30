<?php
$pageTitle = 'LTW GYM - Sign Up';
$pageCss = 'signup.css';
$activePage = '';
require_once 'templates/header.php';
?>

<main class="signup-container">
    <div class="signup-form">
        <h1>Join LTW GYM</h1>
        <p>Start your elite fitness journey today</p>

        <form action="#" method="post">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="john@example.com" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="+351 ..." required>
            </div>

            <div class="form-group">
                <label for="birthdate">Birthdate</label>
                <input type="date" id="birthdate" name="birthdate" required>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="plan">Membership Plan</label>
                <select id="plan" name="plan" required>
                    <option value="" disabled selected>Choose a plan</option>
                    <option value="monthly">Monthly - €29.99</option>
                    <option value="quarterly">Quarterly - €79.99</option>
                    <option value="annual">Annual - €299.99</option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree with the <a href="#" target="_blank">terms and conditions</a></label>
            </div>

            <button type="submit" class="btn btn--gold u-lift full-width-btn">Create Account</button>
        </form>
    </div>
</main>

<?php require_once 'templates/footer.php'; ?>
