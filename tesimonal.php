<?php
function createTestimonial($pdo, $name, $program, $year, $image, $rating, $text)
{
    $sql = "INSERT INTO testimonials (name, program, graduation_year, image_url, rating, testimonial)
            VALUES (:name, :program, :year, :image, :rating, :text)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name' => $name,
        ':program' => $program,
        ':year' => $year,
        ':image' => $image,
        ':rating' => $rating,
        ':text' => $text
    ]);
}
function getTestimonials($pdo)
{
    $sql = "SELECT * FROM testimonials ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function updateTestimonial($pdo, $id, $name, $program, $year, $image, $rating, $text)
{
    $sql = "UPDATE testimonials SET name = :name, program = :program, graduation_year = :year,
            image_url = :image, rating = :rating, testimonial = :text WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':program' => $program,
        ':year' => $year,
        ':image' => $image,
        ':rating' => $rating,
        ':text' => $text
    ]);
}
function deleteTestimonial($pdo, $id)
{
    $sql = "DELETE FROM testimonials WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
}
?>
<?php
require 'db.php'; // Your PDO connection file

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    createTestimonial($pdo, $data['name'], $data['program'], $data['year'], $data['image'], $data['rating'], $data['testimonial']);
    echo json_encode(['message' => 'Testimonial saved!']);
} else {
    echo json_encode(['message' => 'Invalid data']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>getTestimonials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">   

</head>

<body>
   

    <!-- bootstrap code  -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Submit Your Testimonial</h4>
                    </div>
                    <div class="card-body bg-light">
                        <form id="testimonialForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="program" class="form-label">Program</label>
                                <input type="text" class="form-control" id="program" required>
                            </div>
                            <div class="mb-3">
                                <label for="year" class="form-label">Graduation Year</label>
                                <input type="number" class="form-control" id="year" required>
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image URL</label>
                                <input type="url" class="form-control" id="image">
                            </div>
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating (0–5)</label>
                                <input type="number" step="0.5" min="0" max="5" class="form-control" id="rating"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="testimonial" class="form-label">Testimonial</label>
                                <textarea class="form-control" id="testimonial" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Submit Testimonial</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- bootstrapout code ended here  --> -
    <script>
        document.getElementById('testimonialForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const data = {
                name: document.getElementById('name').value,
                program: document.getElementById('program').value,
                year: document.getElementById('year').value,
                image: document.getElementById('image').value,
                rating: document.getElementById('rating').value,
                testimonial: document.getElementById('testimonial').value
            };

            fetch('create_testimonial.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(response => {
                    showAlert(response.message || 'Testimonial submitted successfully!', 'success');
                    document.getElementById('testimonialForm').reset();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Something went wrong. Please try again.', 'danger');
                });
        });

        function showAlert(message, type) {
            const alertBox = document.createElement('div');
            alertBox.className = `alert alert-${type} mt-3`;
            alertBox.textContent = message;
            document.querySelector('.card-body').appendChild(alertBox);
            setTimeout(() => alertBox.remove(), 5000);
        }
    </script>

    <!-- script stated here -->
    <script>
        document.getElementById('testimonialForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const data = {
                name: document.getElementById('name').value,
                program: document.getElementById('program').value,
                year: document.getElementById('year').value,
                image: document.getElementById('image').value,
                rating: document.getElementById('rating').value,
                testimonial: document.getElementById('testimonial').value
            };

            fetch('create_testimonial.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(response => {
                    alert(response.message || 'Testimonial submitted successfully!');
                    document.getElementById('testimonialForm').reset();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong. Please try again.');
                });
        });
    </script>
    <!-- script ended here -->
</body>

</html>
