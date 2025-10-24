<?php
// Start session at the top of the file
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'services');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['service_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$updateMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['service_id'])) {
    $serviceId = $_SESSION['service_id'];
    $fullName = $_POST['full_name'];
    $serviceIdPost = $_POST['service_id'];
    $business = $_POST['business'];
    $mobileNumber = $_POST['mobile_number'];
    $location = $_POST['location'];
    $experience = $_POST['experience'];
    $availability = $_POST['availability'];

    $stmt = $conn->prepare("UPDATE services SET full_name = ?, service_id = ?, business = ?, mobile_number = ?, location = ?, experience = ?, availability = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $fullName, $serviceIdPost, $business, $mobileNumber, $location, $experience, $availability, $serviceId);

    if ($stmt->execute()) {
        $updateMessage = "Profile updated successfully.";
    } else {
        $updateMessage = "Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch profile data
$serviceId = $_SESSION['service_id'];
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->bind_param("i", $serviceId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

$servicesResult = $conn->query("SELECT * FROM available_services");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
body {
            background-image: url('view profile.jpg');
            background-size: cover;
            background-attachment: fixed;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            position: relative;
            text-align: center;
            padding: 20px;
            background-color: transparent; 
        }
        header h1 {
            margin: 0;
            color: #03045E;
            font-size: 2em;
        }
        .menu {
            position: absolute;
            top: 30px;
            right: 20px;
        }
        .menu a {
            color: #caf0f8;
            background-color: #03045e;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.1em;
        }
        .menu a:hover {
            background-color: #0077b6;
        }
        .login-box {
            width: 40%; 
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: rgba(144, 224, 239, 0.7); 
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #0077b6; 
        }
        .login-box label {
            display: block;
            font-weight: bold;
            color: #0077b6;
            margin-top: 10px;
            font-size: 1.1em;
        }
        .login-box input[type="text"],
        .login-box select {
            width: 90%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #03045e;
            font-size: 1em;
        }
.button-container {
    display: flex;
    justify-content: center;
    gap: 20px; 
    margin-top: 15px;
}
.login-box button,
.button-back {
    padding: 10px 20px;
    background-color: #03045e;
    color: white;
    font-size: 1.1em;
    text-align: center;
    border-radius: 5px;
    border: 2px solid #00b4d8;
    cursor: pointer;
    width: 30%; 
}

.login-box button:hover {
    background-color: #0077b6;
    border-color: #cc0000;
}

.button-back:hover {
    background-color: #e60000;
    border-color: #cc0000;
}
.popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: rgba(0, 128, 128, 0.8);
            color: white;
            border-radius: 10px;
            text-align: center;
            width: 50%;
            max-width: 400px;
            z-index: 1000;
        }
        .popup button {
            padding: 10px 20px;
            background-color: #0077b6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .popup button:hover {
            background-color: #005f7f;
        }
    </style>
</head>
<body>
    <header>
        <h1>Customize Your Profile</h1>
        <div class="menu">
            <a href="menu.html">Main Menu</a>
        </div>
    </header>

    <div class="login-box">
        <form method="POST" action="">
            <label>Full Name:</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($profile['full_name']); ?>"><br>

            <label>Service Name:</label>
            <select name="service_id" id="service_name" onchange="fetchSpecializations()">
                <?php while ($row = $servicesResult->fetch_assoc()) { ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $profile['service_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </option>
                <?php } ?>
            </select><br>

            <div id="specialization-container" style="display:none;">
                <label>Specializations:</label>
                <div id="specialization-list"></div>
            </div><br>

            <label>Business:</label>
            <input type="text" name="business" value="<?php echo htmlspecialchars($profile['business']); ?>"><br>

            <label>Mobile Number:</label>
            <input type="text" name="mobile_number" value="<?php echo htmlspecialchars($profile['mobile_number']); ?>"><br>

            <label>Location:</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($profile['location']); ?>"><br>

            <label>Experience (in years):</label>
            <input type="text" name="experience" value="<?php echo htmlspecialchars($profile['experience']); ?>"><br>

            <label>Availability Timings:</label>
            <input type="text" name="availability" value="<?php echo htmlspecialchars($profile['availability']); ?>"><br>

            <div class="button-container">
	       <button type="submit">Submit</button>
               <button type="button" class="button-back" onclick="window.location.href='view_profile.php';">Back</button>
                </div>
    			</div>
</form>
    <div class="popup" id="popupMessage">
        <p><?php echo $updateMessage; ?></p>
        <button onclick="closePopup()">OK</button>
    </div>

    <script>
        function fetchSpecializations() {
            const serviceId = document.getElementById('service_name').value;
            if (!serviceId) {
                document.getElementById('specialization-container').style.display = 'none';
                return;
            }

            fetch('get_specializations.php?service_id=' + serviceId)
                .then(response => response.json())
                .then(data => {
                    const specializationContainer = document.getElementById('specialization-container');
                    const specializationList = document.getElementById('specialization-list');
                    specializationList.innerHTML = ''; 
                    
                    data.forEach(specialization => {
                        const div = document.createElement('div');
                        div.innerHTML = `<label>
                            <input type="checkbox" name="specializations[]" value="${specialization.id}">
                            ${specialization.name}
                        </label> 
                        <input type="text" name="charges[${specialization.id}]" placeholder="Enter charge">`;
                        specializationList.appendChild(div);
                    });
                    specializationContainer.style.display = 'block';
                })
                .catch(error => console.error("Error fetching specializations:", error));
        }

        window.onload = function() {
            if (<?php echo !empty($updateMessage) ? 'true' : 'false'; ?>) {
                document.getElementById('popupMessage').style.display = 'block';
            }
        };

        function closePopup() {
            document.getElementById('popupMessage').style.display = 'none';
        }
    </script>
</body>
</html>
