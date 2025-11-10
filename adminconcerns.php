<?php 
session_start();
include("config.php");

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : $username;
$activePage = "concerns";

$query = "
    SELECT 
        c.ConcernID,
        c.Concern_Title,
        c.Room,
        c.Problem_Type,
        c.Priority,
        c.Concern_Date,
        c.Status,
        a.Name AS ReportedBy,
        c.Assigned_to
    FROM Concerns c
    LEFT JOIN Accounts a ON c.AccountID = a.AccountID
    ORDER BY c.ConcernID ASC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concerns</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            background: #f9fafb;
            overflow-x: hidden;
        }

        /* Navbar styling */
        .navbar {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #087830, #3c4142);
            padding: 12px 15px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            position: relative;
            width: 100%;
            box-sizing: border-box;
        }

        /* Logo */
        .logo {
            display: flex;
            align-items: center;
            margin-right: 15px; 
        }

        .logo img {
            height: 35px; 
            width: auto; 
            object-fit: contain;
        }

        /* Navbar links */
        .navbar .links {
            display: flex;
            gap: 12px;
            margin-right: auto;
        }

        .navbar .links a {
            color: white; 
            text-decoration: none;
            font-weight: bold; 
            font-size: 14px;
            padding: 8px 12px; 
            border-radius: 5px;
            transition: all 0.3s ease;
            min-height: 44px;
            display: flex;
            align-items: center;
        }

        .navbar .links a.active {
            background: #4ba06f;
            border: 1px solid #07491f;
            box-shadow: 0 4px 6px rgba(0,0,0,0.4);
            color: white;
        }

        .navbar .links a:hover {
            background: #107040;
            color: white;
        }

        .navbar .links a i {
            margin-right: 5px;
        }

        .dropdown {
            position: relative;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 180px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-radius: 5px;
            overflow: hidden;
            z-index: 10;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        .dropdown .username-btn {
            color: white !important;
            background: none !important;
            border: none !important;
            font-weight: bold;
            font-size: 16px;
        }

        .dropdown .username-btn:hover,
        .dropdown .username-btn:focus {
            color: white !important;
            background: none !important;
            border: none !important;
        }

        .table thead {
            background: #198754;
            color: white;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 30px 40px 10px 40px;
        }

        .page-header h3 {
            color: #198754;
            font-weight: bold;
        }

        .realtime-clock {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
        }

        .table td,
        .table th {
            padding: 5px 8px;
        }

        .table th:nth-last-child(1),
        .table td:nth-last-child(1) {
            width: 250px;
            text-align: center;
        }

        .table-container {
            margin: 0 40px 40px 40px;
        }

        .assign-btn {
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            width: 150px;
            transition: 0.3s;
            text-align: center;
        }

        .assign-btn.unassigned {
            background-color: #ff0000;
            color: white;
        }

        .assign-btn.assigned {
            background-color: #198754;
            color: #212529;
        }

        .assign-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <div class="logo">
            <img src="img/LSULogo.png" alt="LSU Logo">
        </div>

        <div class="links">
            <a href="admindb.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <a href="adminconcerns.php" class="<?php echo ($activePage == 'concerns') ? 'active' : ''; ?>">
                <i class="fas fa-list-ul me-1"></i> Concerns
            </a>
            <a href="adminreports.php" class="<?php echo ($activePage == 'reports') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="adminfeedback.php" class="<?php echo ($activePage == 'feedback') ? 'active' : ''; ?>">
                <i class="fas fa-comment-alt"></i> Feedback
            </a>
            <a href="adminannouncement.php" class="<?php echo ($activePage == 'announcements') ? 'active' : ''; ?>">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
        </div>

        <!-- User dropdown -->
        <div class="dropdown ms-auto">
            <button class="btn dropdown-toggle username-btn" aria-expanded="false" aria-haspopup="true">
                <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($name) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fas fa-key me-2"></i>Change Password
                </a></li>
                <li><a class="dropdown-item" href="login.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a></li>
            </ul>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#1f9158; color:white;">
            <h5 class="modal-title" id="changePasswordLabel">
                <i class="fas fa-key me-2"></i>Change Password
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="changePasswordForm">
              <div class="mb-3">
                <label for="currentPassword" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" required>
              </div>
              <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" required>
              </div>
              <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" id="savePasswordBtn">
                <i class="fas fa-save me-1"></i>Change Password
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="page-header">
        <h3><i class="fas fa-list-ul me-2"></i>All Concerns</h3>
        <div class="realtime-clock" id="currentDateTime">
            <i class="fas fa-clock me-2"></i><span id="datetime"></span>
        </div>
    </div>

    <div class="table-container mx-4">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 text-center">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Room</th>
                        <th>Problem Type</th>
                        <th>Priority</th>
                        <th>Concern Date</th>
                        <th>Status</th>
                        <th>Reported By</th>
                        <th>Assigned To</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_assoc($result)): 
                        $statusClass = '';
                        switch ($row['Status']) {
                            case 'Completed':
                                $statusClass = 'bg-success text-white'; // Green
                                break;
                            case 'In Progress':
                                $statusClass = 'bg-primary text-white'; // Blue
                                break;
                            case 'Pending':
                                $statusClass = 'bg-warning text-dark'; // Yellow
                                break;
                            case 'Cancelled':
                                $statusClass = 'bg-danger text-white'; // Red
                                break;
                            default:
                                $statusClass = 'bg-secondary text-white';
                        }

                        $assignedName = trim($row['Assigned_to']);
                        if (empty($assignedName)) {
                            $buttonText = "Assign";
                            $buttonClass = "assign-btn unassigned";
                        } else {
                            $buttonText = htmlspecialchars($assignedName);
                            $buttonClass = "assign-btn assigned";
                        }
                    ?>
                    <tr>
                        <td><?php echo $row['ConcernID']; ?></td>
                        <td><?php echo htmlspecialchars($row['Concern_Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['Room']); ?></td>
                        <td><?php echo htmlspecialchars($row['Problem_Type']); ?></td>
                        <td><?php echo htmlspecialchars($row['Priority']); ?></td>
                        <td><?php echo htmlspecialchars($row['Concern_Date']); ?></td>
                        <td>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($row['Status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['ReportedBy']); ?></td>
                        <td>
                            <button 
                                class="<?php echo $buttonClass; ?>" 
                                onclick="window.location.href='view_concern.php?id=<?php echo $row['ConcernID']; ?>'">
                                <?php echo $buttonText; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            };
            document.getElementById('datetime').textContent = now.toLocaleString('en-US', options);
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();

        document.getElementById('savePasswordBtn').addEventListener('click', () => {
            const currentPassword = document.getElementById('currentPassword').value.trim();
            const newPassword = document.getElementById('newPassword').value.trim();
            const confirmPassword = document.getElementById('confirmPassword').value.trim();

            if (!currentPassword || !newPassword || !confirmPassword) {
                Swal.fire('Error', 'Please fill in all fields.', 'error');
                return;
            }

            if (newPassword !== confirmPassword) {
                Swal.fire('Error', 'New password and confirmation do not match.', 'error');
                return;
            }

            fetch('change_password.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({currentPassword, newPassword})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    Swal.fire('Success', data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                    document.getElementById('changePasswordForm').reset();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Something went wrong.', 'error'));
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</body>
</html>