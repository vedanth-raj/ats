<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Change as per your MySQL setup
$password = ""; // Change as per your MySQL setup - leave empty if no password set
$dbname = "campus_voting";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Firebase configuration
$firebaseProjectId = "voicelt-90822"; // Replace with your actual Firebase project ID
$firebaseURL = "https://$firebaseProjectId.firebaseio.com";

// Admin credentials (in production, use hashed passwords)
$adminUsername = "admin";
$adminPassword = "admin123"; // Change this to a secure password
?>
