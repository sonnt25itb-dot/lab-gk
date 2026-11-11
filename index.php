
<?php
require_once "connect.php";

// Lay danh sach giang vien
$sql = "SELECT * FROM GiangVien ORDER BY IDGV ASC";
$result = mysqli_query($conn, $sql);

// Dem tong so giang vien
$total = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Quản lý giảng viên</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="main-container">

    <div class="header">
        <div>
            <h1>QUẢN LÝ GIẢNG VIÊN</h1>
            <p>Hệ thống quản lý học phần</p>
        </div>

        <a href="add.php" class="btn-add">
            + Thêm giảng viên
        </a>
    </div>

    <?php if (isset($_GET["success"])): ?>
        <div class="success-message">
            Thêm giảng viên thành công!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["updated"])): ?>
        <div class="success-message">
            Cập nhật giảng viên thành công!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["deleted"])): ?>
        <div class="success-message">
            Xóa giảng viên thành công!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div style="color:red; margin:15px 0;">
            Không thể thực hiện thao tác!
        </div>
    <?php endif; ?>

    <div class="stat-card">
        <h3>Tổng số giảng viên</h3>
        <h2><?= $total ?></h2>
    </div>

    <div class="table-wrapper">
        <table class="gv-table">

            <thead>
                <tr>
                    <th>Mã GV</th>
                    <th>Họ và tên</th>
                    <th>Hình ảnh</th>
                    <th>Tổng số lớp</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($total > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <tr>
                        <td>
                            <?= htmlspecialchars($row["IDGV"]) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row["Fullname"]) ?>
                        </td>

                        <td>
                            <?php
                            $image = basename($row["Image"] ?? "");
                            $imagePath = "uploads/" . $image;
                            ?>

                            <?php if ($image !== "" &&
                                is_file(__DIR__ . "/" . $imagePath)): ?>

                                <img
                                    src="<?= htmlspecialchars($imagePath) ?>"
                                    class="gv-image"
                                    alt="Ảnh giảng viên">

                            <?php else: ?>
                                <span>Chưa có ảnh</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= (int)$row["SumClass"] ?>
                        </td>

                        <td>
                            <div class="actions">

                                <!-- NUT SUA -->
                                <a class="btn-edit"
                                   href="edit.php?id=<?= urlencode($row["IDGV"]) ?>">
                                    Sửa
                                </a>

                                <!-- NUT XOA -->
                                <form action="delete.php"
                                      method="POST"
                                      class="delete-form"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên này không?');">

                                    <input type="hidden"
                                           name="IDGV"
                                           value="<?= htmlspecialchars($row["IDGV"], ENT_QUOTES, 'UTF-8') ?>">

                                    <button type="submit"
                                            class="btn-delete">
                                        Xóa
                                    </button>

                                </form>

                            </div>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="5">
                        Chưa có giảng viên nào!
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>

<?php
mysqli_free_result($result);
mysqli_close($conn);
?>
