<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'LTW GYM' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/global.css">
    <?php if (isset($pageCss)): ?>
        <link rel="stylesheet" href="css/<?= htmlspecialchars($pageCss) ?>">
    <?php endif; ?>
    <script src="js/nav.js" defer></script>

</head>
<body>
    <header>
        <nav class="main-nav">
            <a href="index.php" class="logo">LTW GYM</a>

            <button class="menu-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
            </button>

            <ul class="nav-links">
                <li><a href="index.php" class="nav-link u-lift<?= isset($activePage) && $activePage === 'index' ? ' active' : '' ?>">Home</a></li>
                <li><a href="trainers.php" class="nav-link u-lift<?= isset($activePage) && $activePage === 'trainers' ? ' active' : '' ?>">Trainers</a></li>
                <li><a href="classes.php" class="nav-link u-lift<?= isset($activePage) && $activePage === 'classes' ? ' active' : '' ?>">Classes</a></li>
                <li><a href="about.html" class="nav-link u-lift">About Us</a></li>
                <li><a href="contact.html" class="nav-link u-lift">Contact</a></li>
            </ul>

            <div class="nav-actions">
                <button class="icon-btn u-lift" aria-label="Search">
                    <svg class="nav-icon"><use href="assets/img/icons.svg#icon-search"></use></svg>
                </button>

                <a href="profile.php" class="nav-link u-lift<?= isset($activePage) && $activePage === 'profile' ? ' active' : '' ?>">
                    <svg class="nav-icon"><use href="assets/img/icons.svg#icon-user"></use></svg>
                    <span>PROFILE</span>
                </a>
            </div>
        </nav>
    </header>
