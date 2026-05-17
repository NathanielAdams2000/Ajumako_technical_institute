<?php
$host = "localhost";
$port = "5432";
$dbname = "shs_db";
$user = "postgres";
$password = "nat2301";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("❌ Connection failed: " . pg_last_error());
} 
?>



