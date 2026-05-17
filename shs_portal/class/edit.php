<?php
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$success = $error = "";

// Get class ID
$id = $_GET['id'] ?? 0;

// Fetch class info
$query = "SELECT * FROM classes WHERE class_id=$1";
$result = pg_query_params($conn, $query, [$id]);
$class = pg_fetch_assoc($result);

if (!$class) {
    echo "<div class='alert alert-danger'>Class not found.</div>";
    exit();
}

// Fetch teachers for dropdown
$teachers = pg_query($conn, "SELECT teacher_id, first_name, last_name FROM teachers ORDER BY first_name");

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'];
    $teacher_id = $_POST['teacher_incharge'] ?: null;

    $updateQuery = "UPDATE classes SET class_name=$1, teacher_incharge=$2 WHERE class_id=$3";
    $res = pg_query_params($conn, $updateQuery, [$class_name, $teacher_id, $id]);

    if ($res) {
        header("Location: index.php?updated=1"); // redirect after saving
        exit();
    } else {
        $error = "❌ Error: " . pg_last_error($conn);
    }
}
?>

<div class="main-content p-4">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-primary">✏️ Edit Class</h3>
            <a href="index.php" class="btn btn-secondary btn-sm">← Back</a>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Class Name</label>
                    <input type="text" name="class_name" class="form-control border" required value="<?= htmlspecialchars($class['class_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label>Teacher Incharge</label>
                    <select name="teacher_incharge" class="form-select border">
                        <option value="">-- Select Teacher --</option>
                        <?php while($t = pg_fetch_assoc($teachers)) {
                            $selected = ($t['class_id'] == $class['teacher_incharge']) ? "selected" : "";
                            echo "<option value='{$t['id']}' $selected>{$t['first_name']} {$t['last_name']}</option>";
                        } ?>
                    </select>
                </div>
            </div>

            <div class="text-center mt-4">
               
                <button type="submit" class="btn btn-primary px-5">💾 Update Class</button>
            </div>
        </form>
    </div>
</div>
