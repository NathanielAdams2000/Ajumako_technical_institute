<?php
ob_start(); // start output buffering
session_start();
include('../db/connect.php');
include('../header.php');

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables so PHP doesn't complain
$success = "";
$error = "";

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Student Info ---
    $fname = $_POST['first_name'];
	$oname = $_POST['other_name'];
    $lname = $_POST['last_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $class_id = $_POST['class_id'];
    $admission_date = $_POST['admission_date'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
	$department_id = $_POST['department_id'];

    // --- Handle Image Upload ---
    $photoData = null;
    if (!empty($_FILES['photo']['tmp_name'])) {
        $photoData = file_get_contents($_FILES['photo']['tmp_name']);
    }

    // --- Parent Info ---
    $pname = $_POST['parent_name'];
    $relation = $_POST['relationship'];
    $pphone = $_POST['parent_phone'];
    $pemail = $_POST['parent_email'];
    $paddress = $_POST['parent_address'];

    // --- Insert student ---
    if ($photoData) {
        $query = "INSERT INTO students 
                  (first_name, other_name, last_name, gender, date_of_birth, class_id, admission_date, address, phone, email , department_id, photo)
                  VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12) RETURNING student_id";
        $params = [$fname, $oname, $lname, $gender, $dob, $class_id, $admission_date, $address, $phone, $email, $department_id, pg_escape_bytea($photoData)];
    } else {
        $query = "INSERT INTO students 
                  (first_name, other_name, last_name, gender, date_of_birth, class_id, admission_date, address, phone, email,department_id)
                  VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11) RETURNING student_id";
        $params = [$fname, $oname, $lname, $gender, $dob, $class_id, $admission_date, $address, $phone, $email, $department_id];
    }
$result = pg_query_params($conn, $query, $params);

if ($result) {
    $row = pg_fetch_assoc($result);
    $student_id = $row['student_id']; // ✅ safer than pg_fetch_result

    if (!empty($pname)) {
        $pquery = "INSERT INTO parents (student_id, full_name, relationship, phone, email, address)
                   VALUES ($1,$2,$3,$4,$5,$6)";
        pg_query_params($conn, $pquery, [
            $student_id, $pname, $relation, $pphone, $pemail, $paddress
        ]);
    }

        // ✅ Redirect to index after saving
        header("Location: index.php?added=1");
        exit();
    } else {
        $error = "❌ Error saving student: " . pg_last_error($conn);
    }
}
$classes = pg_query($conn, "SELECT class_id, class_name FROM classes ORDER BY class_name");

$department = pg_query($conn, "SELECT department_id, department_name FROM department ORDER BY department_name");
?>
<?php
ob_end_flush();
?>



<div class="main-content p-4">
    <div class="card p-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0 text-primary">🎓 Add New Student</h3>
            <a href="../students/index.php" class="btn btn-secondary btn-sm">← Back</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- Student Info -->
            <h5 class="form-section-title mb-3">Student Information</h5>

            <div class="row g-3 align-items-start">
                <!-- Left: Image -->
        <div class="row g-3 align-items-center">
    <!-- Left: Photo -->
    <div class="col-md-2 text-center">
        <div id="photoContainer"
             class="position-relative mx-auto d-flex justify-content-center align-items-center"
             style="width:120px; height:120px; border-radius:50%; border:3px solid #007bff; overflow:hidden; cursor:pointer;">
            
            <img id="preview"
                 src=""
                 alt="Student Photo"
                 style="width:100%; height:100%; object-fit:cover; display:none;">
            
            <div id="placeholderText"
                 class="position-absolute text-center text-muted fw-semibold"
                 style="font-size:0.8rem;">
                 Upload<br>Photo
            </div>
        </div>
        <input type="file" name="photo" id="photo" accept="image/*" style="display:none;">
        <p class="text-muted mt-2 mb-0" style="font-size:0.85rem;">Click to upload</p>
    </div>

    <!-- Right: First Name, Last Name, Gender on same row -->
    <div class="col-md-10">
        <div class="row g-3">
            <div class="col-md-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
			
			            <div class="col-md-3">
                <label>Other Name</label>
                <input type="text" name="other_name" class="form-control" >
            </div>
			
            <div class="col-md-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="">--Select--</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Next row -->
    <div class="col-md-6">
        <label>Date of Birth</label>
        <input type="date" name="dob" class="form-control" required>
    </div>

    <div class="col-md-4">
                    <label>Class</label>
                    <select name="class_id" class="form-select border" required>
                        <option value="">-- Select Class --</option>
                        <?php while($c = pg_fetch_assoc($classes)) {
                            $selected = ($c['class_id'] == $subject['class_id']) ? "selected" : "";
                            echo "<option value='{$c['class_id']}' $selected>{$c['class_name']}</option>";
                        } ?>
                    </select>
                </div>

   <div class="col-md-6">
    <label>Admission Date</label>
    <input type="date" name="admission_date" class="form-control" required>
</div>

	

    <div class="col-md-6">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control">
    </div>
    <div class="col-md-6">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
    </div>
	
	<div class="col-md-6">
        <label>Department</label>
		 <select name="department_id" class="form-select border" required>
                        <option value="">-- Select Department --</option>
                        <?php while($c = pg_fetch_assoc($department)) {
                            $selected = ($c['department_id'] == $subject['department_id']) ? "selected" : "";
                            echo "<option value='{$c['department_id']}' $selected>{$c['department_name']}</option>";
                        } ?>
                    </select>
    </div>
	
	    <div class="col-md-8">
        <label>Address</label>
        <textarea name="address" class="form-control" rows="2"></textarea>
    </div>
  


</div>



            <!-- Parent Info -->
            <div class="mt-4">
                <h5 class="mb-0 text-primary">Parent / Guardian Information</h5>
                <button class="btn btn-outline-primary btn-sm mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#parentForm">
                    ➕ Add Parent/Guardian Details
                </button>

                <div class="collapse" id="parentForm">
                    <div class="card card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Full Name</label>
                                <input type="text" name="parent_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Relationship</label>
                                <input type="text" name="relationship" class="form-control" placeholder="e.g., Father, Aunt, Guardian">
                            </div>
                            <div class="col-md-6">
                                <label>Phone</label>
                                <input type="text" name="parent_phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="parent_email" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label>Address</label>
                                <textarea name="parent_address" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-5">💾 Save Student</button>
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

// Make entire circle clickable
photoContainer.addEventListener('click', () => fileInput.click());

// Show image preview once uploaded
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        placeholderText.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

// Attach event
fileInput.addEventListener('change', previewImage);
</script>
