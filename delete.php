
<?php
require_once "connect.php";

// Chi cho phep xoa bang POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Lay ma giang vien
$id = trim($_POST["id"] ?? "");

if ($id == "") {
    die("Khong tim thay ma giang vien!");
}

// Tim thong tin giang vien
$sql = "SELECT Image FROM GiangVien WHERE IDGV = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$gv = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$gv) {
    die("Giang vien khong ton tai!");
}

// Xoa giang vien khoi MySQL
$sql = "DELETE FROM GiangVien WHERE IDGV = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);

try {
    mysqli_stmt_execute($stmt);
    $deleted = mysqli_stmt_affected_rows($stmt);
} catch (mysqli_sql_exception $e) {
    mysqli_stmt_close($stmt);
    header("Location: index.php?error=delete");
    exit;
}

mysqli_stmt_close($stmt);

// Xoa anh da upload neu co
if ($deleted > 0 && !empty($gv["Image"])) {

    $filename = basename($gv["Image"]);
    $filepath = __DIR__ . "/uploads/" . $filename;

    if (is_file($filepath)) {
        unlink($filepath);
    }
}

mysqli_close($conn);

// Quay ve danh sach
header("Location: index.php?deleted=1");
exit;
?>
