<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Profile</title>
    <style>
body {
    background: url('profile background.jpg') no-repeat center center fixed;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    font-family: Arial, sans-serif;
    color: #333;
    margin: 0;
    padding: 0;
}

header {
    text-align: center;
    padding: 20px;
    background-color:transparent;
}

header h1 {
    margin: 0;
    color: #03045e;
}

.menu {
    position: absolute;
    top: 30px;
    right: 20px;
}

.menu a {
    color: #ffffff;
    background-color: #03045E;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}

.menu a:hover {
    background-color: rgba(86, 163, 216, 0.8);
}

.form-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: rgba(255, 255, 255, 0.6);
    border-radius: 10px;
    text-align: left;
    color: #333;
    border: 2px solid #0077b6;
}

.form-group {
    display: flex;
    align-items: center; 
    justify-content: space-between;
    margin-bottom: 15px;
    text-align: left;
    gap: 10px;
}

.form-group label {
    flex: 1; 
    font-weight: bold;
    color: #1a75a1;
    margin-bottom: 0; 
}

.form-group input,
.form-group select {
    flex: 2;
    padding: 8px;
    border: 2px solid #0077b6;
    border-radius: 5px;
    color: #005c80;
    background-color: transparent;
    width: auto;
}


button {
    width: 30%;
    padding: 10px;
    background-color: #0077B6;
    color: #ffffff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

button:hover {
    background-color: rgba(86, 163, 216, 0.8);
}

.specialization-container {
    display: none;
    padding: 15px;
    background-color: rgba(255, 255, 255, 0.6);
    border: 2px solid #0077b6; 
    border-radius: 10px;
    margin-top: 20px;
    text-align: left;
}

.specialization-container h3 {
    margin: 0;
    margin-bottom: 10px;
    color: #1a75a1;
    font-size: 18px;
    font-weight: bold;
}

.charge-input {
    margin-left: 10px;
    padding: 5px;
    border: 2px solid #0077b6; 
    border-radius: 5px;
    width: 80px;
}

.overlay-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #ffffff;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    padding: 10px 20px;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 8px;
}

</style>
</head>
<body>
    <header>
        <h1>Create Service profile</h1>
        <div class="menu">
            <a href="menu.html">Main Menu</a>
        </div>
    </header>

    <div class="form-container">
        <form method="POST" action="">
	<h2 align=center></h2>
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="form-group">
                <label for="service_name">Service Name:</label>
                <select id="service_name" name="service_name" required onchange="fetchSpecializations()">
                    <option value="">Select a service</option>
                    <?php
                    // Database connection
                    $conn = new mysqli('localhost', 'root', '', 'services');

                    // Check for connection error
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Fetch available services from the available_services table
                    $result = $conn->query("SELECT id, name FROM available_services");

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No service available</option>";
                    }

                    $conn->close();
                    ?>
                </select>
            </div>

            <div class="specialization-container" id="specialization-container">
                <h3>Specializations</h3>
                <div id="specialization-list"></div>
            </div>

            <div class="form-group">
                <label for="business">Business (optional):</label>
                <input type="text" id="business" name="business">
            </div>

            <div class="form-group">
                <label for="mobile_number">Mobile Number:</label>
                <input type="text" id="mobile_number" name="mobile_number" placeholder="e.g., 9876543210" required pattern="^[789]\d{9}$" title="Please enter a valid 10-digit Indian mobile number starting with 7, 8, or 9.">
            </div>

            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required>
            </div>

            <div class="form-group">
                <label for="experience">Experience (in years):</label>
                <input type="number" id="experience" name="experience" min="0" required>
            </div>

           <div class="form-group">
                <label for="availability">Availability Timings:</label>
                <input type="text" id="availability" name="availability" placeholder="e.g., 9:00 AM - 5:00 PM"  title="Please enter the time in the format '9:00 AM - 5:00 PM'">
            </div>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div style="text-align: center;">
                <button type="submit">Create Profile</button>
            </div>
        </form>
    </div>

<script>
function fetchSpecializations() {
    const serviceId = document.getElementById('service_name').value;

    if (!serviceId) {
        document.getElementById('specialization-container').style.display = 'none';
        return;
    }

    fetch('get_specializations.php?service_id=' + serviceId)
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch specializations');
            return response.json();
        })
        .then(data => {
            const specializationContainer = document.getElementById('specialization-container');
            const specializationList = document.getElementById('specialization-list');

            specializationList.innerHTML = '';
            data.forEach(specialization => {
                const div = document.createElement('div');
                div.classList.add('specialization-group');
                div.innerHTML = `
                    <label for="specialization_${specialization.id}">
                        <input type="checkbox" name="specializations[]" value="${specialization.id}" id="specialization_${specialization.id}">
                        ${specialization.name}
                    </label>
                    <input type="text" class="charge-input" name="charges[${specialization.id}]" placeholder="Enter charge">
                `;
                specializationList.appendChild(div);
            });

            specializationContainer.style.display = 'block';
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php
$conn = new mysqli('localhost', 'root', '', 'services');

if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = $conn->real_escape_string($_POST['full_name']);
    $serviceTypeId = (int) $_POST['service_name']; // `service_name` refers to `available_services` ID
    $business = $conn->real_escape_string($_POST['business']);
    $mobileNumber = $conn->real_escape_string($_POST['mobile_number']);
    $location = $conn->real_escape_string($_POST['location']);
    $experience = (int) $_POST['experience'];
    $availability = $conn->real_escape_string($_POST['availability']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if the username already exists in the `login` table
    $stmt = $conn->prepare("SELECT COUNT(*) FROM login WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($usernameCount);
    $stmt->fetch();
    $stmt->close();

    if ($usernameCount > 0) {
        echo "<p style='color: red;'>Error: Username already exists. Please choose a different username.</p>";
    } else {
        // Insert data into the `services` table
        $stmt = $conn->prepare("INSERT INTO services (full_name, service_id, business, mobile_number, location, experience, availability) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssis", $fullName, $serviceTypeId, $business, $mobileNumber, $location, $experience, $availability);

        if ($stmt->execute()) {
            $newServiceId = $stmt->insert_id; // Get the new ID generated in the `services` table
            $stmt->close();

            // Use `newServiceId` for login and specialization_charges to maintain foreign key integrity
            $stmt = $conn->prepare("INSERT INTO login (username, password, service_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $username, $password, $newServiceId); // Corrected to use `newServiceId`
            if ($stmt->execute()) {
                echo "<p>Profile created successfully!</p>";
            } else {
                echo "<p>Error: " . htmlspecialchars($stmt->error) . "</p>";
            }
            $stmt->close();

            // Insert into `specialization_charges` using `newServiceId`
            if (isset($_POST['specializations']) && isset($_POST['charges'])) {
                $specializations = $_POST['specializations'];
                $charges = $_POST['charges'];

                foreach ($specializations as $specializationId) {
                    $charge = $conn->real_escape_string($charges[$specializationId]);
                    $stmt = $conn->prepare("INSERT INTO specialization_charges (service_id, specialization_id, charge) VALUES (?, ?, ?)");
                    $stmt->bind_param("iis", $newServiceId, $specializationId, $charge); // Use `newServiceId`
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } else {
            echo "<p>Error: " . htmlspecialchars($stmt->error) . "</p>";
        }
    }

    $conn->close();
}
?>


</body>
</html>
