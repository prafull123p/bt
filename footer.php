<!-- Footer will be added here -->

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="js\script.js">
</script>
<!-- Footer -->

<?php
// Fetch footer settings from database
include_once 'db.php';
$footer = [
    'address' => '',
    'contact' => '',
    'email' => '',
    'map' => ''
];
$res = $conn->query("SELECT * FROM footer_settings LIMIT 1");
if ($res && $res->num_rows > 0) {
    $footer = $res->fetch_assoc();
}
?>
<footer class=" py-4 mt-5"
    style="background-color:#2c3e50; color:white; padding:40px 0; font-family:Arial, sans-serif;">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <h5 class="mb-2">Contact Us</h5>
                <p class="mb-1"><i class="bi bi-geo-alt-fill me-2"></i></p>
                <p class="mb-1"><i class="bi bi-telephone-fill me-2"></i></p>
                <p class="mb-0"><i class="bi bi-envelope-fill me-2"></i></p>
            </div>
             <div class="col-md-4 mb-3 mb-md-0">
                <h5 class="mb-2">Useful Links</h5>
                <li>
                    <a href="//voterportal.eci.gov.in" target="blank" class="lcr">www.voterportal.eci.gov.in</a>
                </li>
                <!-- <li>
                    <a href="//rajbhavancg.gov.in" target="blank">www.rajbhavancg.gov.in</a>
                </li> -->
                <li>
                    <a href="//www.ugc.ac.in" target="blank">www.ugc.ac.in</a>
                </li>
                <li>
                    <a href="//www.swayam.gov.in" target="blank">www.swayam.gov.in</a>
                </li>
                <!-- <li>
                    <a href="//aishe.gov.in" target="blank">www.aishe.gov.in</a>
                </li> -->
                <li>
                    <a href="//nptel.ac.in" target="blank">www.nptel.ac.in</a>
                </li>
                <li>
                    <a href="//aicte-india.org" target="blank">www.aicte-india.org</a>
                </li>
                <!-- <li>
                    <a href="//scert.cg.gov.in" target="blank">www.scert.cg.gov.in</a>
                </li> -->
                <li>
                    <a href="http://naac.gov.in/" target="blank">www.naac.gov.in</a>
                </li>
                <li>
                    <a href="//www.swayamprabha.gov.in" target="blank">www.swayamprabha.gov.in</a>
                </li>

            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <h5 class="mb-2">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="about.php" class="text-white text-decoration-none">About Us</a></li>
                    <li><a href="gallery.php" class="text-white text-decoration-none">Gallery</a></li>
                    <li><a href="contact.php" class="text-white text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="mb-2">Find Us</h5>
                <?php if (!empty($footer['map'])): ?>
                    <div class="ratio ratio-4x3">
                        <?= $footer['map']; ?>
                        
                    </div>
                <?php else: ?>
                    <p class="text-muted">Map not available.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center pt-3 mt-3 border-top border-secondary">
            <small>&copy; <?= date('Y') ?> BT.</small>
        </div>
    </div>
    <!-- Footer Section -->
</footer>
</body>