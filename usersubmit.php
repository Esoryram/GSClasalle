<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['return'])) {
    $returnPage = $_GET['return'];
} else {
    $referrer = basename($_SERVER['HTTP_REFERER']);

    if ($referrer === 'user_archived.php') {
        $returnPage = 'user_archived.php';
    } elseif ($referrer === 'userdb.php') {
        $returnPage = 'userdb.php';
    } else {
        $returnPage = 'userconcerns.php';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submit New Concerns</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f4f4;
}

.navbar .logo {
    display: flex;
    align-items: center;
    gap: 20px; 
}
.navbar .logo img {
    height: 40px;
    width: auto;
    object-fit: contain;
}

.navbar .logo h2 {
    margin: 0;
    font-size: 22px;
}
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #163a37, #1c4440, #275850, #1f9158);
    padding: 15px 30px;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
}
.navbar h2 { margin: 0; font-size: 20px; }
.return-btn {
    background: #107040;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-left: auto;
}
.return-btn:hover { background: #07532e; }

.date-display {
    text-align: right;
    font-weight: bold;
    margin: 10px auto 0 auto;
    width: 95%;
}
.date-display span {
    background: white;
    border: 1px solid #ccc;
    padding: 5px 10px;
    border-radius: 3px;
}

.form-card {
    background: white;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    padding: 35px;
    margin: 0px auto;
    max-width: 850px;
    width: 100%;
    min-width: 400px;
    box-sizing: border-box;
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
    margin-top: 10px;
}
.submit-btn:hover { background: #07532e; }

.form-label { font-weight: bold; }

.custom-select {
    position: relative;
    user-select: none;
    width: 100%;
}
.custom-select .select-selected {
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 6px 10px;
    min-height: 28px;
    display: flex;
    align-items: center;
    font-size: 15px;
    box-sizing: border-box;
    cursor: pointer;
}
.custom-select .select-selected.placeholder {
    color: gray;
    font-style: italic;
}
.custom-select .select-items {
    position: absolute;
    background-color: white;
    border: 1px solid #ccc;
    border-top: none;
    border-radius: 0 0 4px 4px;
    z-index: 99;
    width: 100%;
    max-height: 100px;
    overflow-y: auto;
    display: none;
}
.custom-select .select-items div {
    padding: 6px 10px;
    cursor: pointer;
}
.custom-select .select-items div:hover {
    background-color: #f1f1f1;
}

.other-container {
    overflow: hidden;
    height: 0;
    transition: height 0.2s ease;
}
.other-container input {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 16px;
    width: 100%;
    box-sizing: border-box;
    height: 28px;
    margin-top: 5px;
}
</style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <img src="img/LSULogo.png" alt="LSU Logo">
        <h2>Submit New Concerns</h2>
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
        <form id="concernForm" action="usersubmit_process.php" method="POST" enctype="multipart/form-data" novalidate>
            
            <div class="mb-3">
                <label for="title" class="form-label">Concern Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Room</label>
                    <div class="custom-select" id="roomSelect">
                        <div class="select-selected placeholder">Select a room</div>
                        <div class="select-items">
                            <div data-value="LS 211">LS 211</div>
                            <div data-value="LS 212">LS 212</div>
                            <div data-value="LS 213">LS 213</div>
                            <div data-value="SB 311">SB 311</div>
                            <div data-value="SB 312">SB 312</div>
                            <div data-value="SB 313">SB 313</div>
                            <div data-value="Other">Other</div>
                        </div>
                    </div>
                    <input type="hidden" name="room" id="roomInput" required>
                    <div class="other-container" id="otherRoomContainer">
                        <input type="text" id="other_room" name="other_room" placeholder="Enter room name">
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Equipment / Facility</label>
                    <div class="custom-select" id="equipmentSelect">
                        <div class="select-selected placeholder">Select equipment/facility</div>
                        <div class="select-items">
                            <div data-value="Air Conditioner">Air Conditioner</div>
                            <div data-value="Electric Fan">Electric Fan</div>
                            <div data-value="Chair">Chair</div>
                            <div data-value="Lights">Lights</div>
                            <div data-value="Outlet">Outlet</div>
                            <div data-value="Other">Other</div>
                        </div>
                    </div>
                    <input type="hidden" name="equipment" id="equipmentInput" required>
                    <div class="other-container" id="otherEquipmentContainer">
                        <input type="text" id="other_equipment" name="other_equipment" placeholder="Enter equipment/facility name">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Problem Type</label>
                    <div class="custom-select" id="problemSelect">
                        <div class="select-selected placeholder">Select problem type</div>
                        <div class="select-items">
                            <div data-value="Carpentry">Carpentry</div>
                            <div data-value="Masonry">Masonry</div>
                            <div data-value="Electrical">Electrical</div>
                            <div data-value="Plumbing">Plumbing</div>
                            <div data-value="Painting">Painting</div>
                            <div data-value="Fabrication">Fabrication</div>
                            <div data-value="Appliance Repair/Installation">Appliance Repair/Installation</div>
                        </div>
                    </div>
                    <input type="hidden" name="problem_type" id="problemInput" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Priority</label>
                    <div class="custom-select" id="prioritySelect">
                        <div class="select-selected placeholder">Select priority</div>
                        <div class="select-items">
                            <div data-value="Low">Low</div>
                            <div data-value="Medium">Medium</div>
                            <div data-value="High">High</div>
                            <div data-value="Critical">Critical</div>
                        </div>
                    </div>
                    <input type="hidden" name="priority" id="priorityInput" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="attachment" class="form-label">Attachment (Photo/Video)</label>
                <input type="file" class="form-control" id="attachment" name="attachment" accept=".jpg,.png,.gif,.mp4,.mov" required>
                <small class="text-muted">Max file size: 5MB. Allowed types: JPG, PNG, GIF, MP4, MOV.</small>
            </div>

            <button type="submit" class="submit-btn">Submit Concern</button>
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

function initCustomSelect(selectId, hiddenInputId, otherContainerId) {
    const select = document.getElementById(selectId);
    const selected = select.querySelector('.select-selected');
    const items = select.querySelector('.select-items');
    const hiddenInput = document.getElementById(hiddenInputId);
    const otherContainer = otherContainerId ? document.getElementById(otherContainerId) : null;

    selected.addEventListener('click', () => {
        items.style.display = items.style.display === 'block' ? 'none' : 'block';
    });

    const options = items.querySelectorAll('div');
    options.forEach(option => {
        option.addEventListener('click', () => {
            selected.textContent = option.textContent;
            selected.classList.remove('placeholder');
            hiddenInput.value = option.dataset.value;
            items.style.display = 'none';

            if (option.dataset.value === 'Other' && otherContainer) {
                otherContainer.style.height = '38px';
                otherContainer.querySelector('input').required = true;
            } else if (otherContainer) {
                otherContainer.style.height = '0';
                const input = otherContainer.querySelector('input');
                input.required = false;
                input.value = '';
            }
        });
    });

    document.addEventListener('click', function(e) {
        if (!select.contains(e.target)) items.style.display = 'none';
    });
}

initCustomSelect('roomSelect', 'roomInput', 'otherRoomContainer');
initCustomSelect('equipmentSelect', 'equipmentInput', 'otherEquipmentContainer');
initCustomSelect('problemSelect', 'problemInput');
initCustomSelect('prioritySelect', 'priorityInput');

const form = document.getElementById('concernForm');
form.addEventListener('submit', function(e) {
    let valid = true;

    // Title & Description check
    ['title','description','attachment'].forEach(id => {
        const input = document.getElementById(id);
        if (!input.value) {
            valid = false;
            input.style.border = '2px solid red';
        } else input.style.border = '1px solid #ccc';
    });

    // Dropdowns check
    ['roomInput','equipmentInput','problemInput','priorityInput'].forEach(id => {
        const input = document.getElementById(id);
        if (!input.value) {
            valid = false;
            const customSelect = input.previousElementSibling || input.parentElement.querySelector('.select-selected');
            customSelect.style.border = '2px solid red';
            customSelect.style.borderRadius = '5px';
        } else {
            const customSelect = input.previousElementSibling || input.parentElement.querySelector('.select-selected');
            customSelect.style.border = '1px solid #ccc';
            customSelect.style.borderRadius = '5px';
        }
    });

    if (!valid) {
        e.preventDefault();
        alert('Please fill out all required fields before submitting.');
    }
});
</script>

</body>
</html>
