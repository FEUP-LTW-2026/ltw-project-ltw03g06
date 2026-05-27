<?php
require_once '../utils/connection.php';
require_once 'auth.php';
requireAdmin();

$pageTitle = "Create Class";

$error = '';
$success = '';

$trainers = $db->query("
    SELECT id, name 
    FROM users 
    WHERE role = 'trainer'
    ORDER BY name
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $duration = (int) ($_POST['duration'] ?? 0);
    $intensity = trim($_POST['intensity'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $trainer_id = (int) ($_POST['trainer_id'] ?? 0);

    if ($title === '' || $description === '' || $category === '' || $duration <= 0) {
        $error = "Please fill all required fields.";
    } else {

        $stmt = $db->prepare("
            INSERT INTO classes 
            (title, description, category, duration, intensity, image_url, trainer_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $title,
            $description,
            $category,
            $duration,
            $intensity,
            $image_url,
            $trainer_id > 0 ? $trainer_id : null
        ]);

        $success = "Class created successfully!";
    }
}

require_once '../templates/header.php';
?>

<main class="admin-container">

    <h1>Create Class</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" class="admin-form">

        <label>
            Title *
            <input type="text" name="title" required>
        </label>

        <label>
            Description *
            <textarea name="description" rows="4" required></textarea>
        </label>

        <label>
            Category *
            <input type="text" name="category" placeholder="Strength, Cardio..." required>
        </label>

        <label>
            Duration (minutes) *
            <input type="number" name="duration" required>
        </label>

        <label>
            Intensity
            <select name="intensity">
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
        </label>

        <label>
            Image URL
            <input type="text" name="image_url">
        </label>

        <label>
            Trainer
            <select name="trainer_id">
                <option value="0">Unassigned</option>
                <?php foreach ($trainers as $trainer): ?>
                    <option value="<?= (int) $trainer['id'] ?>">
                        <?= htmlspecialchars($trainer['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit" class="btn btn--gold u-lift">
            Create Class
        </button>

    </form>

</main>

<?php require_once '../templates/footer.php'; ?>