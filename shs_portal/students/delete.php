<?php
include('../db/connect.php');

if (isset($_GET['student_id'])) {

    $id = $_GET['student_id'];

    pg_query_params(
        $conn,
        "DELETE FROM students WHERE student_id = $1",
        [$id]
    );
}

header("Location: index.php");
exit();
?>
