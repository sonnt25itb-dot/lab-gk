
<?php
require_once "connect.php";

// Chi chap nhan yeu cau POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Nhan ma giang vien
// Ho tro ca IDGV va id
$id = trim($_POST["IDGV"] ?? $_POST["id"] ?? "");

if ($id === "") {
    die("Loi: Khong nhan duoc ma giang vien can xoa!");
}

// Lay thong tin anh truoc khi xoa
$sql = "SELECT Image FROM GiangVien WHERE IDGV = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Loi SELECT: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$gv = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$gv) {
    die("Khong tim thay giang vien co ma: " .
        htmlspecialchars($id, ENT_QUOTES, "UTF-8"));
}

// Xoa du lieu trong MySQL
$sql = "DELETE FROM GiangVien WHERE IDGV = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Loi DELETE: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $id);

try {
    mysqli_stmt_execute($stmt);
    $deleted = mysqli_stmt_affected_rows($stmt);
} catch (mysqli_sql_exception $e) {
    mysqli_stmt_close($stmt);
    die("Loi xoa: " .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8"));
}

mysqli_stmt_close($stmt);

if ($deleted <= 0) {
    die("Xoa that bai: Khong co du lieu nao duoc xoa!");
}

// Xoa anh neu khong con giang vien nao su dung
if (!empty($gv["Image"])) {
    $sql = "SELECT COUNT(*) AS total
            FROM GiangVien
            WHERE Image = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $gv["Image"]);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if ((int)$row["total"] === 0) {
        $filename = basename($gv["Image"]);
        $filepath = __DIR__ . "/uploads/" . $filename;

        if (is_file($filepath)) {
            unlink($filepath);
        }
    }
}

mysqli_close($conn);

// Chuyen ve trang danh sach
header("Location: index.php?deleted=1");
exit;
?>
