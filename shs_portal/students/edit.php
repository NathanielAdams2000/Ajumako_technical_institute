<?php
ob_start(); // Start output buffering
include('../header.php'); // include header/sidebar
include('../db/connect.php');

// Get student ID
$id = $_GET['id'] ?? 0;

$success = "";
$error = "";

// Handle update BEFORE output
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
	$other_name = $_POST['other_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $class_id = $_POST['class_id'];
    $admission_date = $_POST['admission_date'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
	$department_id = $_POST['department_id'];

    $parent_name = $_POST['parent_name'];
    $relationship = $_POST['relationship'];
    $parent_phone = $_POST['parent_phone'];
    $parent_email = $_POST['parent_email'];
    $parent_address = $_POST['parent_address'];

    // Handle photo upload
    $photo_data = null;
    if (!empty($_FILES['photo']['tmp_name'])) {
        $photo_data = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // Update student with or without photo
    if ($photo_data) {
        $photo_hex = bin2hex($photo_data);
        $update_student = "UPDATE students SET 
            first_name=$1, other_name=$2, last_name=$3, gender=$4, date_of_birth=$5,
            class_id=$6, admission_date=$7, address=$8, phone=$9, email=$10,department_id =$11, photo=decode($12, 'hex')
            WHERE student_id=$13";
        $res_student = pg_query_params($conn, $update_student, [
            $first_name, $other_name, $last_name, $gender, $dob, $class_id, $admission_date, $address, $phone, $email,$department_id, $photo_hex, $id
        ]);
    } else {
        $update_student = "UPDATE students SET 
            first_name=$1, other_name=$2, last_name=$3, gender=$4, date_of_birth=$5,
            class_id=$6, admission_date=$7, address=$8, phone=$9, email=$10, department_id =$11
            WHERE student_id=$12";
        $res_student = pg_query_params($conn, $update_student, [
            $first_name, $other_name, $last_name, $gender, $dob, $class_id, $admission_date, $address, $phone, $email,$department_id, $id
        ]);
    }

    // Update or insert parent info
    $check_parent = pg_query_params($conn, "SELECT id FROM parents WHERE id=$1", [$id]);
    if (pg_num_rows($check_parent) > 0) {
        $update_parent = "UPDATE parents SET full_name=$1, relationship=$2, phone=$3, email=$4, address=$5 
                          WHERE id=$6";
        pg_query_params($conn, $update_parent, [
            $parent_name, $relationship, $parent_phone, $parent_email, $parent_address, $id
        ]);
    } else {
        $insert_parent = "INSERT INTO parents (id, full_name, relationship, phone, email, address)
                          VALUES ($1, $2, $3, $4, $5, $6)";
        pg_query_params($conn, $insert_parent, [
            $id, $parent_name, $relationship, $parent_phone, $parent_email, $parent_address
        ]);
    }

    if ($res_student) {
        header("Location: index.php?updated=1"); // redirect after save
        exit();
    } else {
        $error = "Failed to update record.";
    }
}

// Fetch student info including admission_date
$query = "SELECT s.*, s.admission_date, p.full_name AS parent_name, p.relationship, p.phone AS parent_phone, 
                 p.email AS parent_email, p.address AS parent_address
          FROM students s
          LEFT JOIN parents p ON s.student_id = p.student_id
          WHERE s.student_id= $1";
$classes = pg_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");

$department = pg_query($conn, "SELECT department_id, department_name FROM department ORDER BY department_name");


$result = pg_query_params($conn, $query, [$id]);
$student = pg_fetch_assoc($result);
?>
<?php ob_end_flush(); ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="main-content p-4">
    <div class="card p-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-primary">✏️ Edit Student</h3>
            <a href="index.php" class="btn btn-secondary btn-sm">← Back</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <h5 class="form-section-title mb-3">Student Information</h5>

            <div class="row g-3 align-items-start">
                <!-- 🖼 Left: Photo -->
                <div class="col-md-2 text-center">
                    <div id="photoContainer"
                         class="position-relative mx-auto d-flex justify-content-center align-items-center"
                         style="width:120px; height:120px; border-radius:50%; border:3px solid #007bff; overflow:hidden; cursor:pointer;">
                        <img id="preview"
                             src="<?= !empty($student['photo']) ? 'data:image/jpeg;base64,' . base64_encode(pg_unescape_bytea($student['photo'])) : '' ?>"
                             alt="Student Photo"
                             style="width:100%; height:100%; object-fit:cover; <?= !empty($student['photo']) ? '' : 'display:none;' ?>">
                        <div id="placeholderText"
                             class="position-absolute text-center text-muted fw-semibold"
                             style="font-size:0.8rem; <?= !empty($student['photo']) ? 'display:none;' : '' ?>">
                             Upload<br>Photo
                        </div>
                    </div>
                    <input type="file" name="photo" id="photo" accept="image/*" style="display:none;">
                    <p class="text-muted mt-2 mb-0" style="font-size:0.85rem;">Click to upload</p>
                </div>

                <!-- 🧍‍♂️ Right: First Name, Last Name, Gender -->
                <div class="col-md-10">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control"
                                   value="<?= htmlspecialchars($student['first_name']) ?>" required>
                        </div>
						
						<div class="col-md-3">
                            <label>Other Name</label>
                            <input type="text" name="other_name" class="form-control"
                                   value="<?= htmlspecialchars($student['other_name']) ?>" >
                        </div>
						
                        <div class="col-md-3">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control"
                                   value="<?= htmlspecialchars($student['last_name']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label>Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="">--Select--</option>
                                <option <?= $student['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option <?= $student['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 🗓 Other Fields -->
                <div class="col-md-6">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" class="form-control"
                           value="<?= htmlspecialchars($student['date_of_birth']) ?>" required>
                </div>

           <div class="col-md-4">
    <label>Class</label>
    <select name="class_id" class="form-select border" required>
        <option value="">-- Select Class --</option>

        <?php 
        $currentClass = $student['class_id']; // ✅ correct variable

        while($c = pg_fetch_assoc($classes)) {

            $selected = ($c['class_id'] == $currentClass) ? "selected" : "";

            echo "<option value='{$c['class_id']}' $selected>{$c['class_name']}</option>";
        }
        ?>
    </select>
</div>

        <div class="col-md-6">
                    <label>Admission date</label>
                    <input type="date" name="admission_date" class="form-control"
                           value="<?= htmlspecialchars($student['admission_date']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($student['phone']) ?>">
                </div>

                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($student['email']) ?>">
                </div>
				
			<div class="col-md-4">
    <label>Department</label>
    <select name="department_id" class="form-select border" required>
        <option value="">-- Select Department --</option>

        <?php 
        $currentDepartment = $student['department_id'] ?? '';

        while($d = pg_fetch_assoc($department)) {

            $selected = ($d['department_id'] == $currentDepartment) ? "selected" : "";

            echo "<option value='{$d['department_id']}' $selected>{$d['department_name']}</option>";
        }
        ?>
    </select>
</div>
				
				 <div class="col-md-8">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($student['address']) ?></textarea>
                </div>
            </div>

            <!-- 👨‍👩 Parent Info -->
            <div class="mt-4">
                <h5 class="mb-0 text-primary">Parent / Guardian Information</h5>
                <button class="btn btn-outline-primary btn-sm mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#parentForm">
                    👨‍👩‍👧 View / Edit Parent Details
                </button>

                <div class="collapse show" id="parentForm">
                    <div class="card card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Full Name</label>
                                <input type="text" name="parent_name" class="form-control"
                                       value="<?= htmlspecialchars($student['parent_name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Relationship</label>
                                <input type="text" name="relationship" class="form-control"
                                       value="<?= htmlspecialchars($student['relationship']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Phone</label>
                                <input type="text" name="parent_phone" class="form-control"
                                       value="<?= htmlspecialchars($student['parent_phone']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="parent_email" class="form-control"
                                       value="<?= htmlspecialchars($student['parent_email']) ?>">
                            </div>
                            <div class="col-md-12">
                                <label>Address</label>
                                <textarea name="parent_address" class="form-control"
                                          rows="2"><?= htmlspecialchars($student['parent_address']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 💾 Submit -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-5">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

<footer class="mt-5 text-center">
    © <?= date('Y') ?> Student Information System | All Rights Reserved
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const photoContainer = document.getElementById('photoContainer');
const fileInput = document.getElementById('photo');
const preview = document.getElementById('preview');
const placeholderText = document.getElementById('placeholderText');

photoContainer.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (placeholderText) placeholderText.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
</body>
</html>
