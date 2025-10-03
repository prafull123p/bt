<?php

include('auth.php');
include 'db.php';

// CRUD Logic
$message = '';
$edit_mode = false;
$edit_id = null;
$edit_title = '';
$edit_content = '';
$edit_image = '';

// CREATE or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $img_name = basename($_FILES['image']['name']);
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($img_ext, $allowed)) {
            $img_new_name = uniqid('blog_', true) . '.' . $img_ext;
            $img_dest = 'uploads/blog/' . $img_new_name;
            if (!is_dir('uploads/blog')) {
                mkdir('uploads/blog', 0777, true);
            }
            if (move_uploaded_file($img_tmp, $img_dest)) {
                $image_path = $img_dest;
            }
        }
    }

    if (isset($_POST['save_blog'])) {
        if ($title && $content) {
            $stmt = $conn->prepare("INSERT INTO blog_posts (title, content, image, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $title, $content, $image_path);
            $stmt->execute();
            $stmt->close();
            $message = "Blog post created successfully!";
        } else {
            $message = "Title and content are required.";
        }
    } elseif (isset($_POST['update_blog']) && isset($_POST['edit_id'])) {
        $edit_id = intval($_POST['edit_id']);
        if ($title && $content) {
            if ($image_path) {
                $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=?, image=? WHERE id=?");
                $stmt->bind_param("sssi", $title, $content, $image_path, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=? WHERE id=?");
                $stmt->bind_param("ssi", $title, $content, $edit_id);
            }
            $stmt->execute();
            $stmt->close();
            $message = "Blog post updated successfully!";
        } else {
            $message = "Title and content are required.";
        }
    }
}

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Optionally delete the image file from server
    $img_res = $conn->query("SELECT image FROM blog_posts WHERE id=$delete_id");
    if ($img_res && $img_row = $img_res->fetch_assoc()) {
        if (!empty($img_row['image']) && file_exists($img_row['image'])) {
            unlink($img_row['image']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Blog post deleted.";
}

// EDIT (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT title, content, image FROM blog_posts WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_title, $edit_content, $edit_image);
    $stmt->fetch();
    $stmt->close();
}

// READ (fetch all)
$blog_posts = [];
$result = $conn->query("SELECT id, title, content, image, created_at FROM blog_posts ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $blog_posts[] = $row;
    }
}
?>

<main>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Blog Management</h2>
                <p class="lead">Create, edit, and delete blog posts</p>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- Blog Form -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="<?= $edit_mode ? 'edit_id' : '' ?>" value="<?= $edit_mode ? $edit_id : '' ?>">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" name="title" id="title" class="form-control" required value="<?= htmlspecialchars($edit_title) ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content</label>
                                    <textarea name="content" id="content" class="form-control" rows="5" required><?= htmlspecialchars($edit_content) ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Image (optional)</label>
                                    <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                    <?php if ($edit_mode && $edit_image): ?>
                                        <div class="mt-2">
                                            <img src="<?= htmlspecialchars($edit_image) ?>" alt="Blog Image" style="max-width:120px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($edit_mode): ?>
                                    <button type="submit" name="update_blog" class="btn btn-success">Update Blog Post</button>
                                    <a href="admin_blog.php" class="btn btn-secondary">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="save_blog" class="btn btn-primary">Add Blog Post</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blog List -->
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-3">All Blog Posts</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Content</th>
                                    <th>Created At</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($blog_posts): ?>
                                    <?php foreach ($blog_posts as $post): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($post['image'])): ?>
                                                    <img src="<?= htmlspecialchars($post['image']) ?>" alt="Blog Image" style="max-width:80px;">
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($post['title']) ?></td>
                                            <td><?= nl2br(htmlspecialchars(mb_strimwidth($post['content'], 0, 80, '...'))) ?></td>
                                            <td><?= htmlspecialchars($post['created_at']) ?></td>
                                            <td>
                                                <a href="admin_blog.php?edit=<?= $post['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="admin_blog.php?delete=<?= $post['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this blog post?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No blog posts found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include('footer.php'); ?>