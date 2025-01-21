<?php
session_start();
include 'dp_connection.php'; // Ensure the connection file exists and is correct

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = ""; // Initialize a message variable for feedback

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id']; // Get the logged-in user's ID
    $uploadDir = 'uploads/';

    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle profile picture upload
    $profileImage = $_FILES['profile_image']['name'];
    $profileImageTemp = $_FILES['profile_image']['tmp_name'];
    $profileImagePath = $uploadDir . basename($profileImage);

    // Handle resume upload
    $resume = $_FILES['resume']['name'];
    $resumeTemp = $_FILES['resume']['tmp_name'];
    $resumePath = $uploadDir . basename($resume);

    // Move uploaded files
    if (move_uploaded_file($profileImageTemp, $profileImagePath) && move_uploaded_file($resumeTemp, $resumePath)) {
        // Insert or update the profile record in the database
        $query = "INSERT INTO profiles (user_id, profile_image, resume) 
                  VALUES ('$userId', '$profileImagePath', '$resumePath')
                  ON DUPLICATE KEY UPDATE 
                  profile_image='$profileImagePath', resume='$resumePath'";
        if (mysqli_query($conn, $query)) {
            $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
            header("Refresh: 2; URL=profile_update.php"); // Redirect after success
        } else {
            $message = "<div class='alert alert-danger'>Database error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Failed to upload files. Please try again.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Upload Your Profile</h4>
                    </div>
                    <div class="card-body">
                        <!-- Display Success/Error Messages -->
                        <?php echo $message; ?>

                        <!-- Profile Upload Form -->
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Profile Picture:</label>
                                <input type="file" name="profile_image" id="profile_image" class="form-control" required>
                                <small class="form-text text-muted">Supported formats: JPG, PNG</small>
                            </div>
                            <div class="mb-3">
                                <label for="resume" class="form-label">Resume (PDF only):</label>
                                <input type="file" name="resume" id="resume" class="form-control" required>
                                <small class="form-text text-muted">Supported format: PDF</small>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="profile_update.php" class="btn btn-link">Go to Profile Update</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
