<?php

$servername = "localhost";
$username = "projectcoder";     
$password = "admin";         
$database = "studentinfo";


$conn = mysqli_connect($servername, $username, $password, $database);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql = "CREATE DATABASE IF NOT EXISTS studentinfo";
if (mysqli_query($conn, $sql)) {
    
    mysqli_select_db($conn, $database);
    
    
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