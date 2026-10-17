<?php
session_start();

$summary = $_SESSION['active_quiz_summary'] ?? null;

if (!$summary) {
    header('Location: index.php');
    exit;
}

// Clear session value so user cannot refresh and view again
unset($_SESSION['active_quiz_summary']);

// Determine status label and feedback message
$percentage = $summary['percent'];
if ($percentage >= 80) {
    $gradeTitle = 'EXCELLENT';
    $feedback = 'ดีเยี่ยม! คุณมีความเข้าใจในเนื้อหานี้เป็นอย่างดี';
} elseif ($percentage >= 60) {
    $gradeTitle = 'GOOD PASS';
    $feedback = 'ผ่านเกณฑ์! คุณสามารถทบทวนเพิ่มเติมเพื่อผลลัพธ์ที่ดีขึ้น';
} else {
    $gradeTitle = 'NEEDS IMPROVEMENT';
    $feedback = 'พยายามอีกนิด! แนะนำให้ทบทวนบทเรียนและทำแบบฝึกหัดซ้ำอีกครั้ง';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปผลการทดสอบ - Math Quest</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="result-page-container">
        <div class="result-panel">
            <div class="success-icon">✓</div>
            <span class="badge-tag">SUMMARY REPORT</span>
            <h1 class="cat-title"><?= htmlspecialchars($summary['title']) ?></h1>

            <div class="score-display">
                <span class="percent"><?= (int)$summary['percent'] ?>%</span>
                <span class="score-detail">ได้รับ <?= (int)$summary['score'] ?> / <?= (int)$summary['total'] ?> คะแนน</span>
            </div>

            <div class="grade-badge"><?= $gradeTitle ?></div>
            <p class="feedback-msg"><?= htmlspecialchars($feedback) ?></p>
            <p class="sub-detail">ทำแบบทดสอบไปทั้งหมด <?= (int)$summary['answered'] ?> จาก <?= (int)$summary['total'] ?> ข้อ</p>

            <div class="result-navigation">
                <a href="index.php" class="btn-home">กลับสู่หน้าหลัก</a>
                <a href="index.php" class="btn-retry">ทำแบบทดสอบอื่น</a>
            </div>
        </div>
    </main>
</body>
</html>