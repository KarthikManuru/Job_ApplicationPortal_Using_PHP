<?php
session_start();
include 'dp_connection.php'; // Ensure the connection file exists and is correct

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$query = "SELECT * FROM profiles WHERE user_id = '$userId'";
$result = mysqli_query($conn, $query);
$profile = mysqli_fetch_assoc($result);

$message = ""; // Initialize a message variable for feedback

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uploadDir = 'uploads/';

    // Handle profile picture update
    if (!empty($_FILES['profile_image']['name'])) {
        $oldImage = $profile['profile_image'];
        $profileImage = $_FILES['profile_image']['name'];
        $profileImageTemp = $_FILES['profile_image']['tmp_name'];
        $profileImagePath = $uploadDir . basename($profileImage);

        if (move_uploaded_file($profileImageTemp, $profileImagePath)) {
            // Delete the old image
            if ($oldImage && file_exists($oldImage)) {
                unlink($oldImage);
            }
        }
    }

    // Handle resume update
    if (!empty($_FILES['resume']['name'])) {
        $oldResume = $profile['resume'];
        $resume = $_FILES['resume']['name'];
        $resumeTemp = $_FILES['resume']['tmp_name'];
        $resumePath = $uploadDir . basename($resume);

        if (move_uploaded_file($resumeTemp, $resumePath)) {
            // Delete the old resume
            if ($oldResume && file_exists($oldResume)) {
                unlink($oldResume);
            }
        }
    }

    // Update the database
    $query = "UPDATE profiles SET 
              profile_image='$profileImagePath', 
              resume='$resumePath' 
              WHERE user_id='$userId'";
    if (mysqli_query($conn, $query)) {
        $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
        header("Refresh: 2; URL=export_profile.php"); // Redirect after 2 seconds
    } else {
        $message = "<div class='alert alert-danger'>Database error: " . mysqli_error($conn) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .card img {
            border-radius: 50%;
            max-width: 120px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Update Your Profile</h4>
                    </div>
                    <div class="card-body">
                        <!-- Display Success/Error Messages -->
                        <?php echo $message; ?>

                        <!-- Profile Update Form -->
                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <!-- Display current profile image -->
                                <?php if (!empty($profile['profile_image'])): ?>
                                    <img src="<?php echo $profile['profile_image']; ?>" alt="Profile Picture" class="img-thumbnail">
                                <?php else: ?>
                                    <img src="default_profile.png" alt="Default Profile Picture" class="img-thumbnail">
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Update Profile Picture:</label>
                                <input type="file" name="profile_image" id="profile_image" class="form-control">
                                <small class="form-text text-muted">Supported formats: JPG, PNG</small>
                            </div>
                            <div class="mb-3">
                                <label for="resume" class="form-label">Update Resume:</label>
                                <a href="<?php echo $profile['resume']; ?>" download class="d-block mb-2">Download Current Resume</a>
                                <input type="file" name="resume" id="resume" class="form-control">
                                <small class="form-text text-muted">Supported format: PDF</small>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="export_profile.php" class="btn btn-link">Export Profile as JSON</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
