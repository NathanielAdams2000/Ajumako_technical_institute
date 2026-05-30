<?php
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

/* GET FILTER VALUES */
$class_id = $_GET['class_id'] ?? null;
$term = $_GET['term'] ?? null;
$academic_year = $_GET['year'] ?? null;

/* LOAD CLASSES */
$classes = pg_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");

/* GET DISTINCT TERMS BASED ON CLASS + YEAR */
$terms = null;
if ($class_id && $academic_year) {
    $terms = pg_query_params(
        $conn,
        "SELECT DISTINCT term 
         FROM exam_scores 
         WHERE class_id=$1 AND academic_year=$2
         ORDER BY term",
        [$class_id, $academic_year]
    );
}

/* GET DISTINCT ACADEMIC YEARS (OPTIONAL HELPER) */
$years = pg_query(
    $conn,
    "SELECT DISTINCT academic_year FROM exam_scores ORDER BY academic_year DESC"
);
?>

<div class="main-content p-4">
<div class="card p-4 shadow">

<h4>📄 Select Class to View Reports</h4>

<!-- FILTER FORM -->
<form method="GET" class="row g-3 mb-4">

    <!-- CLASS -->
    <div class="col-md-4">
        <label>Class</label>
        <select name="class_id" class="form-select" required>
            <option value="">-- Select Class --</option>
            <?php while($c = pg_fetch_assoc($classes)) { ?>
                <option value="<?= $c['class_id'] ?>" <?= ($class_id == $c['class_id']) ? 'selected' : '' ?>>
                    <?= $c['class_name'] ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <!-- TERM (DYNAMIC) -->
 <div class="col-md-4">
    <label>Semester</label>
    <select name="term" class="form-select" required>
        <option value="">-- Select Semester --</option>

        <?php
        $terms = pg_query($conn, "SELECT DISTINCT term FROM exam_scores ORDER BY term");

        if ($terms && pg_num_rows($terms) > 0) {
            while ($t = pg_fetch_assoc($terms)) { ?>
                <option value="<?= htmlspecialchars($t['term']) ?>"
                    <?= ($term == $t['term']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['term']) ?>
                </option>
            <?php }
        }
        ?>

    </select>
</div>


    <!-- ACADEMIC YEAR -->
    <div class="col-md-4">
        <label>Academic Year</label>
        <select name="year" class="form-select" required>
            <option value="">-- Select Year --</option>

            <?php while($y = pg_fetch_assoc($years)) { ?>
                <option value="<?= $y['academic_year'] ?>" <?= ($academic_year == $y['academic_year']) ? 'selected' : '' ?>>
                    <?= $y['academic_year'] ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="col-12">
        <button class="btn btn-primary">Filter Students</button>
    </div>

</form>

<?php
/* SHOW STUDENTS AFTER FILTER */
if ($class_id && $term && $academic_year) {

    $students = pg_query_params(
        $conn,
        "SELECT student_id, first_name, last_name 
         FROM students 
         WHERE class_id=$1
         ORDER BY first_name",
        [$class_id]
    );
?>

<h5>Students in Class</h5>

<table class="table table-bordered">
<thead>
<tr>
    <th>Student</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php while ($st = pg_fetch_assoc($students)) { ?>
<tr>
    <td><?= $st['first_name'] . " " . $st['last_name'] ?></td>

    <td>
        <a class="btn btn-success btn-sm"
           href="report.php?student_id=<?= $st['student_id'] ?>&class_id=<?= $class_id ?>&term=<?= $term ?>&year=<?= $academic_year ?>">
            View Report
        </a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>

<?php } ?>

</div>
</div>