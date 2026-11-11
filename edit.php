
<?php
require_once "connect.php";

// Lay ma giang vien tu URL
$id = trim($_GET["id"] ?? "");

if ($id == "") {
    die("Khong tim thay ma giang vien!");
}

$thongbao = "";

// Lay thong tin giang vien tu MySQL
$sql = "SELECT * FROM GiangVien WHERE IDGV = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$gv = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$gv) {
    die("Giang vien khong ton tai!");
}

// Xu ly khi nhan nut Luu thay doi
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST["Fullname"] ?? "");
    $sumclass = $_POST["SumClass"] ?? "";

    $oldImage = basename($gv["Image"] ?? "");
    $newImage = $oldImage;
    $uploadedImage = "";

    // Kiem tra thong tin
    if (
        $fullname == "" ||
        filter_var($sumclass, FILTER_VALIDATE_INT) === false ||
        (int)$sumclass < 0
    ) {
        $thongbao = "Vui long nhap thong tin hop le!";
    } else {

        // Kiem tra nguoi dung co chon anh moi khong
        if (
            isset($_FILES["Image"]) &&
            $_FILES["Image"]["error"] != UPLOAD_ERR_NO_FILE
        ) {

            $file = $_FILES["Image"];

            if ($file["error"] != UPLOAD_ERR_OK) {

                $thongbao = "Upload anh that bai!";

            } else {

                // Dinh dang anh cho phep
                $allowed = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                // Kiem tra dung luong
                if ($file["size"] > 2 * 1024 * 1024) {

                    $thongbao = "Anh khong duoc vuot qua 2MB!";

                } else {

                    // Kiem tra dinh dang anh
                    $mime = mime_content_type($file["tmp_name"]);

                    if (
                        !isset($allowed[$mime]) ||
                        getimagesize($file["tmp_name"]) === false
                    ) {

                        $thongbao = "Chi chap nhan anh JPG, PNG, WEBP!";

                    } else {

                        // Tao ten anh ngau nhien
                        $uploadedImage = bin2hex(random_bytes(16))
                            . "." . $allowed[$mime];

                        // Thu muc luu anh
                        $folder = __DIR__ . "/uploads/";

                        if (!is_dir($folder)) {
                            mkdir($folder, 0755, true);
                        }

                        // Luu anh moi
                        if (move_uploaded_file(
                            $file["tmp_name"],
                            $folder . $uploadedImage
                        )) {

                            $newImage = $uploadedImage;

                        } else {

                            $thongbao = "Khong the luu anh moi!";
                        }
                    }
                }
            }
        }

        // Cap nhat MySQL neu khong co loi
        if ($thongbao == "") {

            $sql = "UPDATE GiangVien
                    SET Fullname = ?,
                        Image = ?,
                        SumClass = ?
                    WHERE IDGV = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssis",
                $fullname,
                $newImage,
                $sumclass,
                $id
            );

            try {

                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                // Xoa anh cu neu da thay anh moi
                if (
                    $uploadedImage != "" &&
                    $oldImage != "" &&
                    $oldImage != $newImage
                ) {

                    // Kiem tra anh cu co duoc su dung boi GV khac
                    $checkSql = "SELECT COUNT(*) AS total
                                 FROM GiangVien
                                 WHERE Image = ?";

                    $checkStmt = mysqli_prepare($conn, $checkSql);
                    mysqli_stmt_bind_param(
                        $checkStmt,
                        "s",
                        $oldImage
                    );

                    mysqli_stmt_execute($checkStmt);

                    $checkResult = mysqli_stmt_get_result($checkStmt);
                    $checkRow = mysqli_fetch_assoc($checkResult);
                    mysqli_stmt_close($checkStmt);

                    if ((int)$checkRow["total"] === 0) {

                        $oldPath = __DIR__ . "/uploads/" . $oldImage;

                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                }

                header("Location: index.php?updated=1");
                exit;

            } catch (mysqli_sql_exception $e) {

                $thongbao = "Cap nhat that bai!";

                // Xoa anh vua upload neu database bi loi
                if ($uploadedImage != "") {
                    $newPath = __DIR__ . "/uploads/" . $uploadedImage;

                    if (is_file($newPath)) {
                        unlink($newPath);
                    }
                }
            }
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

    <title>Sửa thông tin giảng viên</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <h1>SỬA THÔNG TIN GIẢNG VIÊN</h1>

    <?php if ($thongbao != ""): ?>

        <p style="color:red; text-align:center;">
            <?= htmlspecialchars($thongbao) ?>
        </p>

    <?php endif; ?>

    <form method="POST"
          enctype="multipart/form-data">

        <!-- MA GIANG VIEN -->
        <label>Mã giảng viên:</label>

        <input type="text"
               value="<?= htmlspecialchars($gv["IDGV"]) ?>"
               readonly>

        <!-- HO TEN -->
        <label>Họ và tên:</label>

        <input type="text"
               name="Fullname"
               maxlength="100"
               value="<?= htmlspecialchars($_POST["Fullname"] ?? $gv["Fullname"]) ?>"
               required>

        <!-- TONG SO LOP -->
        <label>Tổng số lớp:</label>

        <input type="number"
               name="SumClass"
               min="0"
               value="<?= htmlspecialchars((string)($_POST["SumClass"] ?? $gv["SumClass"])) ?>"
               required>

        <!-- ANH HIEN TAI -->
        <label>Ảnh giảng viên hiện tại:</label>

        <?php
        $currentImage = basename($gv["Image"] ?? "");
        $currentPath = __DIR__ . "/uploads/" . $currentImage;
        ?>

        <?php if (
            $currentImage !== "" &&
            is_file($currentPath)
        ): ?>

            <div style="margin:15px 0; text-align:center;">

                <img
                    src="uploads/<?= htmlspecialchars(rawurlencode($currentImage)) ?>"
                    class="gv-image"
                    alt="Ảnh giảng viên">

            </div>

        <?php else: ?>

            <p style="color:gray;">
                Giảng viên chưa có ảnh.
            </p>

        <?php endif; ?>

        <!-- CHON ANH MOI -->
        <label>Chọn ảnh mới:</label>

        <input type="file"
               name="Image"
               accept=".jpg,.jpeg,.png,.webp">

        <p style="font-size:13px; color:gray;">
            Để trống nếu không muốn thay ảnh.
            Chỉ chấp nhận JPG, PNG, WEBP (tối đa 2MB).
        </p>

        <!-- NUT LUU -->
        <button type="submit">
            Lưu thay đổi
        </button>

        <!-- QUAY LAI -->
        <a href="index.php">
            Quay lại danh sách giảng viên
        </a>

    </form>

</div>

</body>
</html>
