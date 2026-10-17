
<?php
$host = "sql304.infinityfree.com";
$username = "if0_43202198";
$password = "PRomise0";
$database = "if0_43202198_XXX";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");
?>
