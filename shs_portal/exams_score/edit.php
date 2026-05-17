<?php
ob_start();
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Get teacher ID
$id = $_GET['id'] ?? 0;
$success = "";
$error = "";

// Fetch teacher info
$query = "SELECT * FROM teachers WHERE id=$1";
$result = pg_query_params($conn, $query, [$id]);
$teacher = pg_fetch_assoc($result);

if (!$teacher) {
    echo "<div class='alert alert-danger'>Teacher not found.</div>";
    exit();
}

// Prepare photo for preview
if (!empty($teacher['photo'])) {
    $photoData = pg_unescape_bytea($teacher['photo']); // unescape bytea
    $photoSrc = 'data:image/jpeg;base64,' . base64_encode($photoData);
    $placeholderStyle = 'display:none;';
} else {
    $photoSrc = '';
    $placeholderStyle = '';
}

// Handle POST submission (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['date_of_birth'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $ssnit_no = $_POST['ssnit_no'];
    $gh_card_no = $_POST['gh_card_no'];
    $hire_date = $_POST['hire_date'];
    $department = $_POST['department'];
    $qualification = $_POST['qualification'];
    $experience_years = $_POST['experience_years'];
    $employment_status = $_POST['employment_status'];
    $salary = $_POST['salary'];
    $emergency_name = $_POST['emergency_name'];
    $emergency_phone = $_POST['emergency_phone'];

    // Handle photo upload
    $photoData = null;
    if (!empty($_FILES['photo']['tmp_name'])) {
        $photoData = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // Update query
    if ($photoData) {
        $photo_hex = bin2hex($photoData);
        $updateQuery = "UPDATE teachers SET 
            first_name=$1,last_name=$2,gender=$3,date_of_birth=$4,email=$5,phone=$6,
            address=$7,ssnit_no=$8,gh_card_no=$9,hire_date=$10,department=$11,
            qualification=$12,experience_years=$13,employment_status=$14,salary=$15,
            emergency_contact_name=$16,emergency_contact_phone=$17,photo=decode($18,'hex')
            WHERE id=$19";
        $params = [
            $fname,$lname,$gender,$dob,$email,$phone,$address,$ssnit_no,$gh_card_no,
            $hire_date,$department,$qualification,$experience_years,$employment_status,$salary,
            $emergency_name,$emergency_phone,$photo_hex,$id
        ];
    } else {
        $updateQuery = "UPDATE teachers SET 
            first_name=$1,last_name=$2,gender=$3,date_of_birth=$4,email=$5,phone=$6,
            address=$7,ssnit_no=$8,gh_card_no=$9,hire_date=$10,department=$11,
            qualification=$12,experience_years=$13,employment_status=$14,salary=$15,
            emergency_contact_name=$16,emergency_contact_phone=$17
            WHERE id=$18";
        $params = [
            $fname,$lname,$gender,$dob,$email,$phone,$address,$ssnit_no,$gh_card_no,
            $hire_date,$department,$qualification,$experience_years,$employment_status,$salary,
            $emergency_name,$emergency_phone,$id
        ];
    }

    $res = pg_query_params($conn, $updateQuery, $params);

    if ($res) {
        header("Location: index.php?updated=1");
        exit();
    } else {
        $error = "❌ Error updating teacher: " . pg_last_error($conn);
    }
}
ob_end_flush();
?>

<div class="main-content p-4">
    <div class="card p-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-primary">✏️ Edit Teacher</h3>
            <a href="../teachers/index.php" class="btn btn-secondary btn-sm">← Back</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <h5 class="form-section-title mb-3">Teacher Information</h5>
            <div class="row g-3">
                <!-- Photo -->
                <div class="col-md-2 text-center">
                    <div id="photoContainer" class="position-relative mx-auto d-flex justify-content-center align-items-center"
                         style="width:120px; height:120px; border-radius:50%; border:3px solid #007bff; overflow:hidden; cursor:pointer;">
                        <img id="preview" src="<?= $photoSrc ?>" 
                             alt="Teacher Photo" style="width:100%; height:100%; object-fit:cover; <?= empty($photoSrc) ? 'display:none;' : '' ?>">
                        <div id="placeholderText" class="position-absolute text-center text-muted fw-semibold" 
                             style="font-size:0.8rem; <?= empty($photoSrc) ? '' : 'display:none;' ?>">
                             Upload<br>Photo
                        </div>
                    </div>
                    <input type="file" name="photo" id="photo" accept="image/*" style="display:none;">
                    <p class="text-muted mt-2 mb-0" style="font-size:0.85rem;">Click to upload</p>
                </div>

                <!-- Name & Gender -->
                <div class="col-md-10">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($teacher['first_name']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($teacher['last_name']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="">--Select--</option>
                                <option value="Male" <?= $teacher['gender']=='Male'?'selected':'' ?>>Male</option>
                                <option value="Female" <?= $teacher['gender']=='Female'?'selected':'' ?>>Female</option>
                                <option value="Other" <?= $teacher['gender']=='Other'?'selected':'' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Other fields prefilled like before -->
                <div class="col-md-4">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" required value="<?= $teacher['date_of_birth'] ?>">
                </div>
                <div class="col-md-4">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($teacher['email']) ?>">
                </div>
                <div class="col-md-4">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($teacher['phone']) ?>">
                </div>
                <div class="col-md-4">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($teacher['address']) ?></textarea>
                </div>
                <div class="col-md-4">
                    <label>SSNIT Number</label>
                    <input type="text" name="ssnit_no" class="form-control" value="<?= htmlspecialchars($teacher['ssnit_no']) ?>">
                </div>
                <div class="col-md-4">
                    <label>Ghana Card Number</label>
                    <input type="text" name="gh_card_no" class="form-control" value="<?= htmlspecialchars($teacher['gh_card_no']) ?>">
                </div>

                <div class="col-md-4">
                    <label>Hire Date</label>
                    <input type="date" name="hire_date" class="form-control" required value="<?= $teacher['hire_date'] ?>">
                </div>
                <div class="col-md-4">
                    <label>Department</label>
                    <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($teacher['department']) ?>">
                </div>
                <div class="col-md-4">
                    <label>Qualification</label>
                    <input type="text" name="qualification" class="form-control" value="<?= htmlspecialchars($teacher['qualification']) ?>">
                </div>
                <div class="col-md-4">
                    <label>Years of Experience</label>
                    <input type="number" name="experience_years" class="form-control" min="0" value="<?= $teacher['experience_years'] ?>">
                </div>
                <div class="col-md-4">
                    <label>Employment Status</label>
                    <select name="employment_status" class="form-select">
                        <option value="Active" <?= $teacher['employment_status']=='Active'?'selected':'' ?>>Active</option>
                        <option value="Resigned" <?= $teacher['employment_status']=='Resigned'?'selected':'' ?>>Resigned</option>
                        <option value="Retired" <?= $teacher['employment_status']=='Retired'?'selected':'' ?>>Retired</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Salary</label>
                    <input type="number" step="0.01" name="salary" class="form-control" value="<?= $teacher['salary'] ?>">
                </div>

                <div class="col-md-6">
                    <label>Emergency Contact Name</label>
                    <input type="text" name="emergency_name" class="form-control" value="<?= htmlspecialchars($teacher['emergency_contact_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label>Emergency Contact Phone</label>
                    <input type="text" name="emergency_phone" class="form-control" value="<?= htmlspecialchars($teacher['emergency_contact_phone']) ?>">
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-5">💾 Update Teacher</button>
            </div>
        </form>
    </div>
</div>

<footer class="mt-5 text-center">
    © <?= date('Y') ?> Teacher Information System | All Rights Reserved
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const photoContainer = document.getElementById('photoContainer');
const fileInput = document.getElementById('photo');
const preview = document.getElementById('preview');
const placeholderText = document.getElementById('placeholderText');

photoContainer.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        placeholderText.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
