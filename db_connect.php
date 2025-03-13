<?php
// Database Configuration
$servername = "localhost";
$username = "projectcoder";     // Default XAMPP username
$password = "admin";         // Default XAMPP password
$database = "studentinfo";

// Create Connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check Connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create Database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS studentinfo";
if (mysqli_query($conn, $sql)) {
    // Select the database
    mysqli_select_db($conn, $database);
    
    // Create Students Table
    $sql = "CREATE TABLE IF NOT EXISTS students (
            student_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(30) NOT NULL,
            last_name VARCHAR(30) NOT NULL,
            email VARCHAR(50) UNIQUE,
            phone VARCHAR(15),
            enrollment_date DATE,
            course_enrolled VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    
    if (!mysqli_query($conn, $sql)) {
        die("Error creating students table: " . mysqli_error($conn));
    }

    // Create Courses Table (from previous example)
    $sql = "CREATE TABLE IF NOT EXISTS courses (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            course_code VARCHAR(20) NOT NULL UNIQUE,
            course_title VARCHAR(100) NOT NULL,
            category VARCHAR(50),
            l INT(2),
            t INT(2),
            p INT(2),
            credit INT(2),
            year INT(1),
            semester INT(1),
            internal INT(3),
            external INT(3),
            total INT(3),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    
    if (!mysqli_query($conn, $sql)) {
        die("Error creating courses table: " . mysqli_error($conn));
    }

} else {
    die("Error creating database: " . mysqli_error($conn));
}
?>