<?php
ob_start(); // start output buffering
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$success = $error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = $_POST['subject_name'];
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'] ?: null;

    $query = "INSERT INTO subjects (subject_name, class_id, teacher_id) VALUES ($1, $2, $3)";
    $res = pg_query_params($conn, $query, [$subject_name, $class_id, $teacher_id]);

    if ($res) {
        header("Location: index.php?added=1");
        exit();
    } else {
        $error = "❌ Error: " . pg_last_error($conn);
    }
}

// Fetch classes & teachers for dropdowns
$classes = pg_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");
$teachers = pg_query($conn, "SELECT teacher_id, first_name, last_name FROM teachers ORDER BY first_name");
ob_end_flush();
?>

<div class="main-content p-4">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-primary">✏️ Add New Subject</h3>
            <a href="index.php" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" class="form-control border" required>
                </div>
                <div class="col-md-4">
                    <label>Class</label>
                    <select name="class_id" class="form-select border" required>
                        <option value="">-- Select Class --</option>
                        <?php while($c = pg_fetch_assoc($classes)) {
                            echo "<option value='{$c['class_id']}'>{$c['class_name']}</option>";
                        } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-select border">
                        <option value="">-- Select Teacher --</option>
                        <?php while($t = pg_fetch_assoc($teachers)) {
                            echo "<option value='{$t['id']}'>{$t['first_name']} {$t['last_name']}</option>";
                        } ?>
                    </select>
                </div>
            </div>

            <div class="text-center mt-4">
             
                <button type="submit" class="btn btn-primary px-5">💾 Add Subject</button>
            </div>
        </form>
    </div>
</div>
