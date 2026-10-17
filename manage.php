<?php
require_once 'db.php';

$db = db();
$action = $_GET['action'] ?? 'view';
$questionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Load Category List
$categoriesList = $db->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionType = $_POST['action_type'] ?? '';

    if ($actionType === 'add') {
        $sql = "INSERT INTO questions (category_id, question, choice_a, choice_b, choice_c, choice_d, correct_answer, explanation, difficulty)
                VALUES (:cat_id, :question, :ca, :cb, :cc, :cd, :correct, :exp, :diff)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':cat_id'   => $_POST['category_id'],
            ':question' => trim($_POST['question']),
            ':ca'       => trim($_POST['choice_a']),
            ':cb'       => trim($_POST['choice_b']),
            ':cc'       => trim($_POST['choice_c']),
            ':cd'       => trim($_POST['choice_d']),
            ':correct'  => $_POST['correct_answer'],
            ':exp'      => trim($_POST['explanation']),
            ':diff'     => $_POST['difficulty']
        ]);
        header('Location: manage.php?status=success_add');
        exit;
    }

    if ($actionType === 'edit') {
        $sql = "UPDATE questions SET
                category_id = :cat_id, question = :question,
                choice_a = :ca, choice_b = :cb, choice_c = :cc, choice_d = :cd,
                correct_answer = :correct, explanation = :exp, difficulty = :diff
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':cat_id'   => $_POST['category_id'],
            ':question' => trim($_POST['question']),
            ':ca'       => trim($_POST['choice_a']),
            ':cb'       => trim($_POST['choice_b']),
            ':cc'       => trim($_POST['choice_c']),
            ':cd'       => trim($_POST['choice_d']),
            ':correct'  => $_POST['correct_answer'],
            ':exp'      => trim($_POST['explanation']),
            ':diff'     => $_POST['difficulty'],
            ':id'       => $_POST['id']
        ]);
        header('Location: manage.php?status=success_edit');
        exit;
    }

    if ($actionType === 'remove') {
        $stmt = $db->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header('Location: manage.php?status=success_delete');
        exit;
    }
}

// Fetch single item for edit
$editData = null;
if ($action === 'edit' && $questionId) {
    $stmt = $db->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$questionId]);
    $editData = $stmt->fetch();
}

// Fetch all questions
$questionsList = $db->query("
    SELECT q.*, c.name AS cat_name, c.icon AS cat_icon
    FROM questions q
    INNER JOIN categories c ON c.id = q.category_id
    ORDER BY q.id DESC
")->fetchAll();

$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการคำถาม - Math Quest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <a class="brand-logo link" href="index.php">
            <span class="icon">∑</span>
            <div class="title-group">
                <strong>Math Quest</strong>
                <small>Admin Panel</small>
            </div>
        </a>
        <a class="nav-link-btn" href="index.php">หน้าหลักผู้ใช้งาน</a>
    </header>

    <main class="main-wrapper">
        <?php if ($status): ?>
            <div class="notification-banner">ดําเนินการปรับปรุงข้อมูลเรียบร้อยแล้ว</div>
        <?php endif; ?>

        <div class="content-header">
            <h1><?= $editData ? 'แก้ไขโจทย์' : 'คลังข้อสอบ (เพิ่มโจทย์ใหม่)' ?></h1>
            <p>จัดการฐานข้อมูลข้อสอบสำหรับผู้เรียน</p>
        </div>

        <section class="panel-card">
            <form method="post" class="crud-form">
                <input type="hidden" name="action_type" value="<?= $editData ? 'edit' : 'add' ?>">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= (int)$editData['id'] ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label>หมวดหมู่ *</label>
                        <select name="category_id" required>
                            <option value="">-- เลือกหมวดหมู่ --</option>
                            <?php foreach ($categoriesList as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"
                                    <?= (($editData['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ระดับความยาก *</label>
                        <select name="difficulty" required>
                            <?php foreach (['ง่าย', 'ปานกลาง', 'ยาก'] as $diff): ?>
                                <option value="<?= $diff ?>" <?= (($editData['difficulty'] ?? '') === $diff) ? 'selected' : '' ?>>
                                    <?= $diff ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>โจทย์คำถาม *</label>
                    <textarea name="question" required><?= htmlspecialchars($editData['question'] ?? '') ?></textarea>
                </div>

                <div class="choices-container">
                    <?php foreach (['a', 'b', 'c', 'd'] as $key): ?>
                        <div class="form-group">
                            <label>ตัวเลือก <?= strtoupper($key) ?> *</label>
                            <input type="text" name="choice_<?= $key ?>" required
                                   value="<?= htmlspecialchars($editData["choice_{$key}"] ?? '') ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>เฉลยข้อที่ถูกต้อง *</label>
                        <select name="correct_answer" required>
                            <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($editData['correct_answer'] ?? '') === $opt) ? 'selected' : '' ?>>
                                    ตัวเลือก <?= $opt ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>คำอธิบายเพิ่มเติม</label>
                        <input type="text" name="explanation" value="<?= htmlspecialchars($editData['explanation'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-buttons">
                    <?php if ($editData): ?>
                        <a href="manage.php" class="btn-cancel">ยกเลิก</a>
                    <?php endif; ?>
                    <button type="submit" class="btn-save">
                        <?= $editData ? 'บันทึกการปรับปรุง' : 'บันทึกโจทย์ใหม่' ?>
                    </button>
                </div>
            </form>
        </section>

        <div class="content-header compact">
            <h2>รายการข้อสอบในระบบ (<?= count($questionsList) ?>)</h2>
        </div>

        <section class="panel-card padding-none">
            <div class="responsive-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>หมวดหมู่</th>
                            <th>คำถาม</th>
                            <th>ระดับ</th>
                            <th>เฉลย</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questionsList as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars($row['cat_icon'] . ' ' . $row['cat_name']) ?></td>
                                <td><?= htmlspecialchars($row['question']) ?></td>
                                <td><span class="tag-badge"><?= htmlspecialchars($row['difficulty']) ?></span></td>
                                <td><strong><?= htmlspecialchars($row['correct_answer']) ?></strong></td>
                                <td class="action-cell">
                                    <a href="manage.php?action=edit&id=<?= (int)$row['id'] ?>" class="btn-edit-sm">แก้ไข</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('ยืนยันการลบโจทย์นี้?');">
                                        <input type="hidden" name="action_type" value="remove">
                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                        <button type="submit" class="btn-del-sm">ลบ</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer class="app-footer">
        <p>© Math Quest - ระบบจัดการคำถาม</p>
    </footer>
</body>
</html>