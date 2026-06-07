<?php
require '../vendor/autoload.php';
include('../db/connect.php');

use Dompdf\Dompdf;

$filter = "";

if (!empty($_GET['department_id'])) {
    $dept_id = $_GET['department_id'];
    $filter = "WHERE s.department_id = $dept_id";
}

$query = "
SELECT s.*, d.department_name, c.class_name
FROM students s
LEFT JOIN department d ON s.department_id = d.department_id
LEFT JOIN classes c ON s.class_id = c.class_id
$filter
";

$result = pg_query($conn, $query);

$html = "<h2 style='text-align:center'>Student List</h2>";
$html .= "<table border='1' width='100%' cellspacing='0' cellpadding='5'>
<tr>
<th>Name</th>
<th>Class</th>
<th>Department</th>
<th>Phone</th>
</tr>";

while ($row = pg_fetch_assoc($result)) {
    $html .= "<tr>
        <td>{$row['first_name']} {$row['last_name']}</td>
        <td>{$row['class_name']}</td>
        <td>{$row['department_name']}</td>
        <td>{$row['phone']}</td>
    </tr>";
}

$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("students.pdf", ["Attachment" => 1]);e
