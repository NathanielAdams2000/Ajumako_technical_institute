<?php
session_start();
include('../db/connect.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

/* =========================
   DELETE DEPARTMENT
========================= */
if (isset($_GET['delete_department'])) {
    $id = $_GET['delete_department'];

    pg_query_params($conn,
        "DELETE FROM department WHERE department_id=$1",
        [$id]
    );

    header("Location: index.php");
    exit();
}

/* =========================
   DELETE SUBJECT
========================= */
if (isset($_GET['delete_subject'])) {
    $id = $_GET['delete_subject'];

    pg_query_params($conn,
        "DELETE FROM subjects WHERE subject_id=$1",
        [$id]
    );

    header("Location: index.php");
    exit();
}

/* =========================
   ADD DEPARTMENT
========================= */
if (isset($_POST['add_department'])) {
    $name = trim($_POST['department_name']);

    pg_query_params($conn,
        "INSERT INTO department (department_name) VALUES ($1)",
        [$name]
    );

    header("Location: index.php");
    exit();
}

/* =========================
   ADD SUBJECT
========================= */
if (isset($_POST['add_subject'])) {
    $subject = trim($_POST['subject_name']);
    $department_id = $_POST['department_id'];

    pg_query_params($conn,
        "INSERT INTO subjects (subject_name, department_id) VALUES ($1, $2)",
        [$subject, $department_id]
    );

    header("Location: index.php");
    exit();
}

/* 🔥 LOAD DATA */
$departments = pg_query($conn, "SELECT * FROM department ORDER BY department_name");

/* 🔥 INCLUDE HEADER AFTER LOGIC */
include('../header.php');
?>
<style>
.btn-mini {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
}
</style>
<div class="main-content p-4">
<div class="card p-4 shadow-sm">

<h3 class="mb-4">📚 Manage Departments & Subjects</h3>

<!-- ADD DEPARTMENT -->
<form method="POST" class="mb-4">
    <div class="input-group">
        <input type="text" name="department_name" class="form-control" placeholder="Enter Department Name" required>
        <button class="btn btn-primary" name="add_department">Add Department</button>
    </div>
</form>

<?php while ($d = pg_fetch_assoc($departments)) { ?>

<div class="card mb-4 border">
    
    <!-- HEADER -->
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
        <strong><?= htmlspecialchars($d['department_name']) ?></strong>

        <!-- DELETE DEPARTMENT -->
        <a href="?delete_department=<?= $d['department_id'] ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Delete this department?');">
           Delete
        </a>
    </div>

    <div class="card-body">

        <!-- SUBJECT LIST -->
        <?php
        $subjects = pg_query_params(
            $conn,
            "SELECT * FROM subjects WHERE department_id=$1",
            [$d['department_id']]
        );

        if (pg_num_rows($subjects) > 0) {
    echo "<ul class='list-group mb-3'>";

    while ($s = pg_fetch_assoc($subjects)) {
        echo "
        <li class='list-group-item d-flex justify-content-between align-items-center'>
            📘 " . htmlspecialchars($s['subject_name']) . "

            <a href='?delete_subject={$s['subject_id']}'
               class='btn btn-sm btn-danger btn-mini'
               onclick='return confirm(\"Delete this subject?\")'>
               ✖
            </a>
        </li>";
    }

    echo "</ul>";
} else {
    echo "<p class='text-muted'>No subjects yet</p>";
}
        ?>

        <!-- ADD SUBJECT -->
        <form method="POST" class="d-flex">
            <input type="hidden" name="department_id" value="<?= $d['department_id'] ?>">
            <input type="text" name="subject_name" class="form-control me-2" placeholder="Add subject" required>
            <button class="btn btn-success" name="add_subject">Add</button>
        </form>

    </div>

</div>

<?php } ?>

</div>
</div>