<?php
session_start();
include('../db/connect.php'); // ← this defines $conn
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Now $conn is defined
$classes = pg_query($conn, "
    SELECT c.class_id, c.class_name, t.first_name, t.last_name
    FROM classes c
    LEFT JOIN teachers t ON c.teacher_incharge = t.teacher_id
    ORDER BY c.class_id
");
?>


<h3>Classes List</h3>
<a href="add.php" class="btn btn-success mb-3">+ Add Class</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Class Name</th>
            <th>Teacher Incharge</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($c = pg_fetch_assoc($classes)) {
            $teacherName = $c['first_name'] ? $c['first_name'].' '.$c['last_name'] : '---';
            echo "<tr>
                <td>{$c['class_id']}</td>
                <td>{$c['class_name']}</td>
                <td>$teacherName</td>
                <td>
                    <a href='edit.php?id={$c['class_id']}' class='btn btn-warning btn-sm'>Edit</a>
                    <a href='delete.php?id={$c['class_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this class?\")'>Delete</a>
                </td>
            </tr>";
        } ?>
    </tbody>
</table>
