<?php

$conn = pg_connect("
host=dpg-d89q9r6q1p3s73dq3r5g-a
port=5432
dbname=shs_db_pi0o
user=shs_db_pi0o_user
password=yzkh72m7CokjMpnq2MlJ06Uh6xBJGw14
");

if (!$conn) {
    die("Database connection failed: " . pg_last_error());
}
?>
