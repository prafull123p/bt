<?php
include('header.php');
include 'db.php';
?>
<main>
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Our Staff</h2>
                <p class="lead">Meet all our dedicated faculty and staff members</p>
            </div>
            <div class="row g-4">
                <?php
                $result = $conn->query("SELECT name, designation, qualification, photo FROM staff ORDER BY id DESC");
                if ($result && $result->num_rows > 0):
                    while ($member = $result->fetch_assoc()):
                ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 shadow-sm text-center">
                            <div class="pt-4">
                                <img src="<?= htmlspecialchars($member['photo']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover;">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($member['name']) ?></h5>
                                <p class="text-primary mb-1"><?= htmlspecialchars($member['designation']) ?></p>
                                <p class="mb-0"><small><?= htmlspecialchars($member['qualification']) ?></small></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                    <div class="col-12">
                        <div class="alert alert-secondary text-center mb-0">No staff members found.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php include('footer.php'); ?>