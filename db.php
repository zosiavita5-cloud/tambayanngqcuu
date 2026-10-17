
<?php
$host = "sql203.infinityfree.com";
$username = "if0_43203117";
$password = "LeiBajande1";
$database = "if0_43203117_mindcapsule";

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Sorry, the website is temporarily unavailable.");
}

$conn->set_charset("utf8mb4");
?>
