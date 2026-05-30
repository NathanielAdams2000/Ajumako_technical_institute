<?php
session_start();
include('db/connect.php');
include('header.php');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user'];

/* =========================
   BASIC COUNTS (KPI DATA)
========================= */
function getCount($conn, $table) {
    $q = pg_query($conn, "SELECT COUNT(*) FROM $table");
    return pg_fetch_result($q, 0, 0);
}

$totalStudents = getCount($conn, 'students');
$totalsubject = getCount($conn, 'subjects');
$totalClasses  = getCount($conn, 'classes');
$totalScores   = getCount($conn, 'exam_scores');

/* Pass rate */
$passQ = pg_query($conn, "SELECT COUNT(*) FROM exam_scores WHERE total >= 50");
$pass = pg_fetch_result($passQ, 0, 0);

$totalQ = pg_query($conn, "SELECT COUNT(*) FROM exam_scores");
$total = pg_fetch_result($totalQ, 0, 0);

$passRate = $total > 0 ? round(($pass / $total) * 100) : 0;

/* =========================
   CHART DATA
========================= */

/* Students per class */
$classData = pg_query($conn, "
    SELECT c.class_name, COUNT(s.student_id) AS total
    FROM classes c
    LEFT JOIN students s ON s.class_id = c.class_id
    GROUP BY c.class_name
    ORDER BY c.class_name
");

/* Gender distribution */
$genderData = pg_query($conn, "
    SELECT gender, COUNT(*) AS total
    FROM students
    GROUP BY gender
");

/* Pass / Fail */
$perfData = pg_query($conn, "
    SELECT 
        CASE WHEN total >= 50 THEN 'Pass' ELSE 'Fail' END AS result,
        COUNT(*) AS total
    FROM exam_scores
    GROUP BY result
");
?>

<!-- =========================
     DASHBOARD UI
========================= -->
<div class="main-content p-4">

<!-- KPI CARDS -->
<div class="row g-4">

  <div class="col-md-3">
    <div class="card p-3 text-center text-white" style="background-color:#0d6efd;">
        <h5>Students</h5>
        <h2><?= $totalStudents ?></h2>
    </div>
</div>

    <div class="col-md-3">
        <div class="card p-3 text-center text-white" style="background-color:#0d6efd;">
            <h5>Subjects</h5>
            <h2><?= $totalsubject ?></h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 text-center text-white" style="background-color:#0d6efd;">
            <h5>Classes</h5>
            <h2><?= $totalClasses ?></h2>
        </div>
    </div>


<!-- =========================
     CHART SECTION
========================= -->
<div class="row mt-5">

    <div class="col-md-6">
        <div class="card p-3">
            <h5>Students per Class</h5>
            <canvas id="classChart"></canvas>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h5>Gender Distribution</h5>
            <canvas id="genderChart"></canvas>
        </div>
    </div>

</div>



<!-- =========================
     JS DATA + CHARTS
========================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

/* CLASS CHART */
const classLabels = [];
const classValues = [];

<?php while($c = pg_fetch_assoc($classData)) { ?>
classLabels.push("<?= $c['class_name'] ?>");
classValues.push(<?= $c['total'] ?>);
<?php } ?>

new Chart(document.getElementById('classChart'), {
    type: 'bar',
    data: {
        labels: classLabels,
        datasets: [{
            label: 'Students',
            data: classValues,
            backgroundColor: '#0d6efd'
        }]
    }
});

/* GENDER CHART */
const genderLabels = [];
const genderValues = [];

<?php while($g = pg_fetch_assoc($genderData)) { ?>
genderLabels.push("<?= $g['gender'] ?>");
genderValues.push(<?= $g['total'] ?>);
<?php } ?>

new Chart(document.getElementById('genderChart'), {
    type: 'pie',
    data: {
        labels: genderLabels,
        datasets: [{
            data: genderValues,
            backgroundColor: ['#36A2EB', '#FF6384']
        }]
    }
});


</script>