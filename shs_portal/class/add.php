<?php
ob_start(); // start output buffering
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = $_POST['class_name'];
    $teacher_id = $_POST['teacher_incharge'] ?: null;

    $query = "INSERT INTO classes (class_name, teacher_incharge) VALUES ($1, $2)";
    $res = pg_query_params($conn, $query, [$class_name, $teacher_id]);

    if ($res) {
        // Redirect to index after saving
        header("Location: index.php?added=1");
        exit();
    } else {
        $error = "❌ Error: " . pg_last_error($conn);
    }
}


// Fetch teachers for dropdown
$teachers = pg_query($conn, "SELECT teacher_id, first_name, last_name FROM teachers ORDER BY first_name");
ob_end_flush();
?>


<div class="main-content p-4">
    <div class="card shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-primary">✏️ Add New Class</h3>
            <a href="index.php" class="btn btn-secondary btn-sm">← Back</a>
        </div>

<?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

<form method="POST">
    <div class="row g-3 align-items-end ">
        <div class="col-md-6">
            <label for="class_name" class="form-label">Class Name</label>
            <input type="text" name="class_name" id="class_name" class="form-control" placeholder="Enter class name" required>
        </div>
        <div class="col-md-6">
            <label for="teacher_incharge" class="form-label">Teacher Incharge</label>
            <select name="teacher_incharge" id="teacher_incharge" class="form-select">
                <option value="">-- Select Teacher --</option>
                <?php while($t = pg_fetch_assoc($teachers)) {
                    echo "<option value='{$t['id']}'>{$t['first_name']} {$t['last_name']}</option>";
                } ?>
            </select>
        </div>
        <div class="col-12 text-center mt-3">
            <button type="submit" class="btn btn-primary px-5">💾 Add Class</button>
        </div>
    </div>
</form>

