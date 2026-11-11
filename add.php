
<?php
require_once "connect.php";

$thongbao = "";

// Khi nguoi dung nhan nut Them
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $idgv = trim($_POST["IDGV"] ?? "");
    $fullname = trim($_POST["Fullname"] ?? "");
    $sumclass = $_POST["SumClass"] ?? "";

    // Kiem tra du lieu
    if ($idgv == "" || $fullname == "" ||
        filter_var($sumclass, FILTER_VALIDATE_INT) === false ||
        (int)$sumclass < 0) {

        $thongbao = "Vui long nhap thong tin hop le!";

    } else {

        $imageName = "";

        // Xu ly upload anh
        if (isset($_FILES["Image"]) &&
            $_FILES["Image"]["error"] == UPLOAD_ERR_OK) {

            $file = $_FILES["Image"];
            $allowed = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            $mime = mime_content_type($file["tmp_name"]);

            if ($file["size"] > 2 * 1024 * 1024 ||
                !isset($allowed[$mime]) ||
                getimagesize($file["tmp_name"]) === false) {

                $thongbao = "Anh khong hop le (toi da 2MB)!";

            } else {

                $imageName = bin2hex(random_bytes(16))
                    . "." . $allowed[$mime];

                $folder = __DIR__ . "/uploads/";

                if (!is_dir($folder)) {
                    mkdir($folder, 0755, true);
                }

                if (!move_uploaded_file(
                    $file["tmp_name"],
                    $folder . $imageName
                )) {
                    $thongbao = "Khong the luu anh!";
                }
            }

        } else {
            $thongbao = "Vui long chon anh!";
        }

        // Luu du lieu vao MySQL
        if ($thongbao == "") {

            $sql = "INSERT INTO GiangVien
                    (IDGV, Fullname, Image, SumClass)
                    VALUES (?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $idgv,
                $fullname,
                $imageName,
                $sumclass
            );

            try {
                mysqli_stmt_execute($stmt);
                header("Location: index.php?success=1");
                exit;
            } catch (mysqli_sql_exception $e) {
                $thongbao = "Khong the them giang vien. "
                    . "Hay kiem tra ma giang vien!";

                // Xoa anh neu them database that bai
                if ($imageName != "") {
                    @unlink($folder . $imageName);
                }
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Thêm giảng viên</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h1>THÊM GIẢNG VIÊN</h1>

    <?php if ($thongbao != ""): ?>
        <p style="color:red">
            <?= htmlspecialchars($thongbao) ?>
        </p>
    <?php endif; ?>

    <form action="add.php"
          method="POST"
          enctype="multipart/form-data">

        <label>Mã giảng viên:</label>
        <input type="text"
               name="IDGV"
               maxlength="20"
               required>

        <label>Họ và tên:</label>
        <input type="text"
               name="Fullname"
               maxlength="100"
               required>

        <label>Hình ảnh:</label>
        <input type="file"
               name="Image"
               accept=".jpg,.jpeg,.png,.webp"
               required>

        <label>Tổng số lớp:</label>
        <input type="number"
               name="SumClass"
               min="0"
               required>

        <button type="submit">
            Thêm giảng viên
        </button>

        <a href="index.php">
            Quay lại danh sách
        </a>

    </form>

</div>

</body>
</html>
