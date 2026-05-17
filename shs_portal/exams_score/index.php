<?php
session_start();

include('../db/connect.php');
include('../header.php');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Fetch Classes
|--------------------------------------------------------------------------
*/
$classes = pg_query($conn, "
    SELECT class_id, class_name
    FROM classes
    ORDER BY class_name
");

/*
|--------------------------------------------------------------------------
| Fetch Unique Subjects Only
|--------------------------------------------------------------------------
| This prevents subjects like English appearing multiple times
| even if they belong to different departments.
|--------------------------------------------------------------------------
*/
$subjects = pg_query($conn, "
    SELECT 
        MIN(subject_id) AS subject_id,
        subject_name
    FROM subjects
    GROUP BY subject_name
    ORDER BY subject_name
");
?>

<div class="main-content p-4">

    <div class="card p-4 shadow">

        <h3 class="mb-4">📊 Select Exam Setup</h3>

        <form method="GET" action="enter_scores.php">

            <div class="row g-3">

                <!-- Class -->
                <div class="col-md-4">
                    <label class="form-label">Class</label>

                    <select name="class_id" class="form-select" required>
                        <option value="">Select Class</option>

                        <?php while($c = pg_fetch_assoc($classes)) { ?>

                            <option value="<?= $c['class_id'] ?>">
                                <?= htmlspecialchars($c['class_name']) ?>
                            </option>

                        <?php } ?>
                    </select>
                </div>

                <!-- Subject -->
                <div class="col-md-4">
                    <label class="form-label">Subject</label>

                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>

                        <?php while($s = pg_fetch_assoc($subjects)) { ?>

                            <option value="<?= $s['subject_id'] ?>">
                                <?= htmlspecialchars($s['subject_name']) ?>
                            </option>

                        <?php } ?>
                    </select>
                </div>

                <!-- Term -->
                <div class="col-md-4">
                    <label class="form-label">Term</label>

                    <select name="term" class="form-select" required>
                        <option value="">Select Term</option>
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2">Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                </div>

            </div>

            <!-- Submit Button -->
            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-primary px-5">
                    Continue ➜
                </button>
            </div>

        </form>

    </div>

</div>