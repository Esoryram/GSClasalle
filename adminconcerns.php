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

    <style>
        body {
            background: white;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .navbar {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
            padding: 15px 30px;
            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-right: 25px;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .navbar .links {
            display: flex;
            gap: 20px;
            margin-right: auto;
        }

        .navbar .links a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .navbar .links a.active {
            background: #4ba06f;
        }

        .navbar .links a:hover {
            background: #107040;
        }

        .dropdown {
            position: relative;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .dropdown .username {
            font-weight: bold;
            font-size: 16px;
            padding: 6px 12px;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
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

        .dropdown-menu a {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        .dropdown-menu a:hover {
            background: #f1f1f1;
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

        .concern-row:hover {
            background-color: #e9ecef;
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
            background-color: #198754;
            color: white;
        }

        .assign-btn.assigned {
            background-color: #ffc107;
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
            <a href="admindb.php" class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
            <a href="adminconcerns.php" class="<?php echo ($activePage == 'concerns') ? 'active' : ''; ?>">Concerns</a>
            <a href="adminreports.php" class="<?php echo ($activePage == 'reports') ? 'active' : ''; ?>">Reports</a>
            <a href="adminfeedback.php" class="<?php echo ($activePage == 'feedback') ? 'active' : ''; ?>">Feedback</a>
            <a href="adminannouncement.php" class="<?php echo ($activePage == 'announcements') ? 'active' : ''; ?>">Announcements</a>
        </div>

        <div class="dropdown">
            <span class="username"><?php echo htmlspecialchars($name); ?></span>
            <span class="dropdown-toggle">
                <div class="dropdown-menu">
                    <a href="#" id="changePasswordLink">Change Password</a>
                    <a href="login.php">Logout</a>
                </div>
            </span>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#1f9158; color:white;">
            <h5 class="modal-title" id="changePasswordLabel">Change Password</h5>
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
            <button type="button" class="btn btn-success" id="savePasswordBtn">Change Password</button>
          </div>
        </div>
      </div>
    </div>

    <div class="page-header">
        <h3>All Concerns</h3>
        <div class="realtime-clock" id="currentDateTime"></div>
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
                                $statusClass = 'bg-success text-white';
                                break;
                            case 'In Progress':
                                $statusClass = 'bg-warning text-dark';
                                break;
                            case 'Pending':
                                $statusClass = 'bg-danger text-white';
                                break;
                            case 'Cancelled':
                                $statusClass = 'bg-secondary text-white';
                                break;
                            default:
                                $statusClass = 'bg-info text-white';
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
            document.getElementById('currentDateTime').textContent = now.toLocaleString('en-US', options);
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
    // Open modal when clicking "Change Password"
    document.getElementById('changePasswordLink').addEventListener('click', e => {
        e.preventDefault();
        new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
    });

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

</body>
</html>
