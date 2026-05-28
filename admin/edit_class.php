<?php
require_once '../utils/connection.php';
require_once '../utils/csrf.php';
require_once 'auth.php';
requireAdmin();
$pageCss = 'admin.css';

$pageTitle = "Edit Class";

$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->execute([$id]);
$class = $stmt->fetch();

if (!$class) {
    header("Location: classes.php");
    exit;
}

$trainers = $db->query("
    SELECT id, name 
    FROM users 
    WHERE role = 'trainer'
    ORDER BY name
")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_die();

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

        $update = $db->prepare("
            UPDATE classes
            SET title = ?,
                description = ?,
                category = ?,
                duration = ?,
                intensity = ?,
                image_url = ?,
                trainer_id = ?
            WHERE id = ?
        ");

        $update->execute([
            $title,
            $description,
            $category,
            $duration,
            $intensity,
            $image_url,
            $trainer_id > 0 ? $trainer_id : null,
            $id
        ]);

        $success = "Class updated successfully!";

        // refresh data
        $stmt->execute([$id]);
        $class = $stmt->fetch();
    }
}

require_once '../templates/header.php';
?>

<main class="admin-container">

    <h1>Edit Class</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" class="admin-form">
        <?= csrf_input() ?>

        <label>
            Title *
            <input type="text" name="title"
                   value="<?= htmlspecialchars($class['title']) ?>" required>
        </label>

        <label>
            Description *
            <textarea name="description" rows="4" required><?= htmlspecialchars($class['description']) ?></textarea>
        </label>

        <label>
            Category *
            <input type="text" name="category"
                   value="<?= htmlspecialchars($class['category']) ?>" required>
        </label>

        <label>
            Duration (minutes) *
            <input type="number" name="duration"
                   value="<?= (int) $class['duration'] ?>" required>
        </label>

        <label>
            Intensity
            <select name="intensity">
                <option value="Low" <?= $class['intensity'] === 'Low' ? 'selected' : '' ?>>Low</option>
                <option value="Medium" <?= $class['intensity'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                <option value="High" <?= $class['intensity'] === 'High' ? 'selected' : '' ?>>High</option>
            </select>
        </label>

        <label>
            Image URL
            <input type="text" name="image_url"
                   value="<?= htmlspecialchars($class['image_url']) ?>">
        </label>

        <label>
            Trainer
            <select name="trainer_id">
                <option value="0">Unassigned</option>
                <?php foreach ($trainers as $trainer): ?>
                    <option value="<?= (int) $trainer['id'] ?>"
                        <?= (int)$class['trainer_id'] === (int)$trainer['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($trainer['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit" class="btn btn--gold u-lift">
            Save Changes
        </button>

    </form>

</main>

<?php require_once '../templates/footer.php'; ?>