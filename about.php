<?php
$pageTitle = "LTW GYM - About Us";
$pageCss = "about.css";
$activePage = "about";

require_once 'templates/header.php';
?>

<main class="about-page">

    <section class="about-content">

        <div class="about-text">

            <span class="section-tag">ABOUT LTW</span>

            <h1>
                BUILT FOR<br>
                THOSE WHO<br>
                WANT MORE
            </h1>

            <p>
                LTW Gym combines elite training, premium equipment and a modern atmosphere
                designed to help every athlete reach their highest level.
            </p>

            <div class="about-stats">

                <div class="stat-box">
                    <h2>10+</h2>
                    <span>Years</span>
                </div>

                <div class="stat-box">
                    <h2>25+</h2>
                    <span>Classes</span>
                </div>

                <div class="stat-box">
                    <h2>8K+</h2>
                    <span>Members</span>
                </div>

            </div>
        </div>

        <div class="about-image">
            <img src="assets/img/george-dagerotip-mYaf3lj2Vs8-unsplash.jpg" alt="Gym">
        </div>

    </section>

</main>

<?php require_once 'templates/footer.php'; ?>