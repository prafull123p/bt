<?php include('header.php'); ?>

<style>
.campus-section {
    background: #f8f9fa;
    padding: 40px 0;
}
.campus-title {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    text-align: center;
}
.campus-card {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(44,62,80,0.08);
    transition: transform 0.2s;
}
.campus-card:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 8px 24px rgba(44,62,80,0.15);
}
.campus-card img {
    height: 220px;
    object-fit: cover;
    width: 100%;
}
.campus-card-body {
    padding: 1.5rem;
}
.campus-card-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2980b9;
}
.campus-card-text {
    color: #555;
}
</style>

<?php
// Fetch campus life cards from the database
include 'db.php';
$cards = [];
$res = $conn->query("SELECT * FROM campus_life ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $cards[] = $row;
    }
}
?>

<div class="campus-section">
    <div class="container">
        <div class="campus-title">Campus Life</div>
        <div class="row g-4">
            <?php if ($cards): ?>
                <?php foreach ($cards as $card): ?>
                    <div class="col-md-4">
                        <div class="card campus-card h-100">
                            <?php if (!empty($card['image_path'])): ?>
                                <img src="<?= htmlspecialchars($card['image_path']) ?>" alt="<?= htmlspecialchars($card['title']) ?>" class="card-img-top">
                            <?php endif; ?>
                            <div class="campus-card-body">
                                <div class="campus-card-title"><?= htmlspecialchars($card['title']) ?></div>
                                <div class="campus-card-text"><?= htmlspecialchars($card['text']) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted">No campus life cards found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>