<?php
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;

// Fetch recent announcements (limit 5)
$announcementsQuery = "SELECT * FROM Announcements ORDER BY created_at DESC LIMIT 5";
$announcementsResult = mysqli_query($conn, $announcementsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
        }

        .navbar {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
            padding: 15px 30px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        .navbar h2 {
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
            padding: 40px 60px; 
            gap: 30px; 
        }

        /* Recent Announcements Cards */
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
            text-align: left;
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
            opacity: 0.7; 
            margin-bottom: 10px; 
            color:#1f9158; 
        }

        .card-value { 
            font-weight: 600; 
            font-size: 16px; 
            margin-bottom: 5px; 
            color:#275850; 
        }

        .card-label { 
            font-size: 14px; 
            color:#555; 
            margin-bottom: 10px; 
        }

        .card-date { 
            font-size: 12px; 
            color: #888; 
            margin-bottom: 10px; 
        }

        .card-buttons {
            display: flex;
            gap: 5px;
            margin-top: auto;
        }

        .update-btn, .delete-btn {
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

        /* Post Announcement Form */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }

        .form-card h3 {
            font-size: 20px;
            color: #1f9158;
            margin-bottom: 15px;
        }

        .form-card .form-label { 
            font-weight: 500; 
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

        @media (max-width: 768px) {
            .container { 
                padding: 20px; 
            }
            .status-cards-wrapper { 
                grid-template-columns: 1fr; 
            }
        }
    </style>
</head>

<body>

    <div class="navbar">
        <h2>Announcements</h2>
        <button class="return-btn" onclick="window.location.href='admindb.php'">Return</button>
    </div>

    <div class="container">

        <!-- Recent Announcements Cards -->
        <div class="status-cards-wrapper">
            <?php
            if ($announcementsResult && mysqli_num_rows($announcementsResult) > 0) {
                while ($a = mysqli_fetch_assoc($announcementsResult)) {
            ?>
                <div class="dashboard-card">
                    <div>
                        <div class="card-icon"><i class="fas fa-bullhorn"></i></div>
                        <div class="card-value"><?php echo htmlspecialchars($a['title']); ?></div>
                        <div class="card-label"><?php echo htmlspecialchars(substr($a['content'], 0, 60)) . '...'; ?></div>
                        <div class="card-date"><?php echo isset($a['created_at']) ? date("M d, Y", strtotime($a['created_at'])) : ''; ?></div>
                    </div>
                    <div class="card-buttons">
                        <button class="update-btn" onclick="window.location.href='announcement_update.php?id=<?php echo $a['AnnouncementID']; ?>'">Update</button>
                        <button class="delete-btn" onclick="if(confirm('Are you sure you want to delete this announcement?')) window.location.href='announcement_delete.php?id=<?php echo $a['AnnouncementID']; ?>'">Delete</button>
                    </div>
                </div>
            <?php
                }
            } else {
                echo '<div class="dashboard-card">No announcements yet</div>';
            }
            ?>
        </div>

        <!-- Post Announcement Form -->
        <div class="form-card">
            <h3>Post New Announcement</h3>
            <form id="announcementForm" action="announcement_process.php" method="POST" novalidate>
                <div class="mb-3">
                    <label for="title" class="form-label">Announcement Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                </div>

                <button type="submit" class="submit-btn">Post Announcement</button>
            </form>
        </div>

    </div>

</body>
</html>
