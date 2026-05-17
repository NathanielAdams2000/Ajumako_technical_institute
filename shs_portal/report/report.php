<?php
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

/* =========================
   PARAMETERS
========================= */
$student_id = $_GET['student_id'] ?? null;
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? null;
$academic_year = $_GET['year'] ?? null;

if (!$student_id || !$class_id || !$term || !$academic_year) {
    die("Missing report parameters.");
}

/* =========================
   STUDENT INFO
========================= */
$sql_student = "
    SELECT 
        s.first_name,
        s.last_name,
        c.class_name,
        d.department_name
    FROM students s
    JOIN classes c 
        ON s.class_id = c.class_id
    LEFT JOIN department d 
        ON s.department_id = d.department_id
    WHERE s.student_id = $1
";

$res_student = pg_query_params($conn, $sql_student, [$student_id]);
$student = pg_fetch_assoc($res_student);

/* =========================
   RESULTS
========================= */
$sql_results = "
    SELECT 
        sub.subject_name,
        es.class_score,
        es.exams_score_70,
        es.total,
        es.grade,
        es.remarks
    FROM exam_scores es
    JOIN subjects sub 
        ON es.subject_id = sub.subject_id
    WHERE es.student_id = $1
      AND es.class_id = $2
      AND es.term = $3
      AND es.academic_year = $4
    ORDER BY sub.subject_name
";

$results = pg_query_params(
    $conn,
    $sql_results,
    [$student_id, $class_id, $term, $academic_year]
);

/* =========================
   POSITION
========================= */
$rank_query = pg_query_params(
    $conn,
    "SELECT student_id, SUM(total) AS total_sum
     FROM exam_scores
     WHERE class_id = $1
       AND term = $2
       AND academic_year = $3
     GROUP BY student_id
     ORDER BY total_sum DESC",
    [$class_id, $term, $academic_year]
);

$position = 0;
$rank = 1;

while ($r = pg_fetch_assoc($rank_query)) {
    if ($r['student_id'] == $student_id) {
        $position = $rank;
        break;
    }
    $rank++;
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Student Report</title>

<style>

body{
    font-family: Arial, sans-serif;
    background:#f5f5f5;
    margin:0;
    padding:0;
}

.report-card{
    width:95%;
    margin:20px auto;
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

/* HEADER IMAGE */
.header-table{
    width:100%;
    border-collapse:collapse;
}

.header-table td{
    vertical-align:middle;
}

.logo{
    width:90px;
    height:90px;
    object-fit:contain;
}

/* ================= HEADER TEXT FIX ================= */

.header-title{
    text-align:center;
}

.school-name{
    font-size:18px;      /* smaller */
    font-weight:bold;
    color:#0b2e4a;       /* school blue */
    margin:0;
}

.exam-title{
    font-size:14px;      /* smaller */
    font-weight:bold;
    color:#b38b2d;       /* gold */
    margin:3px 0;
}

.report-title{
    font-size:12px;
    color:#333;
}

/* INFO TABLE */
.info-table{
    width:100%;
    margin-top:15px;
    margin-bottom:20px;
}

.info-table td{
    padding:6px;
    font-size:15px;
}

/* RESULT TABLE */
.result-table{
    width:100%;
    border-collapse:collapse;
}

.result-table th,
.result-table td{
    border:1px solid black;
    padding:8px;
    text-align:center;
    font-size:14px;
}

.result-table th{
    background:#343a40;
    color:white;
}

/* SUMMARY */
.summary-table{
    width:100%;
    margin-top:20px;
    border-collapse:collapse;
}

.summary-table td{
    border:1px solid black;
    padding:12px;
    text-align:center;
}

/* SIGNATURE */
.signature-table{
    width:100%;
    margin-top:70px;
}

.signature-table td{
    text-align:center;
    padding-top:30px;
}

/* PRINT */
@media print {
    body{
        background:white;
    }

    .no-print,
    .btn,
    nav,
    header,
    .navbar{
        display:none !important;
    }

    .report-card{
        width:100%;
        margin:0;
        padding:0;
        box-shadow:none;
        border:none;
    }

    @page{
        size:A4;
        margin:10mm;
    }
}

</style>

</head>

<body>

<div class="report-card">

<!-- BACK BUTTON -->
<div class="no-print">
    <a href="javascript:history.back()" class="btn btn-secondary">
        ← Back
    </a>
</div>

<br>

<!-- ================= HEADER (FIXED) ================= -->
<table class="header-table">

<tr>

    <td width="15%">
        <img src="/shs_portal/report/school_logo.jpeg" class="logo">
    </td>

    <td class="header-title">

        <div class="school-name">
            AJUMAKO AFRANSE TECHNICAL INSTITUTE
        </div>

        <div class="exam-title">
            END OF SEMESTER EXAMINATION
        </div>

        <div class="report-title">
            ACADEMIC REPORT CARD
        </div>

    </td>

    <td width="15%" align="right">
        <img src="/shs_portal/report/school_logo.jpeg" class="logo">
    </td>

</tr>

</table>

<hr>

<!-- STUDENT INFO -->
<table class="info-table">

<tr>
<td><b>Name:</b> <?= $student['first_name'] . " " . $student['last_name'] ?></td>
<td align="right"><b>Term:</b> <?= $term ?></td>
</tr>

<tr>
<td><b>Class:</b> <?= $student['class_name'] ?></td>
<td align="right"><b>Year:</b> <?= $academic_year ?></td>
</tr>

<tr>
<td><b>Department:</b> <?= $student['department_name'] ?></td>
<td align="right"><b>Position:</b> <?= $position ?></td>
</tr>

</table>

<!-- RESULTS -->
<table class="result-table">

<thead>
<tr>
    <th>SUBJECT</th>
    <th>CLASS SCORE (30%)</th>
    <th>EXAM SCORE (70%)</th>
    <th>TOTAL</th>
    <th>GRADE</th>
    <th>REMARK</th>
</tr>
</thead>

<tbody>

<?php
$total_sum = 0;
$count = 0;

while ($row = pg_fetch_assoc($results)) {
    $total_sum += $row['total'];
    $count++;
?>

<tr>
<td><?= strtoupper($row['subject_name']) ?></td>
<td><?= $row['class_score'] ?></td>
<td><?= $row['exams_score_70'] ?></td>
<td><b><?= $row['total'] ?></b></td>
<td><?= $row['grade'] ?></td>
<td><?= $row['remarks'] ?></td>
</tr>

<?php } ?>

</tbody>
</table>

<!-- SUMMARY -->
<table class="summary-table">

<tr>
<td><b>TOTAL MARKS</b><br><?= $total_sum ?></td>
<td><b>AVERAGE</b><br><?= $count ? round($total_sum / $count, 2) : 0 ?></td>
<td><b>POSITION</b><br><?= $position ?></td>
</tr>

</table>

<!-- SIGNATURE -->
<table class="signature-table">

<tr>
<td></td>
<td>
___________________________<br>
<b>Vice Principal</b>
</td>
</tr>

</table>

<!-- PRINT -->
<div class="button-area no-print text-center mt-4">
    <button onclick="window.print()" class="btn btn-dark">
        🖨 Print Report
    </button>
</div>

</div>

</body>
</html>