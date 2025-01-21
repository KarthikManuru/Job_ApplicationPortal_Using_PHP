<?php
session_start();
include 'dp_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch profile data from the database
$query = "SELECT * FROM profiles WHERE user_id = '$userId'";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $profile = mysqli_fetch_assoc($result);

    // Convert profile data to JSON format
    $json = json_encode($profile, JSON_PRETTY_PRINT);

    // Set headers to trigger file download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="profile.json"');

    // Output the JSON data
    echo $json;
} else {
    echo "No profile data found for the user.";
}
?>
