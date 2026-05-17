<?php
include('../db/connect.php');
$id = $_GET['student_id'];
pg_query($conn, "DELETE FROM students WHERE id=$id");
header("Location: index.php");
exit();
?>
