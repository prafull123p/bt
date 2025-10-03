<?php
include 'db.php';

// Fetch images from gallery table
$images = [];
$result = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}

// Grid customization: get columns from query param or default to 4
$columns = isset($_GET['cols']) && in_array((int)$_GET['cols'], [2,3,4,6]) ? (int)$_GET['cols'] : 4;
$col_class = match($columns) {
    2 => 'col-md-6',
    3 => 'col-md-4',
    4 => 'col-md-3',
    6 => 'col-md-2',
    default => 'col-md-3'
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Gallery</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gallery-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.2s;
        }
        .gallery-img:hover {
            transform: scale(1.04);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Image Gallery</h2>
            <form method="get" class="d-flex align-items-center">
                <label class="me-2">Grid:</label>
                <select name="cols" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                    <option value="2" <?= $columns==2?'selected':'' ?>>2</option>
                    <option value="3" <?= $columns==3?'selected':'' ?>>3</option>
                    <option value="4" <?= $columns==4?'selected':'' ?>>4</option>
                    <option value="6" <?= $columns==6?'selected':'' ?>>6</option>
                </select>
            </form>
        </div>
        <div class="row g-4">
            <?php if ($images): ?>
                <?php foreach ($images as $img): ?>
                    <div class="<?= $col_class ?>">
                        <div class="card h-100 shadow-sm">
                            <img src="<?= htmlspecialchars($img['image_path']) ?>" alt="<?= htmlspecialchars($img['title'] ?? '') ?>" class="gallery-img card-img-top">
                            <?php if (!empty($img['title']) || !empty($img['description'])): ?>
                                <div class="card-body">
                                    <?php if (!empty($img['title'])): ?>
                                        <h6 class="card-title"><?= htmlspecialchars($img['title']) ?></h6>
                                    <?php endif; ?>
                                    <?php if (!empty($img['description'])): ?>
                                        <p class="card-text"><?= htmlspecialchars($img['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-secondary text-center">No images found.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>