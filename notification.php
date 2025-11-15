<?php include('header.php'); ?>
<?php include 'db.php'; ?>

<style>
    .notif-hero{background:linear-gradient(180deg,#fff 0,#f1f5ff 100%);padding:3rem 0}
    .event-card, .news-card{transition:transform .18s ease, box-shadow .18s ease;cursor:pointer}
    .event-card:hover, .news-card:hover{transform:translateY(-10px) scale(1.02);box-shadow:0 30px 60px rgba(10,10,30,0.12)}
    .glass{background:linear-gradient(180deg, rgba(255,255,255,0.6), rgba(255,255,255,0.45));backdrop-filter:blur(6px)}
    .ticker{overflow:hidden;white-space:nowrap}
    .ticker div{display:inline-block;padding-right:3rem;animation:slide 12s linear infinite}
    @keyframes slide{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
    .section-title{display:flex;align-items:center;gap:1rem}
</style>

<main>
    <section class="notif-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Notifications & Events</h1>
                    <p class="lead">Stay informed about upcoming events, announcements, and news. Hover over cards to explore.</p>
                    <div class="ticker mt-3">
                        <div id="tickerContent">
                            <?php
                            // build ticker from notification table (fallback to a single message)
                            $notices = [];
                            if (isset($conn) && $conn) {
                                $sql = "SELECT title, message, created_at FROM notification ORDER BY created_at DESC LIMIT 12";
                                $res = @$conn->query($sql);
                                if ($res && $res->num_rows > 0) {
                                    while ($row = $res->fetch_assoc()) $notices[] = $row;
                                }
                            }

                            if (empty($notices)) {
                                $notices = [
                                    ['title' => 'No notifications', 'message' => 'There are currently no notifications.', 'created_at' => date('Y-m-d')],
                                ];
                            }

                            // prepare display items combining title and (optional) message
                            $items = [];
                            foreach ($notices as $n) {
                                $title = trim($n['title'] ?? '');
                                $msg = trim($n['message'] ?? '');
                                $text = $title;
                                if ($msg !== '') $text .= ' — ' . $msg;
                                $items[] = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            }

                            // duplicate sequence for smooth scrolling
                            $seq = implode(' • ', $items);
                            echo '<div>' . $seq . ' • ' . $seq . '</div>';
                            ?>
                        </div>
                    </div>
                </div>
                <!-- <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="register.php" class="btn btn-primary">Apply / Register</a>
                </div> -->
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="section-title mb-4">
                <h2 class="mb-0">Upcoming Events</h2>
            </div>
            <div class="row g-4" id="eventsList">
                <?php
                // Try to read events table; fallback to hardcoded list if table doesn't exist or query fails
                $events = [];
                $events_query = "SELECT title, description, event_date FROM events ORDER BY event_date ASC LIMIT 6";
                if ($conn) {
                    $res = @$conn->query($events_query);
                    if ($res && $res->num_rows>0) {
                        while($r=$res->fetch_assoc()) $events[] = $r;
                    }
                }
                if (empty($events)){
                    $events = [
                        ['title'=>'Open House','description'=>'Campus tour, program previews and Q&A.','event_date'=>'2025-11-15','venue'=>'Main Auditorium'],
                        ['title'=>'STEM Fair','description'=>'Student projects, demonstrations and competitions.','event_date'=>'2025-12-05','venue'=>'Exhibition Hall'],
                        ['title'=>'Alumni Meet','description'=>'Reconnect with alumni and faculty.','event_date'=>'2026-01-20','venue'=>'Conference Center'],
                    ];
                }

                foreach($events as $ev):
                ?>
                <div class="col-md-4">
                    <div class="card event-card h-100 shadow-sm p-3 glass" onmousemove="tiltCard(event,this)" onmouseleave="resetCard(this)">
                        <div class="card-body">
                            <h5 class="card-title"><?=htmlspecialchars($ev['title'])?></h5>
                            <p class="text-muted small mb-1"><?=date('F j, Y', strtotime($ev['event_date']))?> • <?=htmlspecialchars($ev['venue'] ?? '')?></p>
                            <p class="card-text"><?=nl2br(htmlspecialchars(mb_strimwidth($ev['description'],0,120,'...'))) ?></p>
                            <a href="#" class="btn btn-sm btn-outline-primary">Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="section-title mb-4">
                <h2 class="mb-0">Latest News</h2>
            </div>
            <div class="row g-4" id="newsList">
                <?php
                // Reuse blog posts query from index.php
                $news = [];
                $news_query = "SELECT id, title, content, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 6";
                if ($conn) {
                    $res2 = @$conn->query($news_query);
                    if ($res2 && $res2->num_rows>0) {
                        while($r=$res2->fetch_assoc()) $news[] = $r;
                    }
                }
                if (empty($news)){
                    $news = [
                        ['title'=>'Site Update: New Landing','content'=>'We launched a refreshed landing page with interactive 3D visuals.','created_at'=>date('Y-m-d')],
                    ];
                }

                foreach($news as $n):
                ?>
                <div class="col-md-4">
                    <div class="card news-card h-100 shadow-sm p-3" onmousemove="tiltCard(event,this)" onmouseleave="resetCard(this)">
                        <div class="card-body">
                            <h5 class="card-title"><?=htmlspecialchars($n['title'])?></h5>
                            <p class="text-muted small mb-1"><?=date('F j, Y', strtotime($n['created_at']))?></p>
                            <p class="card-text"><?=nl2br(htmlspecialchars(mb_strimwidth($n['content'],0,140,'...'))) ?></p>
                            <a href="admin_blog.php" class="btn btn-sm btn-outline-primary">Read More</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>


<?php include('footer.php'); ?>

<script>
    // simple card tilt effect
    function tiltCard(e, el){
        const rect = el.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        const rotX = (y * 10) * -1;
        const rotY = x * 10;
        el.style.transform = `perspective(700px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(6px)`;
    }
    function resetCard(el){el.style.transform=''}

    // populate ticker from news items
    (function(){
        const ticker = document.getElementById('tickerContent');
        if(!ticker) return;
        const items = [];
        document.querySelectorAll('#newsList .card-title').forEach(t=>items.push(t.textContent.trim()));
        if(items.length===0) items.push('No news at the moment.');
        // duplicate to create continuous scroll
        ticker.innerHTML = '<div>'+items.join(' • ')+' • '+items.join(' • ')+'</div>';
    })();
</script>
