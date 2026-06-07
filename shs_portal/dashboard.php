```php
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
   BASIC COUNTS
========================= */

function getCount($conn, $table) {
    $q = pg_query($conn, "SELECT COUNT(*) FROM $table");
    return pg_fetch_result($q, 0, 0);
}

$totalStudents = getCount($conn, 'students');
$totalSubject  = getCount($conn, 'subjects');
$totalClasses  = getCount($conn, 'classes');
$totalScores   = getCount($conn, 'exam_scores');

/* =========================
   MALE / FEMALE
========================= */

$maleQ = pg_query($conn, "SELECT COUNT(*) FROM students WHERE gender='Male'");
$totalMale = pg_fetch_result($maleQ, 0, 0);

$femaleQ = pg_query($conn, "SELECT COUNT(*) FROM students WHERE gender='Female'");
$totalFemale = pg_fetch_result($femaleQ, 0, 0);

/* =========================
   BOARDING / DAY
========================= */

$boardingQ = pg_query($conn, "SELECT COUNT(*) FROM students WHERE residence='Boarding'");
$totalBoarding = pg_fetch_result($boardingQ, 0, 0);

$dayQ = pg_query($conn, "SELECT COUNT(*) FROM students WHERE residence='Day'");
$totalDay = pg_fetch_result($dayQ, 0, 0);

/* =========================
   STUDENTS PER CLASS
========================= */

$classData = pg_query($conn,"
SELECT c.class_name, COUNT(s.student_id) AS total
FROM classes c
LEFT JOIN students s
ON c.class_id=s.class_id
GROUP BY c.class_name
ORDER BY c.class_name
");

/* =========================
   GENDER DISTRIBUTION
========================= */

$genderData = pg_query($conn,"
SELECT gender, COUNT(*) AS total
FROM students
GROUP BY gender
");

/* =========================
   STUDENTS BY DEPARTMENT
========================= */

$departmentData = pg_query($conn,"
SELECT d.department_name,
COUNT(s.student_id) AS total
FROM department d
LEFT JOIN students s
ON d.department_id=s.department_id
GROUP BY d.department_name
ORDER BY d.department_name
");

/* =========================
   DEPARTMENT DAY & BOARDING
========================= */

$deptResidence = pg_query($conn,"
SELECT
d.department_name,
SUM(CASE WHEN s.residence='Boarding' THEN 1 ELSE 0 END) AS boarding,
SUM(CASE WHEN s.residence='Day' THEN 1 ELSE 0 END) AS day
FROM department d
LEFT JOIN students s
ON d.department_id=s.department_id
GROUP BY d.department_name
ORDER BY d.department_name
");
?>

<div class="main-content p-4">

<div class="row g-4">

<div class="col-md-3">
<div class="card p-3 text-center text-white bg-primary">
<h5>Total Students</h5>
<h2><?= $totalStudents ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center text-white bg-success">
<h5>Total Male</h5>
<h2><?= $totalMale ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center text-white bg-danger">
<h5>Total Female</h5>
<h2><?= $totalFemale ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center text-white bg-dark">
<h5>Total Subjects</h5>
<h2><?= $totalSubject ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center text-white bg-warning">
<h5>Total Boarding</h5>
<h2><?= $totalBoarding ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center text-white bg-info">
<h5>Total Day</h5>
<h2><?= $totalDay ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center text-white bg-secondary">
<h5>Total Classes</h5>
<h2><?= $totalClasses ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center text-white bg-success">
<h5>Total Scores</h5>
<h2><?= $totalScores ?></h2>
</div>
</div>

</div>

<div class="row mt-5">

<div class="col-md-4">
<div class="card p-3">
<h5>Students per Class</h5>
<canvas id="classChart"></canvas>
</div>
</div>

<div class="col-md-3">
<div class="card p-3">
<h5>Gender Distribution</h5>
<canvas id="genderChart"></canvas>
</div>
</div>

<div class="col-md-4 mt-4">
<div class="card p-3">
<h5>Students by Department</h5>
<canvas id="departmentChart"></canvas>
</div>
</div>

<div class="col-md-4 mt-4">
<div class="card p-3">
<h5>Department Day & Boarding</h5>
<canvas id="deptBoardingChart"></canvas>
</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

/* STUDENTS PER CLASS */

const classLabels = [];
const classValues = [];

<?php while($c = pg_fetch_assoc($classData)){ ?>

classLabels.push("<?= $c['class_name'] ?>");
classValues.push(<?= $c['total'] ?>);

<?php } ?>

new Chart(document.getElementById('classChart'),{
type:'bar',
data:{
labels:classLabels,
datasets:[{
label:'Students',
data:classValues,
backgroundColor:'#0d6efd'
}]
}
});


/* GENDER */

const genderLabels=[];
const genderValues=[];

<?php while($g=pg_fetch_assoc($genderData)){ ?>

genderLabels.push("<?= $g['gender'] ?>");
genderValues.push(<?= $g['total'] ?>);

<?php } ?>

new Chart(document.getElementById('genderChart'),{
type:'pie',
data:{
labels:genderLabels,
datasets:[{
data:genderValues,
backgroundColor:['#36A2EB','#FF6384']
}]
}
});


/* DEPARTMENT */

const deptLabels=[];
const deptValues=[];

<?php while($d=pg_fetch_assoc($departmentData)){ ?>

deptLabels.push("<?= $d['department_name'] ?>");
deptValues.push(<?= $d['total'] ?>);

<?php } ?>

new Chart(document.getElementById('departmentChart'),{
type:'bar',
data:{
labels:deptLabels,
datasets:[{
label:'Students',
data:deptValues,
backgroundColor:'#198754'
}]
}
});


/* DEPARTMENT BOARDING & DAY */

const depNames=[];
const boarding=[];
const day=[];

<?php while($r=pg_fetch_assoc($deptResidence)){ ?>

depNames.push("<?= $r['department_name'] ?>");
boarding.push(<?= $r['boarding'] ?>);
day.push(<?= $r['day'] ?>);

<?php } ?>

new Chart(document.getElementById('deptBoardingChart'),{
type:'bar',
data:{
labels:depNames,
datasets:[
{
label:'Boarding',
data:boarding,
backgroundColor:'#0d6efd'
},
{
label:'Day',
data:day,
backgroundColor:'#ffc107'
}
]
}
});

</script>
```
