<?php
include('../db/connect.php');
$id = $_GET['id'];
pg_query($conn, "DELETE FROM students WHERE id=$id");
header("Location: index.php");
exit();
?>
