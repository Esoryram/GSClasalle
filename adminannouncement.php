<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;

// Fetch announcements
$announcementsQuery = "SELECT * FROM Announcements ORDER BY created_at DESC LIMIT 5";
$announcementsResult = mysqli_query($conn, $announcementsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* ===== NAVBAR ===== */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f4f4;
            padding-top: 80px;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
            color: white;
            padding: 15px 30px;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar .logo img {
            height: 40px;
            width: auto;
        }

        .navbar .logo h2 {
            margin: 0;
            font-size: 22px;
        }

        .return-btn {
            background: #107040;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .return-btn:hover {
            background: #07532e;
        }

        .container {
            padding: 20px 60px;
            gap: 30px;
        }

        /* ===== CARDS ===== */
        .status-cards-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            min-height: 150px;
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .card-icon {
            font-size: 22px;
            color: #1f9158;
        }

        .card-value {
            font-weight: 600;
            font-size: 16px;
            color: #275850;
        }

        .card-label {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }

        .card-date {
            font-size: 12px;
            color: #888;
        }

        .card-buttons {
            display: flex;
            gap: 5px;
            margin-top: auto;
        }

        .update-btn,
        .delete-btn {
            flex: 1;
            padding: 5px 0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
        }

        .update-btn {
            background-color: #0d6efd;
            color: white;
        }

        .update-btn:hover {
            background-color: #084298;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #a71d2a;
        }

        /* ===== FORM ===== */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .form-card h3 {
            font-size: 20px;
            color: #1f9158;
            margin-bottom: 15px;
        }

        .submit-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(90deg, #0c3c2f, #116546);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
        }

        .submit-btn:hover {
            background: #07532e;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <div class="logo">
            <img src="img/LSULogo.png" alt="LSU Logo">
            <h2>Announcement</h2>
        </div>
        <button class="return-btn" onclick="window.history.back()">Return</button>
    </div>

    <div class="container">

        <!-- Recent Announcements -->
        <div class="status-cards-wrapper">
            <?php if ($announcementsResult && mysqli_num_rows($announcementsResult) > 0): ?>
                <?php while ($a = mysqli_fetch_assoc($announcementsResult)): ?>
                    <div class="dashboard-card">
                        <div>
                            <div class="card-icon"><i class="fas fa-bullhorn"></i></div>
                            <div class="card-value"><?= htmlspecialchars($a['title']) ?></div>
                            <div class="card-label"><?= htmlspecialchars(substr($a['content'], 0, 60)) . '...' ?></div>
                            <div class="card-date"><?= date("M d, Y", strtotime($a['created_at'])) ?></div>
                        </div>

                        <div class="card-buttons">
                            <button 
                                class="update-btn"
                                data-id="<?= $a['AnnouncementID'] ?>"
                                data-title="<?= htmlspecialchars($a['title']) ?>"
                                data-content="<?= htmlspecialchars($a['content']) ?>"
                                data-start="<?= $a['start_date'] ?>"
                                data-end="<?= $a['end_date'] ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#updateModal">
                                Update
                            </button>

                            <button 
                                class="delete-btn"
                                onclick="if(confirm('Are you sure you want to delete this announcement?')) window.location.href='announcement_delete.php?id=<?= $a['AnnouncementID'] ?>'">
                                Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="dashboard-card">No announcements yet</div>
            <?php endif; ?>
        </div>

        <!-- Post Announcement Form -->
        <div class="form-card">
            <h3>Post New Announcement</h3>
            <form action="announcement_process.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Announcement Title</label>
                    <input type="text" class="form-control" name="title" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea class="form-control" name="content" rows="4" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </div>

                <button type="submit" class="submit-btn">Post Announcement</button>
            </form>
        </div>
    </div>

    <!-- ===== Update Modal ===== -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content form-card">
                <div class="modal-header border-0">
                    <h3 class="modal-title">Update Announcement</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="updateForm">
                    <input type="hidden" name="AnnouncementID" id="update_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Announcement Title</label>
                            <input type="text" class="form-control" id="update_title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" id="update_content" name="content" rows="4" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="update_start" name="start_date" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" id="update_end" name="end_date">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Fill modal with data
        $('#updateModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            $('#update_id').val(button.data('id'));
            $('#update_title').val(button.data('title'));
            $('#update_content').val(button.data('content'));
            $('#update_start').val(button.data('start'));
            $('#update_end').val(button.data('end'));
        });

        // AJAX Update
        $('#updateForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'announcement_update_process.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    alert(response);
                    location.reload();
                },
                error: function () {
                    alert('Update failed.');
                }
            });
        });
    </script>
</body>
</html>
