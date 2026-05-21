<?php
require_once 'utils/connection.php';

$pageTitle = 'LTW GYM - Equipment';
$pageCss = 'equipment.css';
$activePage = 'equipment';

$stmt = $db->query('SELECT * FROM equipment ORDER BY area, name');
$equipment = $stmt->fetchAll();

require_once 'templates/header.php';
?>

<main class="equipment-page">
    <section class="equipment-hero">
        <p class="section-label">Member access</p>
        <h1>Equipment availability</h1>
        <p>Check which machines are available right now in the main training area before you head in.</p>
    </section>

    <section class="equipment-grid">
        <?php foreach ($equipment as $item): ?>
            <?php $statusClass = 'equipment-badge--' . $item['status']; ?>
            <article class="equipment-card">
                <div class="equipment-card__header">
                    <h2><?= htmlspecialchars($item['name']) ?></h2>
                    <span class="equipment-badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($item['status'])) ?></span>
                </div>
                <p class="equipment-area"><?= htmlspecialchars($item['area']) ?></p>
                <div class="equipment-stats">
                    <div>
                        <span>Total units</span>
                        <strong><?= (int) $item['total_units'] ?></strong>
                    </div>
                    <div>
                        <span>Available now</span>
                        <strong><?= (int) $item['available_units'] ?></strong>
                    </div>
                </div>
                <p class="equipment-notes"><?= htmlspecialchars($item['notes']) ?></p>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<?php require_once 'templates/footer.php'; ?>