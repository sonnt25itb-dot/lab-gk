
<?php
// Thong tin ket noi MySQL
$host = "localhost";
$user = "root";
$password = "";
$database = "QLHocPhan";

// Tao ket noi
$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

// Kiem tra ket noi
if (!$conn) {
    die("Ket noi that bai: " . mysqli_connect_error());
}

// Cai dat tieng Viet
mysqli_set_charset($conn, "utf8mb4");
?>
