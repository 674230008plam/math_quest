<?php
session_start();
require_once 'db.php';

$db = db();$catId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

if (!$catId) {
    header('Location: index.php');
    exit;
}

// Fetch Category Details
$stmt =$db->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$catId]);
$categoryData =$stmt->fetch();

if (!$categoryData) {
    header('Location: index.php');
    exit;
}

// Fetch Questions (Randomized 10 items)
$stmt =$db->prepare("
    SELECT * FROM questions
    WHERE category_id = ?
    ORDER BY RAND()
    LIMIT 10
");
$stmt->execute([$catId]);
$quizItems =$stmt->fetchAll();

if (empty($quizItems)) {
    exit("หมวดหมู่นี้ยังไม่มีข้อสอบ <a href='index.php'>กลับหน้าหลัก</a>");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {$scoreCount = 0;
    $totalAnswered = 0;
    $userAnswers =$_POST['ans'] ?? [];

    foreach ($quizItems as $item) {$userChoice = $userAnswers[$item['id']] ?? null;
        if (!is_null($userChoice) && $userChoice !== '') {$totalAnswered++;
            if ($userChoice === $item['correct_answer']) {$scoreCount++;
            }
        }
    }

    $totalItems = count($quizItems);$calculatedPercent = round(($scoreCount / $totalItems) * 100);

    // Save result to DB
    $insertResult =$db->prepare("
        INSERT INTO quiz_results (category_id, score, total_questions, percentage)
        VALUES (?, ?, ?, ?)
    ");
    $insertResult->execute([$catId,$scoreCount, $totalItems,$calculatedPercent]);

    // Store payload in session
    $_SESSION['active_quiz_summary'] = [
        'title'    => $categoryData['name'],
        'score'    => $scoreCount,
        'total'    => $totalItems,
        'percent'  => $calculatedPercent,
        'answered' => $totalAnswered
    ];

    header('Location: result.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทำแบบทดสอบ - <?= htmlspecialchars($categoryData['name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <a class="brand-logo link" href="index.php">
            <span class="icon">∑</span>
            <div class="title-group">
                <strong>Math Quest</strong>
                <small>แบบทดสอบออนไลน์</small>
            </div>
        </a>
        <a class="nav-link-btn" href="index.php">← ออกจากหน้าทดสอบ</a>
    </header>

    <main class="main-wrapper narrow">
        <div class="quiz-header-banner">
            <div>
                <span class="badge-tag"><?= htmlspecialchars($categoryData['icon']) ?> <?= htmlspecialchars($categoryData['name']) ?></span>
                <h1>แบบทดสอบวัดระดับ</h1>
            </div>
            <div class="quiz-counter">รวม <?= count($quizItems) ?> ข้อ</div>
        </div>

        <form method="post" id="examForm">
            <?php foreach ($quizItems as $idx =>$question): ?>
                <div class="question-box">
                    <span class="q-number">ข้อที่ <?= $idx + 1 ?></span>
                    <p class="q-title"><?= htmlspecialchars($question['question']) ?></p>

                    <div class="options-group">
                        <?php
                        $optionsMap = [
                            'A' => $question['choice_a'],
                            'B' => $question['choice_b'],
                            'C' => $question['choice_c'],
                            'D' => $question['choice_d']
                        ];
                        foreach ($optionsMap as $key =>$text):
                        ?>
                            <label class="option-card">
                                <input type="radio" name="ans[<?= (int)$question['id'] ?>]" value="<?= $key ?>">
                                <span class="opt-label"><?= $key ?></span>
                                <span class="opt-text"><?= htmlspecialchars($text) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="submit-wrapper">
                <button type="submit" class="btn-submit-exam">ส่งคำตอบคำนวณคะแนน</button>
            </div>
        </form>
    </main>

    <script>
    document.getElementById('examForm').addEventListener('submit', function(event) {
        const total = <?= count($quizItems) ?>;
        const checkedCount = document.querySelectorAll('input[type="radio"]:checked').length;

        if (checkedCount < total) {
            const confirmSubmit = confirm(`คุณตอบคำถามไปเพียง ${checkedCount} จาก ${total} ข้อ ยืนยันการส่งคำตอบหรือไม่?`);
            if (!confirmSubmit) {
                event.preventDefault();
            }
        }
    });
    </script>
</body>
</html>