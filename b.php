<?php
// ================== ตั้งค่า DB ==================
$host = "localhost";
$user = "root";
$pass = "1234";
$db   = "2541db"; // ถ้าของคุณชื่อ 2541 ให้เปลี่ยนเป็น "2541"

$conn = mysqli_connect($host, $user, $pass, $db);
mysqli_set_charset($conn, "utf8mb4");
if (!$conn) die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . mysqli_connect_error());

// ================== ลบข้อมูล ==================
if (isset($_GET['del'])) {
  $pid = (int)$_GET['del'];

  // ลบไฟล์รูปก่อน
  $res = mysqli_query($conn, "SELECT p_image FROM province WHERE p_id=$pid");
  if ($res && $row = mysqli_fetch_assoc($res)) {
    $file = __DIR__ . "/" . $row['p_image'];
    if (is_file($file)) @unlink($file);
  }

  mysqli_query($conn, "DELETE FROM province WHERE p_id=$pid");
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// ================== เพิ่มข้อมูล + อัปโหลดรูป ==================
if (isset($_POST['submit'])) {
  $pname = trim($_POST['pname'] ?? "");
  $rid   = (int)($_POST['rid'] ?? 0);

  if ($pname === "" || $rid === 0) die("กรุณากรอกชื่อจังหวัดและเลือกภาค");

  if (!isset($_FILES['pimage']) || $_FILES['pimage']['error'] !== UPLOAD_ERR_OK) {
    die("กรุณาเลือกรูปภาพให้ถูกต้อง");
  }

  // โฟลเดอร์เก็บรูปจังหวัด
  $uploadDir = __DIR__ . "/upload/";
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

  // ตรวจว่าเป็นรูปจริง
  $tmp = $_FILES['pimage']['tmp_name'];
  if (getimagesize($tmp) === false) die("ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ");

  // ตรวจนามสกุล
  $ext = strtolower(pathinfo($_FILES['pimage']['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ["jpg", "jpeg", "png", "gif", "webp"])) die("รองรับแค่ jpg jpeg png gif webp");

  // ตั้งชื่อไฟล์ใหม่กันซ้ำ
  $newName = "p_" . time() . "_" . rand(1000, 9999) . "." . $ext;
  $dest    = $uploadDir . $newName;

  if (!move_uploaded_file($tmp, $dest)) die("อัปโหลดรูปไม่สำเร็จ");

  $imgPath = "upload/" . $newName;

  // บันทึกลง DB (มีภาค r_id ด้วย)
  $stmt = mysqli_prepare($conn, "INSERT INTO province (p_name, r_id, p_image) VALUES (?,?,?)");
  mysqli_stmt_bind_param($stmt, "sis", $pname, $rid, $imgPath);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_close($stmt);

  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// ================== ดึงภาคทำ dropdown ==================
$regions = mysqli_query($conn, "SELECT r_id, r_name FROM register ORDER BY r_id ASC");
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>งาน b</title>
  <style>
    body{ font-family: Tahoma, Arial, sans-serif; }
    .box{ width: 720px; }
    .row{ margin: 6px 0; }
    label{ display:inline-block; width: 90px; }
    input[type="text"]{ width: 320px; padding: 4px; }
    select{ width: 330px; padding: 4px; }
    table{ border-collapse: collapse; width: 720px; margin-top: 12px; }
    th,td{ border:1px solid #555; padding:8px; text-align:center; }
    th{ background:#f2f2f2; }
    .pic{ width: 220px; height: 120px; object-fit: cover; }
    .trash-btn img{ width: 26px; height: 26px; cursor:pointer; }
  </style>
</head>
<body>

<div class="box">
  <h1>งาน i -- จิรภัทร ดอกไม้ (เปรมชัย)</h1>

  <form method="post" action="" enctype="multipart/form-data">
    <div class="row">
      <label>ชื่อจังหวัด</label>
      <input type="text" name="pname" required>
    </div>

    <div class="row">
      <label>รูปภาพ</label>
      <input type="file" name="pimage" accept="image/*" required>
    </div>

    <div class="row">
      <label>ภาค</label>
      <select name="rid" required>
        <option value="">-- เลือกภาค --</option>
        <?php while($r = mysqli_fetch_assoc($regions)){ ?>
          <option value="<?php echo $r['r_id']; ?>">
            <?php echo htmlspecialchars($r['r_name']); ?>
          </option>
        <?php } ?>
      </select>
    </div>

    <div class="row">
      <button type="submit" name="submit">บันทึก</button>
    </div>
  </form>

  <table>
    <tr>
      <th>รหัสจังหวัด</th>
      <th>ชื่อจังหวัด</th>
      <th>ชื่อภาค</th>
      <th>รูป</th>
      <th>ลบ</th>
    </tr>

    <?php
    $rs = mysqli_query($conn, "
      SELECT p.p_id, p.p_name, p.p_image, r.r_name
      FROM province p
      JOIN register r ON p.r_id = r.r_id
      ORDER BY p.p_id ASC
    ");
    $i = 1; // ให้แสดง 1 2 3 4 ...
    while($d = mysqli_fetch_assoc($rs)){
    ?>
      <tr>
        <td><?php echo $i++; ?></td>
        <td><?php echo htmlspecialchars($d['p_name']); ?></td>
        <td><?php echo htmlspecialchars($d['r_name']); ?></td>
        <td><img class="pic" src="<?php echo htmlspecialchars($d['p_image']); ?>" alt=""></td>
        <td>
          <a class="trash-btn"
             href="?del=<?php echo $d['p_id']; ?>"
             onclick="return confirm('ยืนยันลบรายการนี้?');"
             title="ลบ">
            <img src="img/pc.jpg" alt="ลบ">
          </a>
        </td>
      </tr>
    <?php } ?>
  </table>

</div>

</body>
</html>
