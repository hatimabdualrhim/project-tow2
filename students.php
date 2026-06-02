<?php
require_once 'db.php';

$message = '';
$msg_type = '';

// معالجة عملية الحذف (Delete Action)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        $message = "تم حذف سجل الطالب بنجاح!";
        $msg_type = "success";
        header("Location: students.php?msg=" . urlencode($message) . "&type=" . $msg_type);
        exit;
    } catch (\PDOException $e) {
        $message = "فشلت عملية الحذف: " . $e->getMessage();
        $msg_type = "error";
    }
}

if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = $_GET['msg'];
    $msg_type = $_GET['type'];
}

// جلب الطلاب
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
    <title>الطلاب المسجلين</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container table-page">
        <div class="header-actions">
            <a href="index.php" class="btn-nav">→ إضافة طالب جديد</a>
        </div>

        <h2>إدارة الطلاب المسجلين</h2>

        <?php if (!empty($message)): ?>
            <div class="alert <?= $msg_type == 'success' ? 'alert-success' : 'alert-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <h3>قائمة الطلاب الحالية</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>الاسم الطالب  </th>
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
                                <td class="highlight-text"><?= htmlspecialchars($student['student_name']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= htmlspecialchars($student['student_number']) ?></td>
                                <td> السنة <?= htmlspecialchars($student['year_of_study']) ?></td>
                                <td><?= htmlspecialchars($student['batch_name']) ?></td>
                                <td class="date-text"><?= htmlspecialchars($student['created_at']) ?></td>
                                <td>
                                    <a href="students.php?delete_id=<?= $student['id'] ?>" class="btn-delete" onclick="return confirm('هل أنت متأكد من حذف هذا الطالب نهائياً؟');">حذف</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #888; padding: 30px;">لا يوجد طلاب مسجلين حتى الآن.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>