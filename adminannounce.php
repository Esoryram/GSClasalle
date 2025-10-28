<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$returnPage = $_GET['return'] ?? basename($_SERVER['HTTP_REFERER']) ?? 'admindashboard.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post New Announcement</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { margin: 0; font-family: Arial, sans-serif; background: #f4f4f4; }
.navbar .logo { display: flex; align-items: center; gap: 20px; }
.navbar .logo img { height: 40px; width: auto; object-fit: contain; }
.navbar .logo h2 { margin: 0; font-size: 22px; }
.navbar { display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158); padding: 15px 30px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
.return-btn { background: #107040; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-left: auto; }
.return-btn:hover { background: #07532e; }
.date-display { text-align: right; font-weight: bold; margin: 10px auto 0 auto; width: 95%; }
.date-display span { background: white; border: 1px solid #ccc; padding: 5px 10px; border-radius: 3px; }
.form-card { background: white; border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 35px; margin: 0px auto; max-width: 850px; width: 100%; min-width: 400px; box-sizing: border-box; }
.submit-btn { width: 100%; padding: 10px; background: linear-gradient(90deg, #0c3c2f, #116546); color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 10px; }
.submit-btn:hover { background: #07532e; }
.form-label { font-weight: bold; }
.custom-select { position: relative; user-select: none; width: 100%; }
.custom-select .select-selected { background-color: white; border: 1px solid #ccc; border-radius: 4px; padding: 6px 10px; min-height: 28px; display: flex; align-items: center; font-size: 15px; box-sizing: border-box; cursor: pointer; }
.custom-select .select-selected.placeholder { color: gray; font-style: italic; }
.custom-select .select-items { position: absolute; background-color: white; border: 1px solid #ccc; border-top: none; border-radius: 0 0 4px 4px; z-index: 99; width: 100%; max-height: 100px; overflow-y: auto; display: none; }
.custom-select .select-items div { padding: 6px 10px; cursor: pointer; }
.custom-select .select-items div:hover { background-color: #f1f1f1; }
.other-container { overflow: hidden; height: 0; transition: height 0.2s ease; }
.other-container input { border: 1px solid #ccc; border-radius: 4px; padding: 6px 10px; font-size: 16px; width: 100%; box-sizing: border-box; height: 28px; margin-top: 5px; }
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Post New Announcement</h2>
    </div>
    <button class="return-btn" onclick="window.location.href='<?= htmlspecialchars($returnPage) ?>'">
        Return
    </button>
</div>

<div class="date-display">
    <span id="currentDateTime"></span>
</div>

<div class="container">
    <div class="form-card">
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
                    <label class="form-label">Audience</label>
                    <div class="custom-select" id="audienceSelect">
                        <div class="select-selected placeholder">Select audience</div>
                        <div class="select-items">
                            <div data-value="all">All</div>
                            <div data-value="students">Students</div>
                            <div data-value="faculty">Faculty</div>
                            <div data-value="staff">Staff</div>
                        </div>
                    </div>
                    <input type="hidden" name="audience" id="audienceInput" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="priority" class="form-label">Priority</label>
                    <div class="custom-select" id="prioritySelect">
                        <div class="select-selected placeholder">Select priority</div>
                        <div class="select-items">
                            <div data-value="Low">Low</div>
                            <div data-value="Medium">Medium</div>
                            <div data-value="High">High</div>
                        </div>
                    </div>
                    <input type="hidden" name="priority" id="priorityInput" required>
                </div>
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

<script>
function updateDateTime() {
    const now = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', options);
}
updateDateTime();
setInterval(updateDateTime, 1000);

function initCustomSelect(selectId, hiddenInputId) {
    const select = document.getElementById(selectId);
    const selected = select.querySelector('.select-selected');
    const items = select.querySelector('.select-items');
    const hiddenInput = document.getElementById(hiddenInputId);

    selected.addEventListener('click', () => {
        items.style.display = items.style.display === 'block' ? 'none' : 'block';
    });

    items.querySelectorAll('div').forEach(option => {
        option.addEventListener('click', () => {
            selected.textContent = option.textContent;
            selected.classList.remove('placeholder');
            hiddenInput.value = option.dataset.value;
            items.style.display = 'none';
        });
    });

    document.addEventListener('click', function(e) {
        if (!select.contains(e.target)) items.style.display = 'none';
    });
}

initCustomSelect('audienceSelect', 'audienceInput');
initCustomSelect('prioritySelect', 'priorityInput');

document.getElementById('announcementForm').addEventListener('submit', function(e) {
    let valid = true;
    ['title','content','audienceInput','priorityInput','start_date'].forEach(id => {
        const input = document.getElementById(id);
        if (!input.value) {
            valid = false;
            input.style.border = '2px solid red';
        } else input.style.border = '1px solid #ccc';
    });
    if (!valid) {
        e.preventDefault();
        alert('Please fill out all required fields before posting.');
    }
});
</script>

</body>
</html>
