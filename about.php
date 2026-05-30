<?php
$pageTitle = "LTW GYM - About Us";
$pageCss = "about.css";
$activePage = "about";

require_once 'templates/header.php';
?>

<main class="about-page">

    <section class="about-content">

        <article class="about-text">
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

            <section class="about-stats">
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
            </section>
        </article>

        <figure class="about-image">
            <img src="https://images.unsplash.com/photo-1593079831268-3381b0db4a77?q=80&w=1738&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Gym">
        </figure>

    </section>

</main>

<?php require_once 'templates/footer.php'; ?>