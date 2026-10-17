<?php
require_once 'db.php';

$db = db();

// Fetch category list with question count
$categories = $db->query("
    SELECT c.*, COUNT(q.id) AS total_q
    FROM categories c
    LEFT JOIN questions q ON q.category_id = c.id
    GROUP BY c.id
    ORDER BY c.id ASC
")->fetchAll();

// System Statistics
$statQuestions = (int)$db->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$statCategories = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$statLogs = (int)$db->query("SELECT COUNT(*) FROM quiz_results")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Math Quest - ระบบฝึกทักษะคณิตศาสตร์</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="navbar">
        <div class="brand-logo">
            <span class="icon">∑</span>
            <div class="title-group">
                <strong>Math Quest</strong>
                <small>คลังโจทย์และแบบฝึกหัดคณิตศาสตร์</small>
            </div>
        </div>
        <a class="nav-link-btn" href="manage.php">ระบบจัดการโจทย์</a>
    </header>

    <main class="main-wrapper">
        <section class="banner">
            <div class="banner-content">
                <span class="badge-tag">ONLINE LEARNING</span>
                <h1>พัฒนาทักษะการคิดคำนวณ<br><span>อย่างมีระบบ</span></h1>
                <p>เลือกบทเรียนที่สนใจ ฝึกทำโจทย์ และประเมินความพร้อมได้ทันที</p>
            </div>
            <div class="banner-badge">
                <span class="math-symbol">π</span>
                <span class="math-operators">+ − × ÷</span>
            </div>
        </section>

        <section class="dashboard-stats">
            <div class="card-stat">
                <h3><?= number_format($statQuestions) ?></h3>
                <p>โจทย์ทั้งหมด</p>
            </div>
            <div class="card-stat">
                <h3><?= number_format($statCategories) ?></h3>
                <p>หมวดหมู่เรียนรู้</p>
            </div>
            <div class="card-stat">
                <h3><?= number_format($statLogs) ?></h3>
                <p>แบบทดสอบที่ทำแล้ว</p>
            </div>
        </section>

        <div class="content-header">
            <h2>หมวดหมู่แบบทดสอบ</h2>
            <p>เลือกหัวข้อเพื่อเริ่มทำแบบทดสอบ</p>
        </div>

        <section class="grid-categories">
            <?php foreach ($categories as $item): ?>
                <div class="category-item">
                    <div class="cat-icon"><?= htmlspecialchars($item['icon']) ?></div>
                    <h3 class="cat-name"><?= htmlspecialchars($item['name']) ?></h3>
                    <p class="cat-desc"><?= htmlspecialchars($item['description']) ?></p>
                    <div class="cat-footer">
                        <span class="count-pill"><?= (int)$item['total_q'] ?> ข้อ</span>
                        <a href="quiz.php?category=<?= (int)$item['id'] ?>" class="start-btn">เริ่มทดสอบ</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="instruction-box">
            <div class="icon-wrap">💡</div>
            <div class="text-wrap">
                <h4>คำแนะนำการทำแบบทดสอบ</h4>
                <p>เมื่อเลือกคำตอบครบทุกข้อแล้ว ให้กดปุ่มส่งแบบทดสอบเพื่อดูคะแนนและผลการประเมินทันที</p>
            </div>
        </section>
    </main>

    <footer class="app-footer">
        <p>© Math Quest - ระบบฝึกฝนคณิตศาสตร์ออนไลน์</p>
    </footer>
</body>
</html>