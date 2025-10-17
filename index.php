<?php include('header.php'); ?>
<?php include 'db.php'; ?>

<?php
// Fetch latest 3 blog posts
$blog_posts = [];
$result = $conn->query("SELECT id, title, content, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 3");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $blog_posts[] = $row;
    }
}
?>


<!-- Page content will go here -->
<main style="margin:0px; padding:0px; " class="">
    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white py-5 polyg" style="margin: 0; padding: 0;">
        <?php include 'hero.php'; ?>
        <!-- include 'carousel_view.php'; -->
    </section>


    <!-- news section -->
    <?php
    include('info.php')
        ?>
    <!-- news section end here -->

    <!-- Quick Facts -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-white shadow-sm">
                        <h2 class="text-primary">95%</h2>
                        <p class="mb-0">Graduation Rate</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-white shadow-sm">
                        <h2 class="text-primary">50+</h2>
                        <p class="mb-0">Academic Programs</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-white shadow-sm">
                        <h2 class="text-primary">1:12</h2>
                        <p class="mb-0">Faculty-Student Ratio</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 border rounded bg-white shadow-sm">
                        <h2 class="text-primary">90%</h2>
                        <p class="mb-0">Employment After Graduation</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Programs -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Featured Academic Programs</h2>
                <p class="lead">Explore our most popular programs</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="assets/images/computer-science.jpg" class="card-img-top" alt="Computer Science">
                        <div class="card-body">
                            <h5 class="card-title">Computer Science Application</h5>
                            <p class="card-text">Learn cutting-edge technologies and prepare for careers in
                                software development, AI, and more.</p>
                            <a href="academics.html" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="assets/images/business-admin.jpg" class="card-img-top" alt="Business Administration">
                        <div class="card-body">
                            <h5 class="card-title">Business Administration</h5>
                            <p class="card-text">Develop leadership skills and business acumen with our
                                AACSB-accredited program.</p>
                            <a href="academics.html" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <img src="assets/images/engineering.jpg" class="card-img-top" alt="Engineering">
                        <div class="card-body">
                            <h5 class="card-title">Engineering</h5>
                            <p class="card-text">Hands-on learning in mechanical, electrical, and civil
                                engineering disciplines.</p>
                            <a href="academics.html" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="academics.html" class="btn btn-primary">View All Programs</a>
            </div>
        </div>
    </section>

    <!-- Campus Life
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <img src="assets/images/campus-life.jpg" alt="Campus Life" class="img-fluid rounded shadow">
                </div>
                <div class="col-lg-6">
                    <h2>Vibrant Campus Life</h2>
                    <p class="lead">Experience more than just academics</p>
                    <p>Our campus offers a dynamic environment with over 100 student organizations, NCAA
                        Division I athletics, and a thriving arts scene.</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> 100+ student
                            organizations</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> NCAA Division I
                            athletics</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Performing arts
                            center</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> State-of-the-art
                            recreation center</li>
                    </ul>
                    <a href="campus-life.html" class="btn btn-primary mt-3">Explore Campus Life</a>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Events Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Upcoming Events</h2>
                <p class="lead">Join us for these exciting events</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Open House</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted"><i class="far fa-calendar-alt me-2"></i> June 15, 2023</p>
                            <p>Tour our campus, meet faculty, and learn about our programs at our annual
                                open house.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">Register</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">STEM Fair</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted"><i class="far fa-calendar-alt me-2"></i> July 8, 2023</p>
                            <p>Explore innovations in science, technology, engineering, and mathematics from
                                student projects.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Alumni Weekend</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted"><i class="far fa-calendar-alt me-2"></i> August 12-14,
                                2023</p>
                            <p>Reconnect with classmates and celebrate your college memories during this
                                special weekend.</p>
                            <a href="#" class="btn btn-sm btn-outline-primary">RSVP</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2>What Our Students Say</h2>
                <p class="lead">Hear from our community</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 bg-transparent border-light">
                        <div class="card-body text-center">
                            <img src="assets/images/student1.jpg" alt="Student" class="rounded-circle mb-3" width="100">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="card-text">"The Computer Science program provided me with both
                                theoretical knowledge and practical skills that were directly applicable in
                                my job."</p>
                            <h5 class="mt-3 mb-1">Alex Johnson</h5>
                            <p class="text-light mb-0">BSc Computer Science, 2022</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 bg-transparent border-light">
                        <div class="card-body text-center">
                            <img src="assets/images/student2.jpg" alt="Student" class="rounded-circle mb-3" width="100">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="card-text">"The MBA program transformed my career. The case study
                                approach and industry visits gave me real business perspectives."</p>
                            <h5 class="mt-3 mb-1">Sarah Williams</h5>
                            <p class="text-light mb-0">MBA, 2021</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 bg-transparent border-light">
                        <div class="card-body text-center">
                            <img src="assets/images/student3.jpg" alt="Student" class="rounded-circle mb-3" width="100">
                            <div class="mb-3 text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <p class="card-text">"The engineering labs are equipped with the latest
                                technology. The faculty goes beyond textbooks to explain concepts."</p>
                            <h5 class="mt-3 mb-1">Michael Brown</h5>
                            <p class="text-light mb-0">BEng Mechanical Engineering, 2023</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog Section -->
    <!-- /* HTML: <div class="ribbon">Your text content</div> */
.ribbon {
  font-size: 28px;
  font-weight: bold;
  color: #fff;
}
.ribbon {
  --f: .5em; /* control the folded part */
  
  position: absolute;
  top: 0;
  right: 0;
  line-height: 1.8;
  padding-inline: 1lh;
  padding-bottom: var(--f);
  border-image: conic-gradient(#0008 0 0) 51%/var(--f);
  clip-path: polygon(
    100% calc(100% - var(--f)),100% 100%,calc(100% - var(--f)) calc(100% - var(--f)),var(--f) calc(100% - var(--f)), 0 100%,0 calc(100% - var(--f)),999px calc(100% - var(--f) - 999px),calc(100% - 999px) calc(100% - var(--f) - 999px));
  transform: translate(calc((1 - cos(45deg))*100%), -100%) rotate(45deg);
  transform-origin: 0% 100%;
  background-color: #BD1550; /* the main color  */
} -->
    <section class="py-5 bg-light">

        <div class="container">
            <div class="text-center mb-5">
                <h2>Latest Blog Posts</h2>
                <p class="lead">Insights, news, and stories from our community</p>
            </div>
            <div class="row g-4">
                <?php if ($blog_posts): ?>
                    <?php foreach ($blog_posts as $post): ?>

                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                                    <p class="card-text">
                                        <?= nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 120, '...'))) ?>
                                    
                                    </p>

                                </div>
                                <div class="card-footer bg-white border-0">
                                    <small class="text-muted"><?= date('F j, Y', strtotime($post['created_at'])) ?></small>
                                </div>
                                
                            </div>

                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-secondary text-center mb-0">No blog posts found.</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <a href="admin_blog.php" class="btn btn-primary">View All Blog Posts</a>
            </div>
        </div>
    </section>
    <!-- Founder's Desk Section -->
    <section class="py-5 bg-white">
        <div class="container">
            
            <div class="text-center mb-5">
                <h2>From the Founder's Desk</h2>
                <p class="lead">Meet the visionary leaders who started it all</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-12 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="row g-0 align-items-center">
                            <div class="col-md-5 text-center">
                                <img src="img\founder\IMG-20250403-WA0016.jpg"
                                    alt="Shashidhar Panda seated and smiling warmly in formal attire against a neutral background, conveying a welcoming and inspirational atmosphere"
                                    class="img-fluid m-3" style="max-width: 180px;">
                            </div>
                            <div class="col-md-7">
                                
                                <div class="card-body">
                                    <h3 class="card-title">श्री शशिधर पंडा, महापल्ली</h3>
                                    <p class="text-muted mb-2">एम.ए. (इतिहास, भूगोल, हिंदी), बी.एड., साहित्य रत्न</p>

                                    <p class="card-text mb-2">
                                        प्रख्यात समाजसेवी, रायगढ़ ब्लॉक स्तरीय छत्तीसगढ़ शासन द्वारा ग्राम गौरव से
                                        सम्मानित, व्यवस्थापक गायत्री शक्तिपीठ<br>
                                        (Shri S.D. Panda)
                                    </p>
                                    <div class="mt-3">
                                        <a href="#" class="me-2"><i class="fab fa-linkedin fa-lg"></i></a>
                                        <a href="#" class="me-2"><i class="fab fa-twitter fa-lg"></i></a>
                                        <a href="#"><i class="fas fa-envelope fa-lg"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Our Staff Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Staff</h2>
                <p class="lead">Meet our dedicated faculty and staff members</p>
            </div>
            <div class="row g-4">
                <?php
                include 'db.php';
                $staff = [];

                // Ensure the connection is established
                if ($conn) {
                    // Prepare and execute the query
                    $query = "SELECT name, designation, qualification, photo FROM staff ORDER BY id ASC LIMIT 6";
                    $result = $conn->query($query);

                    // Check if the query was successful
                    if ($result && $result->num_rows > 0) {
                        // Fetch each row as an associative array
                        while ($row = $result->fetch_assoc()) {
                            $staff[] = $row;
                        }
                    } else {
                        echo "No staff records found or query failed.";
                    }
                } else {
                    echo "Database connection failed.";
                }
                //  print the result for debugging
                //  print_r($staff);
                ?>
                <?php if ($staff): ?>
                    <?php foreach ($staff as $member): ?>
                        <div class="col-md-4 col-lg-3">
                            <div class="card h-100 shadow-sm text-center">
                                <div class="pt-4">
                                    <img src="<?= htmlspecialchars($member['photo']) ?>"
                                        alt="<?= htmlspecialchars($member['name']) ?>" class="rounded-circle mb-3"
                                        style="width:100px;height:100px;object-fit:cover;">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($member['name']) ?></h5>
                                    <p class="text-primary mb-1"><?= htmlspecialchars($member['designation']) ?></p>
                                    <p class="mb-0"><small><?= htmlspecialchars($member['qualification']) ?></small></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-secondary text-center mb-0">No staff members found.</div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="staff.php" class="btn btn-primary">View All Staff</a>
            </div>
        </div>
    </section>

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
                            <h3 class="mb-3">Total Visitors: <span class="text-primary"><?= $visitor_count ?></span>
                            </h3>
                            <p class="mb-1"><strong>Date:</strong> <?= $visit_date ?></p>
                            <p class="mb-0"><strong>Time:</strong> <?= $visit_time ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-dark text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3>Ready to start your journey?</h3>
                    <p class="mb-lg-0">Applications for Fall 2023 are now open. Join our community of
                        scholars and innovators.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="admission.php" class="btn btn-light btn-lg me-2">Apply Now</a>
                    <a href="contact.html" class="btn btn-outline-light btn-lg">Contact Us</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include('footer.php'); ?>