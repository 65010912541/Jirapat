<?php
// ====== ตั้งค่าการเชื่อมต่อฐานข้อมูล ======
$host = "localhost";
$user = "root";
$pass = "1234";     // ห้ามมีช่องว่างท้าย
$db   = "2541db";   // ถ้าของคุณชื่อ 2541 ให้แก้เป็น "2541"

$conn = mysqli_connect($host, $user, $pass, $db);
mysqli_set_charset($conn, "utf8mb4");

if (!$conn) {
  die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . mysqli_connect_error());
}

// ====== เพิ่ม/บันทึกข้อมูล ======
if (isset($_POST['submit'])) {
  $rname = trim($_POST['rname']);

  if ($rname !== "") {
    $stmt = mysqli_prepare($conn, "INSERT INTO register (r_name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $rname);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // กันรีเฟรชแล้วบันทึกซ้ำ
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  }
}

// ====== ลบข้อมูล ======
if (isset($_GET['del'])) {
  $id = (int)$_GET['del'];
  mysqli_query($conn, "DELETE FROM register WHERE r_id = $id");

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>งาน 1</title>
  <style>
    body{ font-family: Tahoma, Arial, sans-serif; }
    table{ border-collapse: collapse; }
    th, td{ border:1px solid #000; padding:8px; }
    .trash-btn img{
      width: 24px;
      height: 24px;
      vertical-align: middle;
      cursor: pointer;
    }
  </style>
</head>
<body>

<h1>งาน 1-- นายจิรภัทร ดอกไม้ (เปรม)</h1>

<form method="post" action="">
  ชื่อภาค:
  <input type="text" name="rname" autofocus required>
  <button type="submit" name="submit">บันทึก</button>
</form>

<br>

<table>
  <tr>
    <th>ลำดับ</th>
    <th>ชื่อภาค</th>
    <th>ลบ</th>
  </tr>

<?php
$rs = mysqli_query($conn, "SELECT r_id, r_name FROM register ORDER BY r_id ASC");
$i = 1;

while ($data = mysqli_fetch_assoc($rs)) {
?>
  <tr>
    <td><?php echo $i++; ?></td>
    <td><?php echo htmlspecialchars($data['r_name']); ?></td>
    <td align="center">
      <a class="trash-btn"
         href="?del=<?php echo $data['r_id']; ?>"
         onclick="return confirm('ยืนยันลบรายการนี้?');"
         title="ลบ">
        <img src="img/pc.jpg" alt="ลบ">
      </a>
    </td>
  </tr>
<?php } ?>
</table>
