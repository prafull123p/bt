<?php include('header.php'); ?>
<?php
// Read gallery settings to honor admin intensity and reduced-motion override
$settings_file = __DIR__ . '/tmp/gallery_settings.json';
$server_force_reduced = false;
if (file_exists($settings_file)) {
    $raw = @file_get_contents($settings_file);
    $gjson = $raw ? json_decode($raw, true) : null;
    if (!empty($gjson) && !empty($gjson['reduced_motion']))
        $server_force_reduced = true;
}

// Helper: fetch all rows from a prepared statement.
// Uses mysqli_stmt::get_result when available (mysqlnd), otherwise falls
// back to binding result variables so code works on systems without mysqlnd.
if (!function_exists('stmt_get_all')) {
    function stmt_get_all($stmt)
    {
        $rows = [];
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    $rows[] = $r;
                }
            }
            return $rows;
        }

        // Fallback: bind_result
        $meta = $stmt->result_metadata();
        if (!$meta)
            return $rows;
        $fields = [];
        $row = [];
        while ($field = $meta->fetch_field()) {
            $fields[] = &$row[$field->name];
        }
        // Bind result variables
        call_user_func_array([$stmt, 'bind_result'], $fields);
        // Fetch rows
        while ($stmt->fetch()) {
            $rec = [];
            foreach ($row as $k => $v)
                $rec[$k] = $v;
            $rows[] = $rec;
        }
        return $rows;
    }
}
?>

<!-- Landing styles (external) -->
<link rel="stylesheet" href="css/landing.css">

<main class="hero-3d">
    <div class="scene" id="scene">
        <div class="card-3d">
            <div class="layer poly"></div>
            <div class="layer poly--accent"></div>
            <div class="layer float-dot"
                style="left:6%;top:18%;width:22px;height:22px;opacity:.14;transform:translateZ(-30px)"></div>
            <div class="hero-content">
                <h1>Welcome to Our Campus — Batmul collage</h1>
                <p>Batmul Aashram Mahavidyalay in Mahapalli, Raigarh is a private co-educational college established in
                    1999, offering undergraduate and postgraduate programs across various disciplines</p>
                <div class="cta-row">
                    <a href="notification.php" class="btn btn-primary btn-lg">Notifications & Events</a>
                    <a href="admin_blog.php" class="btn btn-outline-primary btn-lg">Latest News</a>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add the same 3D background well for visual consistency -->
<div class="bg-well-landing" aria-hidden="true">
    <div class="bg-layer" data-depth="0.06"
        style="background:linear-gradient(135deg, rgba(13,110,253,0.06), rgba(111,66,193,0.03));"></div>
    <div class="bg-layer" data-depth="0.14"
        style="background:radial-gradient(600px 300px at 15% 25%, rgba(255,0,150,0.05), transparent 18%), radial-gradient(500px 240px at 85% 75%, rgba(13,110,253,0.04), transparent 28%);">
    </div>
    <div class="bg-shape poly-large" data-depth="0.26"></div>
    <div class="bg-glass" data-depth="0.02">
        <div class="well-inner container">
            <div class="well-content" style="background:transparent;border:none;box-shadow:none;padding:12px 24px;">
                <!-- small decorative well overlay for landing header -->
                <div class="decorative-well-overlay">
                    <img class="img-fluid" src="" alt="">
                </div>

            </div>
        </div>
    </div>

    <!-- Featured Gallery with 3D Auto-Layout -->
    <?php
    $featured_imgs = [];
    if (isset($conn)) {
        // Fetch ALL images from gallery with proper ordering; handle missing table gracefully
        $qf = @$conn->prepare("SELECT id, image_path, image_small, image_medium, image_large, webp_path, title, description, effect_strength FROM gallery ORDER BY COALESCE(display_order, 9999) ASC, id DESC");
        if ($qf && $qf->execute()) {
            $rows = stmt_get_all($qf);
            foreach ($rows as $r) {
                // Ensure each row has all expected fields (null if missing from query)
                $featured_imgs[] = [
                    'id' => $r['id'] ?? null,
                    'image_path' => $r['image_path'] ?? '',
                    'image_small' => $r['image_small'] ?? null,
                    'image_medium' => $r['image_medium'] ?? null,
                    'image_large' => $r['image_large'] ?? null,
                    'webp_path' => $r['webp_path'] ?? null,
                    'title' => $r['title'] ?? 'Untitled',
                    'description' => $r['description'] ?? '',
                    'effect_strength' => $r['effect_strength'] ?? 0
                ];
            }
            $qf->close();
        } else {
            // Query failed; try simple fallback query without responsive columns
            $qf2 = @$conn->query("SELECT id, image_path, title, description FROM gallery LIMIT 20");
            if ($qf2 && $qf2->num_rows > 0) {
                while ($r = $qf2->fetch_assoc()) {
                    $featured_imgs[] = [
                        'id' => $r['id'],
                        'image_path' => $r['image_path'],
                        'image_small' => null,
                        'image_medium' => null,
                        'image_large' => null,
                        'webp_path' => null,
                        'title' => $r['title'] ?? 'Untitled',
                        'description' => $r['description'] ?? '',
                        'effect_strength' => 0
                    ];
                }
            }
        }
    }
    ?>

    <?php if (!empty($featured_imgs)): ?>
        <section class="py-5 bg-white">
            <div class="container">
                <div class="text-center mb-4">
                    <h2>Complete Gallery Collection</h2>
                    <p class="lead">All images from our campus with 3D auto-layout</p>
                </div>
                
                <!-- 3D Grid Gallery -->
                <div id="gallery3dGrid" class="gallery-3d-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; perspective:1000px;">
                    <?php foreach ($featured_imgs as $idx => $img): ?>
                        <?php 
                        $hue = ($idx * 45) % 360;
                        $srcset = [];
                        if (!empty($img['image_small']))
                            $srcset[] = htmlspecialchars($img['image_small']) . ' 480w';
                        if (!empty($img['image_medium']))
                            $srcset[] = htmlspecialchars($img['image_medium']) . ' 768w';
                        if (!empty($img['image_large']))
                            $srcset[] = htmlspecialchars($img['image_large']) . ' 1200w';
                        if (!empty($img['webp_path']))
                            $srcset[] = htmlspecialchars($img['webp_path']) . ' 1200w';
                        $srcsetAttr = implode(', ', $srcset);
                        ?>
                        <figure class="gallery-3d-item" 
                            data-idx="<?= $idx ?>" 
                            data-effect="<?= intval($img['effect_strength'] ?? 0) ?>"
                            style="--hue:<?= $hue ?>deg; cursor:pointer; transform-style:preserve-3d; transition:all 0.3s ease;">
                            <div class="gallery-3d-frame">
                                <img loading="lazy" 
                                    src="<?= htmlspecialchars($img['image_path']) ?>" 
                                    srcset="<?= $srcsetAttr ?>"
                                    sizes="(max-width:768px) 100vw, 220px" 
                                    alt="<?= htmlspecialchars($img['title'] ?? '') ?>"
                                    class="gallery-3d-img">
                                <div class="gallery-3d-overlay"></div>
                            </div>
                            <figcaption class="gallery-3d-caption">
                                <strong style="color:hsl(var(--hue), 85%, 50%)">
                                    <?= htmlspecialchars($img['title'] ?? 'Untitled') ?>
                                </strong>
                                <p class="small text-muted">
                                    <?= htmlspecialchars(mb_strimwidth($img['description'] ?? '', 0, 60, '...')) ?>
                                </p>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 3D Gallery CSS -->
        <style>
            .gallery-3d-grid {
                gap: 20px;
                padding: 20px;
            }

            .gallery-3d-item {
                position: relative;
                margin: 0;
                padding: 0;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                transition: all 0.4s cubic-bezier(0.23, 1, 0.320, 1);
                transform: translateZ(0);
            }

            .gallery-3d-item:hover {
                transform: translateY(-8px) rotateX(5deg) scale(1.05);
                box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            }

            .gallery-3d-frame {
                position: relative;
                width: 100%;
                padding-bottom: 100%;
                overflow: hidden;
                border-radius: 8px;
            }

            .gallery-3d-img {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.4s ease;
            }

            .gallery-3d-item:hover .gallery-3d-img {
                transform: scale(1.1) rotate(2deg);
            }

            .gallery-3d-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(0,0,0,0.3));
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .gallery-3d-item:hover .gallery-3d-overlay {
                opacity: 1;
            }

            .gallery-3d-caption {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 12px;
                background: linear-gradient(180deg, transparent, rgba(0,0,0,0.8));
                color: white;
                transform: translateY(100%);
                transition: transform 0.3s ease;
            }

            .gallery-3d-item:hover .gallery-3d-caption {
                transform: translateY(0);
            }

            @media (max-width: 768px) {
                .gallery-3d-grid {
                    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                }
            }
        </style>

        <!-- JavaScript for 3D interactions -->
        <script>
            (function() {
                const gallery = document.getElementById('gallery3dGrid');
                if (!gallery) return;

                gallery.addEventListener('mousemove', (e) => {
                    const items = gallery.querySelectorAll('.gallery-3d-item');
                    const rect = gallery.getBoundingClientRect();
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    const mouseX = e.clientX - rect.left;
                    const mouseY = e.clientY - rect.top;

                    items.forEach(item => {
                        const itemRect = item.getBoundingClientRect();
                        const itemCenterX = itemRect.left - rect.left + itemRect.width / 2;
                        const itemCenterY = itemRect.top - rect.top + itemRect.height / 2;
                        
                        const angleX = (mouseY - itemCenterY) * 0.05;
                        const angleY = (mouseX - itemCenterX) * 0.05;
                        
                        item.style.transform = `perspective(1000px) rotateX(${angleX}deg) rotateY(${angleY}deg) translateZ(20px)`;
                    });
                });

                gallery.addEventListener('mouseleave', () => {
                    document.querySelectorAll('.gallery-3d-item').forEach(item => {
                        item.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
                    });
                });

                // Click to view full size
                document.querySelectorAll('.gallery-3d-item').forEach((item, idx) => {
                    item.addEventListener('click', () => {
                        const img = item.querySelector('.gallery-3d-img');
                        if (img) {
                            // You can open a lightbox or modal here
                            console.log('Clicked image:', idx);
                        }
                    });
                });
            })();
        </script>
    <?php endif; ?>
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-4">
                <h2>Featured Gallery</h2>
                <p class="lead">Hand-picked images from our campus</p>
            </div>
            <div id="featuredCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($featured_imgs as $i => $fi): ?>
                        <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="card shadow-sm">
                                        <?php
                                        $srcs = [];
                                        if (!empty($fi['image_small']))
                                            $srcs[] = htmlspecialchars($fi['image_small']) . ' 480w';
                                        if (!empty($fi['image_medium']))
                                            $srcs[] = htmlspecialchars($fi['image_medium']) . ' 768w';
                                        if (!empty($fi['image_large']))
                                            $srcs[] = htmlspecialchars($fi['image_large']) . ' 1200w';
                                        if (!empty($fi['webp_path']))
                                            $srcs[] = htmlspecialchars($fi['webp_path']) . ' 1200w';
                                        $srcset = implode(', ', $srcs);
                                        ?>
                                        <img loading="lazy" src="<?= htmlspecialchars($fi['image_path']) ?>"
                                            srcset="<?= $srcset ?>" sizes="(max-width:768px) 100vw, 800px" class="d-block w-100"
                                            alt="<?= htmlspecialchars($fi['title'] ?? '') ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($fi['title'] ?? '') ?></h5>
                                            <p class="card-text"><?= htmlspecialchars($fi['description'] ?? '') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </section>
<?php ; ?>

<!-- 3D Gallery Preview (compact, embedded in landing) -->
<?php
$preview_images = [];
if (isset($conn)) {
    // Fetch preview images with fallback to simple query if schema mismatch
    $qg = @$conn->prepare("SELECT id, image_path, image_small, image_medium, image_large, webp_path, title, description, effect_strength FROM gallery ORDER BY COALESCE(display_order,9999) ASC, id DESC LIMIT 6");
    if ($qg && $qg->execute()) {
        $rows = stmt_get_all($qg);
        foreach ($rows as $r) {
            $preview_images[] = [
                'id' => $r['id'] ?? null,
                'image_path' => $r['image_path'] ?? '',
                'image_small' => $r['image_small'] ?? null,
                'image_medium' => $r['image_medium'] ?? null,
                'image_large' => $r['image_large'] ?? null,
                'webp_path' => $r['webp_path'] ?? null,
                'title' => $r['title'] ?? 'Untitled',
                'description' => $r['description'] ?? '',
                'effect_strength' => $r['effect_strength'] ?? 0
            ];
        }
        $qg->close();
    } else {
        // Fallback: simple query without responsive columns
        $qg2 = @$conn->query("SELECT id, image_path, title, description FROM gallery LIMIT 6");
        if ($qg2 && $qg2->num_rows > 0) {
            while ($r = $qg2->fetch_assoc()) {
                $preview_images[] = [
                    'id' => $r['id'],
                    'image_path' => $r['image_path'],
                    'image_small' => null,
                    'image_medium' => null,
                    'image_large' => null,
                    'webp_path' => null,
                    'title' => $r['title'] ?? 'Untitled',
                    'description' => $r['description'] ?? '',
                    'effect_strength' => 0
                ];
            }
        }
    }
}
?>

<?php if (!empty($preview_images)): ?>
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-4">
                <h2>Gallery Preview</h2>
                <p class="lead">A small immersive preview from our photo gallery — move your mouse to tilt.</p>
            </div>
            <div id="landingGallery" class="d-flex flex-wrap justify-content-center gap-4">
                <?php foreach ($preview_images as $i => $img): ?>
                    <?php $h = $i * 37 % 360; ?>
                    <?php
                    $srcset = [];
                    if (!empty($img['image_small']))
                        $srcset[] = htmlspecialchars($img['image_small']) . ' 480w';
                    if (!empty($img['image_medium']))
                        $srcset[] = htmlspecialchars($img['image_medium']) . ' 768w';
                    if (!empty($img['image_large']))
                        $srcset[] = htmlspecialchars($img['image_large']) . ' 1200w';
                    if (!empty($img['webp_path']))
                        $srcset[] = htmlspecialchars($img['webp_path']) . ' 1200w';
                    $srcsetAttr = implode(', ', $srcset);
                    ?>
                    <figure class="lg-card" tabindex="0" data-src="<?= htmlspecialchars($img['image_path']) ?>"
                        data-idx="<?= $i ?>" data-effect="<?= intval($img['effect_strength'] ?? 0) ?>" style="--h:<?= $h ?>">
                        <div class="lg-media">
                            <img loading="lazy" src="<?= htmlspecialchars($img['image_path']) ?>" srcset="<?= $srcsetAttr ?>"
                                sizes="(max-width:600px) 100vw, 220px" alt="<?= htmlspecialchars($img['title'] ?? '') ?>">
                            <div class="lg-overlay"></div>
                        </div>
                        <figcaption class="lg-meta text-center p-2">
                            <strong style="color:hsl(<?= $h ?>,85%,50%)"><?= htmlspecialchars($img['title'] ?? '') ?></strong>
                            <div class="small text-muted">
                                <?= htmlspecialchars(mb_strimwidth($img['description'] ?? '', 0, 64, '...')) ?></div>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-3">
                <a href="gallery3d.php" class="btn btn-outline-primary">Open Full Gallery</a>
            </div>
        </div>
    </section>
<?php endif; ?>


<!-- Lightbox for landing preview -->
<div id="lgLightbox" class="lightbox" style="display:none;align-items:center;justify-content:center;">
    <div class="frame p-3" style="max-width:90%;max-height:90%;background:#081026;border-radius:12px;">
        <button id="lg-close" class="btn btn-sm btn-outline-light"
            style="position:absolute;right:18px;top:18px;z-index:2">Close</button>
        <img id="lg-img" src="" style="max-width:100%;max-height:80vh;display:block;margin:0 auto" alt="">
        <div class="lb-meta text-white mt-2 text-center"><strong id="lg-title"></strong>
            <div id="lg-desc" class="small text-muted"></div>
        </div>
    </div>
</div>
<!-- Quotes Section -->

<?php
// Fetch initial page of quotes for landing page (server-render first page)
$quotes = [];
$per_page = 6;
$page = 1;
if (isset($conn)) {
    $offset = ($page - 1) * $per_page;
    $rq = @$conn->prepare("SELECT id, quote, author FROM quotes ORDER BY id DESC LIMIT ? OFFSET ?");
    if ($rq) {
        $rq->bind_param('ii', $per_page, $offset);
        if ($rq->execute()) {
            $rows = stmt_get_all($rq);
            foreach ($rows as $r) {
                $quotes[] = [
                    'id' => $r['id'] ?? 0,
                    'quote' => $r['quote'] ?? '',
                    'author' => $r['author'] ?? 'Unknown'
                ];
            }
            $rq->close();
        }
    }
}
if (empty($quotes)) {
    $quotes = [['id' => 0, 'quote' => 'Education is the most powerful weapon which you can use to change the world.', 'author' => 'Nelson Mandela']];
}
?>

<!-- Quotes (Bootstrap carousel for accessibility & responsiveness) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2>What People Say</h2>
            <p class="lead">Selected quotes and testimonials</p>
        </div>

        <div id="quotesCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
            <div class="carousel-inner">
                <?php foreach ($quotes as $i => $q): ?>
                    <div class="carousel-item<?= $i === 0 ? ' active' : '' ?>">
                        <div class="quote-3d-wrapper d-flex justify-content-center">
                            <div class="quote-scene" aria-live="polite" aria-atomic="true">
                                <?php
                                $quoteText = $q['quote'] ?? '';
                                $quoteAuthor = $q['author'] ?? '';
                                if (isset($conn) && !empty($q['id'])) {
                                    $rid = (int) $q['id'];
                                    $st = @$conn->prepare("SELECT quote, author FROM quotes WHERE id = ? LIMIT 1");
                                    if ($st) {
                                        $st->bind_param('i', $rid);
                                        if ($st->execute()) {
                                            $rows = stmt_get_all($st);
                                            if (!empty($rows) && is_array($rows[0])) {
                                                $row = $rows[0];
                                                $quoteText = $row['quote'] ?? $quoteText;
                                                $quoteAuthor = $row['author'] ?? $quoteAuthor;
                                            }
                                        }
                                        $st->close();
                                    }
                                }
                                ?>
                                <div class="quote-card">
                                    <div class="quote-inner">
                                        <div class="quote-text">“<?= nl2br(htmlspecialchars($quoteText, ENT_QUOTES)) ?>”
                                        </div>
                                        <div class="quote-author">— <?= htmlspecialchars($quoteAuthor, ENT_QUOTES) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#quotesCarousel" data-bs-slide="prev"
                aria-label="Previous quote">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#quotesCarousel" data-bs-slide="next"
                aria-label="Next quote">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>

        <div class="text-center mt-3">
            <button id="loadMoreQuotes" class="btn btn-outline-primary" data-next-page="2"
                data-per-page="<?= (int) $per_page ?>">Load more quotes</button>
        </div>
    </div>
</section>

<!-- Marquee: combined Notifications, Events, News (3D marquee) -->
<?php
// Build combined items from notifications/events/news with simple server-side caching
$marquee_items = [];
$cacheFile = __DIR__ . '/tmp/marquee_cache.json';
$cacheTtl = 60; // seconds
$useCache = false;
if (file_exists($cacheFile)) {
    $stat = stat($cacheFile);
    if ($stat && (time() - $stat['mtime'] <= $cacheTtl)) {
        $raw = @file_get_contents($cacheFile);
        $cached = $raw ? json_decode($raw, true) : null;
        if (is_array($cached)) {
            $marquee_items = $cached;
            $useCache = true;
        }
    }
}

if (!$useCache && isset($conn) && $conn) {
    // Notifications: try plural 'notifications' first, then fallback to 'notification'
    $qn = @$conn->query("SELECT id, title, COALESCE(content, message, '') AS body, created_at FROM notifications ORDER BY created_at DESC LIMIT 20");
    if (!($qn && $qn->num_rows > 0)) {
        $qn = @$conn->query("SELECT id, title, message AS body, created_at FROM notification ORDER BY created_at DESC LIMIT 20");
    }
    if ($qn && $qn->num_rows > 0) {
        while ($r = $qn->fetch_assoc()) {
            $marquee_items[] = [
                'type' => 'Notification',
                'id' => isset($r['id']) ? (int) $r['id'] : 0,
                'title' => $r['title'] ?? '',
                'body' => $r['body'] ?? '',
                'dt' => strtotime($r['created_at'] ?? '1970-01-01')
            ];
        }
    }

    // Events
    $qe = @$conn->query("SELECT id, title, description AS body, event_date FROM events ORDER BY event_date DESC LIMIT 20");
    if ($qe && $qe->num_rows > 0) {
        while ($r = $qe->fetch_assoc()) {
            $marquee_items[] = [
                'type' => 'Event',
                'id' => isset($r['id']) ? (int) $r['id'] : 0,
                'title' => $r['title'] ?? '',
                'body' => $r['body'] ?? '',
                'dt' => strtotime($r['event_date'] ?? '1970-01-01')
            ];
        }
    }

    // News / Blog posts
    $qb = @$conn->query("SELECT id, title, content AS body, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 20");
    if ($qb && $qb->num_rows > 0) {
        while ($r = $qb->fetch_assoc()) {
            $marquee_items[] = [
                'type' => 'News',
                'id' => isset($r['id']) ? (int) $r['id'] : 0,
                'title' => $r['title'] ?? '',
                'body' => $r['body'] ?? '',
                'dt' => strtotime($r['created_at'] ?? '1970-01-01')
            ];
        }
    }

    // write cache (best-effort)
    $cached = json_encode($marquee_items, JSON_UNESCAPED_UNICODE);
    if (!is_dir(__DIR__ . '/tmp'))
        @mkdir(__DIR__ . '/tmp', 0777, true);
    @file_put_contents($cacheFile, $cached);
}

// Fallback
if (empty($marquee_items)) {
    $marquee_items[] = ['type' => 'Notice', 'title' => 'No items available', 'body' => 'There are currently no notifications, events, or news.', 'dt' => time()];
}

// sort by date desc
usort($marquee_items, function ($a, $b) {
    return $b['dt'] <=> $a['dt'];
});
// limit to recent 24
$marquee_items = array_slice($marquee_items, 0, 24);

// build display strings
$display = [];
foreach ($marquee_items as $m) {
    $t_raw = $m['title'] ?: ($m['body'] ?? '');
    $t = htmlspecialchars($t_raw, ENT_QUOTES);
    $type = htmlspecialchars($m['type'], ENT_QUOTES);
    $date = $m['dt'] ? date('M j', $m['dt']) : '';
    $summary = strip_tags($m['body'] ?? '');
    $summary = mb_strimwidth($summary, 0, 80, '...');

    // determine link target: prefer detail anchors if id present, otherwise listing pages
    $link = '#';
    if ($m['type'] === 'Event') {
        $link = 'notification.php#eventsList';
        if (!empty($m['id']))
            $link = 'notification.php?event_id=' . (int) $m['id'];
    } elseif ($m['type'] === 'News') {
        $link = 'news.php';
        if (!empty($m['id']))
            $link = 'news.php?id=' . (int) $m['id'];
    } else {
        // Notification or others
        $link = 'notification.php';
        if (!empty($m['id']))
            $link = 'notification.php?notice_id=' . (int) $m['id'];
    }

    $display[] = "<a class=\"marquee-item text-decoration-none\" href=\"{$link}\"><span class=\"marquee-type\">[{$type}]</span> {$t} <small>• {$date}</small></a>";
}

$track = implode(' ', $display);
?>

<section class="marquee-3d" aria-hidden="false" aria-live="polite">
    <div class="marquee-shell">
        <div class="marquee-panel">
            <strong class="ms-2 me-3">Latest</strong>
            <div class="marquee-track" role="marquee">
                <div class="marquee-items" id="marqueeItems">
                    <?php echo $track . ' ' . $track; ?>
                </div>
            </div>
            <div style="width:28px"></div>
        </div>
    </div>
</section>

<!-- Offscreen announcer for screen readers -->
<div id="srAnnouncer" class="visually-hidden" aria-live="polite" aria-atomic="true"></div>

<script>
    (function () {
        const shell = document.querySelector('.marquee-shell');
        const items = document.getElementById('marqueeItems');
        const announcer = document.getElementById('srAnnouncer');
        if (!items) return;
        // Pause on hover handled by CSS; add small tilt
        shell.addEventListener('mousemove', function (e) {
            const rect = shell.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            const rotY = x * 6;
            const rotX = y * -4;
            shell.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg)`;
        });
        shell.addEventListener('mouseleave', () => shell.style.transform = '');

        // Announce the first visible item for screen readers
        try {
            const first = items.querySelector('.marquee-item');
            if (first && announcer) {
                // textContent gives readable string
                announcer.textContent = first.textContent.trim();
            }
        } catch (e) { console.error(e); }
    })();
</script>


<!-- Features section -->
<section class="features bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <!-- <h3>Immersive Design</h3>
                <p>Our new landing uses CSS clip-paths, layered gradients and real-time mouse-driven parallax to create a sense of depth without heavy libraries.</p>
                <ul>
                    <li>GPU-friendly animations</li>
                    <li>Accessible content and semantic markup</li>
                    <li>Mobile-friendly responsive layout</li>
                </ul> -->
                <!-- Visitor Section -->
                <section class="py-5 bg-light">
                    <div class="container">
                        <div class="text-center mb-4">
                            <h2>Visitor Counter</h2>
                            <p class="lead">See how many people have visited our site</p>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <div class="card shadow-sm text-center">
                                    <div class="card-body">
                                        <?php
                                        // Visitor counter logic
                                        $counter_file = 'visitor_count.txt';
                                        if (!file_exists($counter_file)) {
                                            file_put_contents($counter_file, "0");
                                        }
                                        $visitor_count = (int) file_get_contents($counter_file);
                                        $visitor_count++;
                                        file_put_contents($counter_file, (string) $visitor_count);

                                        // Date and time
                                        date_default_timezone_set('Asia/Kolkata'); // Set your timezone
                                        $visit_date = date('F j, Y');
                                        $visit_time = date('h:i:s A');
                                        ?>
                                        <h3 class="mb-3">Total Visitors: <span
                                                class="text-primary"><?= $visitor_count ?></span>
                                        </h3>
                                        <p class="mb-1"><strong>Date:</strong> <?= $visit_date ?></p>
                                        <p class="mb-0"><strong>Time:</strong> <?= $visit_time ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
            <div class="col-md-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card p-3 feature-card">
                            <h5>Events</h5>
                            <p class="mb-0">See what's coming up — open houses, fairs, and lectures.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-3 feature-card">
                            <h5>News</h5>
                            <p class="mb-0">Latest announcements and blog posts from the community.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-3 feature-card">
                            <h5>Admissions</h5>
                            <p class="mb-0">Apply and track your application status quickly.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card p-3 feature-card">
                            <h5>Campus Life</h5>
                            <p class="mb-0">Discover student clubs, activities, and support services.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Separate Founder, Principal, and Staff sections
$founders = [];
$principals = [];
$staff = [];
if (isset($conn)) {
    // Founder(s)
    $rf = @$conn->query("SELECT name, designation, photo, qualification, bio FROM staff WHERE designation LIKE '%Founder%' LIMIT 4");
    if ($rf && $rf->num_rows > 0) {
        while ($r = $rf->fetch_assoc()) {
            $founders[] = [
                'name' => $r['name'] ?? 'Unknown',
                'designation' => $r['designation'] ?? '',
                'photo' => $r['photo'] ?? 'assets/images/default.jpg',
                'qualification' => $r['qualification'] ?? '',
                'bio' => $r['bio'] ?? ''
            ];
        }
    }
    // Principal(s)
    $rp = @$conn->query("SELECT name, designation, photo, qualification, bio FROM staff WHERE designation LIKE '%Principal%' LIMIT 2");
    if ($rp && $rp->num_rows > 0) {
        while ($r = $rp->fetch_assoc()) {
            $principals[] = [
                'name' => $r['name'] ?? 'Unknown',
                'designation' => $r['designation'] ?? '',
                'photo' => $r['photo'] ?? 'assets/images/default.jpg',
                'qualification' => $r['qualification'] ?? '',
                'bio' => $r['bio'] ?? ''
            ];
        }
    }
    // Other staff
    $rs = @$conn->query("SELECT name, designation, qualification, photo FROM staff WHERE designation NOT LIKE '%Founder%' AND designation NOT LIKE '%Principal%' ORDER BY id ASC LIMIT 8");
    if ($rs && $rs->num_rows > 0) {
        while ($r = $rs->fetch_assoc()) {
            $staff[] = [
                'name' => $r['name'] ?? 'Unknown',
                'designation' => $r['designation'] ?? '',
                'qualification' => $r['qualification'] ?? '',
                'photo' => $r['photo'] ?? 'assets/images/default.jpg'
            ];
        }
    }
}

// Fallbacks if no data from DB
if (empty($founders)) {
    $founders = [
        [
            'name' => 'श्री शशिधर पंडा',
            'designation' => 'Founder',
            'photo' => 'img/founder/IMG-20250403-WA0016.jpg',
            'qualification' => 'M.A., B.Ed.',
            'bio' => 'प्रख्यात समाजसेवी एवं व्यवस्थापक'
        ]
    ];
}
if (empty($principals)) {
    $principals = [
        [
            'name' => 'Dr. patel',
            'designation' => 'Principal',
            'photo' => 'assets/images/principal.jpg',
            'qualification' => 'Ph.D., M.Ed.',
            'bio' => 'Committed to academic excellence and student success.'
        ]
    ];
}
if (empty($staff)) {
    $staff = [
        ['name' => 'Alex Johnson', 'designation' => 'Professor', 'qualification' => 'PhD', 'photo' => 'assets/images/student1.jpg'],
        ['name' => 'Sarah Williams', 'designation' => 'Lecturer', 'qualification' => 'MBA', 'photo' => 'assets/images/student2.jpg'],
        ['name' => 'Michael Brown', 'designation' => 'Instructor', 'qualification' => 'MSc', 'photo' => 'assets/images/student3.jpg'],
    ];
}
?>

<!-- clip-path divider -->
<div class="clip-divider" aria-hidden="true">
    <div class="polygon-bg"></div>
</div>

<!-- Founder's Desk -->
<section id="founder" class="founder-section">
    <div class="container">
        <div class="text-center mb-4">
            <h2>From the Founder's Desk</h2>
            <p class="lead">Messages from our founders and visionaries</p>
        </div>
        <div class="row justify-content-center">
            <?php foreach ($founders as $f): ?>
                <div class="col-md-8 mb-4">
                    <div class="card founder-card p-3 shadow-sm">
                        <img src="<?= htmlspecialchars($f['photo']) ?>" alt="<?= htmlspecialchars($f['name']) ?>"
                            class="founder-img">
                        <div>
                            <h4><?= htmlspecialchars($f['name']) ?></h4>
                            <p class="text-primary mb-1"><?= htmlspecialchars($f['designation']) ?> •
                                <?= htmlspecialchars($f['qualification'] ?? '') ?>
                            </p>
                            <p><?= nl2br(htmlspecialchars($f['bio'] ?? '')) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Principal's Desk -->
<section id="principal" class="founder-section bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <h2>From the Principal's Desk</h2>
            <p class="lead">Principal's message and academic priorities</p>
        </div>
        <div class="row justify-content-center">
            <?php foreach ($principals as $p): ?>
                <div class="col-md-8 mb-4">
                    <div class="card founder-card p-3 shadow-sm">
                        <img src="<?= htmlspecialchars($p['photo']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                            class="founder-img">
                        <div>
                            <h4><?= htmlspecialchars($p['name']) ?></h4>
                            <p class="text-primary mb-1"><?= htmlspecialchars($p['designation']) ?> •
                                <?= htmlspecialchars($p['qualification'] ?? '') ?>
                            </p>
                            <p><?= nl2br(htmlspecialchars($p['bio'] ?? '')) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Staff Section -->
<section id="staff" class="staff-section bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <h2>Our Staff</h2>
            <p class="lead">Meet the dedicated faculty and staff</p>
        </div>
        <div class="row g-4">
            <?php foreach ($staff as $i => $m): ?>
                <?php $idAttr = isset($m['id']) ? $m['id'] : $i; ?>
                <div class="col-6 col-md-3">
                    <div class="card h-100 text-center staff-card shadow-sm p-3" style="cursor:pointer"
                        data-staff-id="<?= htmlspecialchars($idAttr, ENT_QUOTES) ?>"
                        data-staff-name="<?= htmlspecialchars($m['name'] ?? '', ENT_QUOTES) ?>"
                        data-staff-photo="<?= htmlspecialchars($m['photo'] ?? '', ENT_QUOTES) ?>"
                        data-staff-designation="<?= htmlspecialchars($m['designation'] ?? '', ENT_QUOTES) ?>"
                        data-staff-qualification="<?= htmlspecialchars($m['qualification'] ?? '', ENT_QUOTES) ?>"
                        data-staff-bio="<?= htmlspecialchars($m['bio'] ?? '', ENT_QUOTES) ?>">
                        <img src="<?= htmlspecialchars($m['photo']) ?>" alt="<?= htmlspecialchars($m['name']) ?>"
                            class="rounded-circle mb-3">
                        <h5 class="mb-0"><?= htmlspecialchars($m['name']) ?></h5>
                        <p class="text-primary small mb-0"><?= htmlspecialchars($m['designation']) ?></p>
                        <small class="text-muted"><?= htmlspecialchars($m['qualification'] ?? '') ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="staff.php" class="btn btn-primary">View All Staff</a>
        </div>
    </div>
</section>

<!-- Single reusable Staff Modal (lazy-populated) -->
<div class="modal fade" id="staffDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staffDetailTitle">Staff Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="staffDetailBody">
                <img id="staffDetailPhoto" src="" alt="" class="rounded-circle mb-3"
                    style="width:120px; height:120px; object-fit:cover; display:none;">
                <p class="mb-1"><strong id="staffDetailDesignation"></strong></p>
                <p class="text-muted small" id="staffDetailQualification"></p>
                <hr>
                <p id="staffDetailBio"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const modalEl = document.getElementById('staffDetailModal');
        const bsModal = new bootstrap.Modal(modalEl);
        const title = document.getElementById('staffDetailTitle');
        const photo = document.getElementById('staffDetailPhoto');
        const designation = document.getElementById('staffDetailDesignation');
        const qualification = document.getElementById('staffDetailQualification');
        const bio = document.getElementById('staffDetailBio');

        document.querySelectorAll('.staff-card').forEach(card => {
            card.addEventListener('click', () => {
                const name = card.getAttribute('data-staff-name') || '';
                const photoSrc = card.getAttribute('data-staff-photo') || '';
                const desig = card.getAttribute('data-staff-designation') || '';
                const qual = card.getAttribute('data-staff-qualification') || '';
                const bios = card.getAttribute('data-staff-bio') || '';
                title.textContent = name;
                if (photoSrc) { photo.src = photoSrc; photo.style.display = 'block'; } else { photo.style.display = 'none'; }
                designation.textContent = desig;
                qualification.textContent = qual;
                bio.innerHTML = bios ? bios.replace(/\n/g, '<br>') : '';
                bsModal.show();
            });
        });
    })();
</script>

<?php include('footer.php'); ?>

<!-- expose server-side reduced-motion flag for the external script -->
<script>window.serverForceReduced = <?= !empty($server_force_reduced) ? 'true' : 'false' ?>;</script>
<script src="js/landing.js" defer></script>