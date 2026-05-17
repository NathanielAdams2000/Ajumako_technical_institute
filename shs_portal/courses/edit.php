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

// Get subject ID from query string
$id = $_GET['id'] ?? 0;

// Fetch subject info
$query = "SELECT * FROM subjects WHERE subject_id=$1";
$result = pg_query_params($conn, $query, [$id]);
$subject = pg_fetch_assoc($result);

if (!$subject) {
    echo "<div class='alert alert-danger'>Subject not found.</div>";
    exit();
}

// Handle POST submission (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_name = $_POST['subject_name'];
    $class_id = $_POST['class_id'];
    $teacher_id = $_POST['teacher_id'] ?: null;

    $updateQuery = "UPDATE subjects SET subject_name=$1, class_id=$2, teacher_id=$3 WHERE subject_id=$4";
    $res = pg_query_params($conn, $updateQuery, [$subject_name, $class_id, $teacher_id, $id]);

    if ($res) {
        header("Location: index.php?updated=1");
        exit();
    } else {
        $error = "❌ Error updating subject: " . pg_last_error($conn);
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
            <h3 class="mb-0 text-primary">✏️ Edit Subject</h3>
            <a href="index.php" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" class="form-control border" required value="<?= htmlspecialchars($subject['subject_name']) ?>">
                </div>
                <div class="col-md-4">
                    <label>Class</label>
                    <select name="class_id" class="form-select border" required>
                        <option value="">-- Select Class --</option>
                        <?php while($c = pg_fetch_assoc($classes)) {
                           $selected = ($c['class_id'] == $subject['class_id']) ? "selected" : "";
                            echo "<option value='{$c['class_id']}' $selected>{$c['class_name']}</option>";
                        } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-select border">
                        <option value="">-- Select Teacher --</option>
                        <?php while($t = pg_fetch_assoc($teachers)) {
                            $selected = ($t['teacher_id'] == $subject['teacher_id']) ? "selected" : "";
                            echo "<option value='{$t['teacher_id']}' $selected>{$t['first_name']} {$t['last_name']}</option>";
                        } ?>
                    </select>
						
					
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-5">💾 Update Subject</button>
            </div>
        </form>
    </div>
</div>
