<?php
session_start();
include('../db/connect.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

/* GET DATA FROM PREVIOUS PAGE */
$class_id = $_GET['class_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;
$term = $_GET['term'] ?? null;

if (!$class_id || !$subject_id || !$term) {
    header("Location: index.php");
    exit();
}

/* AUTO ACADEMIC YEAR */
function getAcademicYear() {
    $year = date("Y");
    $month = date("n");

    if ($month >= 9) {
        return $year . "/" . ($year + 1);
    } else {
        return ($year - 1) . "/" . $year;
    }
}

$academic_year = getAcademicYear();

/* HANDLE FORM SUBMISSION FIRST */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $student_ids = $_POST['student_id'];
    $class_scores = $_POST['class_score'];
    $exam_scores = $_POST['exam_score'];

    for ($i = 0; $i < count($student_ids); $i++) {
$student_id = $student_ids[$i];

$class_score = trim($class_scores[$i]);
$exam_score = trim($exam_scores[$i]);

// Skip if both fields are empty
if ($class_score === '' && $exam_score === '') {
    continue;
}

// Convert to numbers only for calculation
$class_score_num = ($class_score === '') ? 0 : (float)$class_score;
$exam_score_num = ($exam_score === '') ? 0 : (float)$exam_score;

// Convert exam score from /100 to /70
$exams_score_70 = ($exam_score_num / 100) * 70;

// Final total out of 100
$total = $class_score_num + $exams_score_70;

        if ($total >= 75) { $grade="A"; $remark="Distinction"; }
        elseif ($total >= 70) { $grade="B+"; $remark="Upper credit"; }
        elseif ($total >= 65) { $grade="B-"; $remark="Upper credit"; }
        elseif ($total >= 55) { $grade="C+"; $remark="Credit"; }
        elseif ($total >= 50) { $grade="C-"; $remark="Lower credit"; }
		elseif ($total >= 45) { $grade="D"; $remark="Pass"; }
		elseif ($total >= 40) { $grade="E"; $remark="Pass"; }
        else { $grade="F"; $remark="Fail"; }

        $check = pg_query_params(
            $conn,
            "SELECT id FROM exam_scores 
             WHERE student_id=$1 AND class_id=$2 AND subject_id=$3 AND term=$4 AND academic_year=$5",
            [$student_id, $class_id, $subject_id, $term, $academic_year]
        );

        if (pg_num_rows($check) > 0) {
            pg_query_params($conn,
                "UPDATE exam_scores 
                 SET class_score=$1, exam_score=$2,exams_score_70 = $3, total=$4, grade=$5, remarks=$6
                 WHERE student_id=$7 AND class_id=$8 AND subject_id=$9 AND term=$10 AND academic_year=$11",
                [
                    $class_score,
                    $exam_score,
					$exams_score_70,
                    $total,
                    $grade,
                    $remark,
                    $student_id,
                    $class_id,
                    $subject_id,
                    $term,
                    $academic_year
                ]
            );
        } else {
            pg_query_params($conn,
                "INSERT INTO exam_scores
                (student_id, class_id, subject_id, class_score, exam_score,exams_score_70, total, grade, remarks, term, academic_year)
                VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11)",
                [
                    $student_id,
                    $class_id,
                    $subject_id,
                    $class_score,
                    $exam_score,
					$exams_score_70,
                    $total,
                    $grade,
                    $remark,
                    $term,
                    $academic_year
                ]
            );
        }
    }

    header("Location: index.php?saved=1");
    exit();
}

/* NOW LOAD DATA AFTER POST HANDLING */
include('../header.php');

/* GET CLASS NAME */
$class_data = pg_fetch_assoc(pg_query_params(
    $conn,
    "SELECT class_name FROM classes WHERE class_id=$1",
    [$class_id]
));

/* GET SUBJECT NAME */
$subject_data = pg_fetch_assoc(pg_query_params(
    $conn,
    "SELECT subject_name FROM subjects WHERE subject_id=$1",
    [$subject_id]
));

/* LOAD STUDENTS + SCORES */
$students = pg_query_params(
    $conn,
    "SELECT 
        s.student_id,
        s.first_name,
        s.last_name,
        es.class_score,
        es.exam_score
     FROM students s
     LEFT JOIN exam_scores es 
        ON es.student_id = s.student_id
        AND es.class_id = $1
        AND es.subject_id = $2
        AND es.term = $3
        AND es.academic_year = $4
     WHERE s.class_id = $1
     ORDER BY s.first_name",
    [$class_id, $subject_id, $term, $academic_year]
);
?>
<div class="main-content p-4">
<div class="card p-4 shadow">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0 text-primary">✏️ Student Score Entry</h3>
    <a href="index.php" class="btn btn-secondary btn-sm">← Back</a>
</div>

<h5>
Class: <?= htmlspecialchars($class_data['class_name']) ?> |
Term: <?= htmlspecialchars($term) ?> |
Subject: <?= htmlspecialchars($subject_data['subject_name']) ?> |
Year: <?= $academic_year ?>
</h5>

<form method="POST">

<table class="table table-bordered mt-3">
<thead>
<tr>
    <th>Student</th>
    <th>Class Score</th>
    <th>Exam Score</th>
</tr>
</thead>

<tbody>

<?php while($st = pg_fetch_assoc($students)) { ?>
<tr>
    <td>
        <?= htmlspecialchars($st['first_name'] . " " . $st['last_name']) ?>

       

        <input type="hidden" name="student_id[]" value="<?= $st['student_id'] ?>">
    </td>

    <td>
        <input type="number" 
               name="class_score[]" 
               class="form-control"
               value="<?= $st['class_score'] ?? '' ?>"
               min="0" max="30" >
    </td>

    <td>
        <input type="number" 
               name="exam_score[]" 
               class="form-control"
               value="<?= $st['exam_score'] ?? '' ?>"
               min="0" max="100" >
    </td>
</tr>
<?php } ?>

</tbody>
</table>

<div class="text-center mt-3">
    <button class="btn btn-success px-5">💾 Save </button>
</div>

</form>

</div>
</div>