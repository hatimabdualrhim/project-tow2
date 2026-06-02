<?php
require_once 'db.php';

$message = '';
$msg_type = ''; // success أو error للتحكم بلون التنبيه بالـ CSS

// --- أولاً: معالجة عملية الإضافة (Create) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // جلب البيانات وتحصينها من ثغرات XSS بسيطة عبر تصفية الفراغات
    $name = trim($_POST['student_name']);
    $email = trim($_POST['email']);
    $student_number = trim($_POST['student_number']);
    $year_of_study = intval($_POST['year_of_study']);
    $batch_name = trim($_POST['batch_name']);

    // التحقق من صحة المدخلات (Validation)
    if (empty($name) || empty($email) || empty($student_number) || empty($year_of_study) || empty($batch_name)) {
        $message = "جميع الحقول مطلوبة!";
        $msg_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "صيغة البريد الإلكتروني غير صحيحة!";
        $msg_type = "error";
    } else {
        try {
            // استخدام Prepared Statements لحماية البيانات من SQL Injection
            $stmt = $pdo->prepare("INSERT INTO students (student_name, email, student_number, year_of_study, batch_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $student_number, $year_of_study, $batch_name]);
            
            $message = "تم تسجيل الطالب بنجاح!";
            $msg_type = "success";
        } catch (\PDOException $e) {
            // التحقق من تكرار المفاتيح الفريدة (Email أو Student Number)
            if ($e->getCode() == 23000) {
                $message = "خطأ: البريد الإلكتروني أو الرقم الجامعي مسجل مسبقاً!";
                $msg_type = "error";
            } else {
                $message = "حدث خطأ غير متوقع: " . $e->getMessage();
                $msg_type = "error";
            }
        }
    }
}

// --- ثانياً: معالجة عملية الحذف (Delete) ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        $message = "تم حذف سجل الطالب بنجاح!";
        $msg_type = "success";
        // إعادة التوجيه لتحديث الصفحة وتنظيف الرابط من الـ GET parameter
        header("Location: index.php?msg=" . urlencode($message) . "&type=" . $msg_type);
        exit;
    } catch (\PDOException $e) {
        $message = "فشلت عملية الحذف: " . $e->getMessage();
        $msg_type = "error";
    }
}

// جلب التنبيهات بعد إعادة التوجيه إن وجدت
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = $_GET['msg'];
    $msg_type = $_GET['type'];
}

// --- ثالثاً: جلب البيانات الحية لعرضها (Read) ---
$students = [];
try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
    $students = $stmt->fetchAll();
} catch (\PDOException $e) {
    $message = "فشل جلب البيانات: " . $e->getMessage();
    $msg_type = "error";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نظام تسجيل الطلاب</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h2>عداد الطالب حاتم
        <h2>نظام تسجيل الطلاب المتكامل</h2>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?= $msg_type == 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h3>تسجيل طالب جديد</h3>
            <form action="index.php" method="POST">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label>اسم الطالب:</label>
                    <input type="text" name="student_name" required>
                </div>
                <div class="form-group">
                    <label>البريد الإلكتروني:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>الرقم الجامعي:</label>
                    <input type="text" name="student_number" required>
                </div>
                <div class="form-group">
                    <label>سنة الدراسة:</label>
                    <input type="number" name="year_of_study" min="1" max="2040" required>
                </div>
                <div class="form-group">
                    <label>اسم الدفعة :</label>
                    <input type="text" name="batch_name" required>
                </div>
                
                <button type="submit" class="btn-submit">تسجيل الطالب</button>
            </form>
        </div>

        <hr>

        <div class="table-container">
            <h3>الطلاب المسجلين حالياً</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الرقم الجامعي</th>
                        <th>السنة الدراسية</th>
                        <th>الدفعة</th>
                        <th>تاريخ التسجيل</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['id']) ?></td>
                                <td><?= htmlspecialchars($student['student_name']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= htmlspecialchars($student['student_number']) ?></td>
                                <td><?= htmlspecialchars($student['year_of_study']) ?></td>
                                <td><?= htmlspecialchars($student['batch_name']) ?></td>
                                <td><?= htmlspecialchars($student['created_at']) ?></td>
                                <td>
                                    <a href="index.php?delete_id=<?= $student['id'] ?>" class="btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذا الطالب؟');">حذف</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">لا يوجد طلاب مسجلين حتى الآن.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>