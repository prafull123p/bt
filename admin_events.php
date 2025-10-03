<?php
include('auth.php');
include 'db.php';

// CRUD Logic
$message = '';
$edit_mode = false;
$edit_id = null;
$edit_title = '';
$edit_date = '';
$edit_description = '';

// CREATE or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (isset($_POST['save_event'])) {
        if ($title && $date && $description) {
            $stmt = $conn->prepare("INSERT INTO events (title, event_date, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $date, $description);
            $stmt->execute();
            $stmt->close();
            $message = "Event created successfully!";
        } else {
            $message = "All fields are required.";
        }
    } elseif (isset($_POST['update_event']) && isset($_POST['edit_id'])) {
        $edit_id = intval($_POST['edit_id']);
        if ($title && $date && $description) {
            $stmt = $conn->prepare("UPDATE events SET title=?, event_date=?, description=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $date, $description, $edit_id);
            $stmt->execute();
            $stmt->close();
            $message = "Event updated successfully!";
        } else {
            $message = "All fields are required.";
        }
    }
}

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Event deleted.";
}

// EDIT (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT title, event_date, description FROM events WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_title, $edit_date, $edit_description);
    $stmt->fetch();
    $stmt->close();
}

// READ (fetch all)
$events = [];
$result = $conn->query("SELECT id, title, event_date, description FROM events ORDER BY event_date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}
?>

<main>
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2>Event Management</h2>
                <p class="lead">Create, edit, and delete events</p>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- Event Form -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="<?= $edit_mode ? 'edit_id' : '' ?>" value="<?= $edit_mode ? $edit_id : '' ?>">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Event Title</label>
                                    <input type="text" name="title" id="title" class="form-control" required value="<?= htmlspecialchars($edit_title) ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="date" class="form-label">Event Date</label>
                                    <input type="date" name="date" id="date" class="form-control" required value="<?= htmlspecialchars($edit_date) ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="4" required><?= htmlspecialchars($edit_description) ?></textarea>
                                </div>
                                <?php if ($edit_mode): ?>
                                    <button type="submit" name="update_event" class="btn btn-success">Update Event</button>
                                    <a href="admin_events.php" class="btn btn-secondary">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="save_event" class="btn btn-primary">Add Event</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Event List -->
            <div class="row">
                <div class="col-12">
                    <h4 class="mb-3">All Events</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($events): ?>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($event['title']) ?></td>
                                            <td><?= htmlspecialchars($event['event_date']) ?></td>
                                            <td><?= nl2br(htmlspecialchars(mb_strimwidth($event['description'], 0, 80, '...'))) ?></td>
                                            <td>
                                                <a href="admin_events.php?edit=<?= $event['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="admin_events.php?delete=<?= $event['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No events found.</td>
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