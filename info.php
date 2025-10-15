<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Quote Carousel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .carousel-item {
            padding: 2rem;
            background-color: #f8f9fa;
            text-align: center;
        }

        .quote {
            font-size: 1.5rem;
            font-style: italic;
        }

        .author {
            margin-top: 1rem;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div id="quoteCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $active = true;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="carousel-item' . ($active ? ' active' : '') . '">';
                        echo '<div class="quote">"' . htmlspecialchars($row["quote"]) . '"</div>';
                        echo '<div class="author">– ' . htmlspecialchars($row["author"]) . '</div>';
                        echo '</div>';
                        $active = false;
                    }
                } else {
                    echo '<div class="carousel-item active"><div class="quote">No quotes found.</div></div>';
                }
                ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#quoteCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#quoteCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>