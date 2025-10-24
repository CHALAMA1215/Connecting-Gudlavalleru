<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'services');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['service_id'])) {
    header("Location: login.php"); // Redirect to login if service_id is not set
    exit();
}

$service_id = $_SESSION['service_id']; // The logged-in user's service_id

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $customer_phone = $_POST['customer_phone'];

    // Prepare the status message
    $message = ($status === 'accepted') ? "Booking Accepted." : "Booking Rejected.";

    // Mock SMS sending function (replace with actual SMS API call)
    if (sendSms($customer_phone, $message)) {
        $popup_message = $message;
    } else {
        $popup_message = "Failed to send message.";
    }
}

// Fetch bookings for the logged-in user without the service name
$sql = "SELECT b.id AS booking_id, b.service_id, b.customer_name, b.customer_phone, b.Service_date 
        FROM bookings b 
        WHERE b.service_id = ? AND b.Service_date >= CURDATE()";

// Prepare and execute the query
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $service_id); // Bind the service_id from the session
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $bookings = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        echo "Error executing query: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error preparing the query: " . $conn->error;
}

$conn->close();

function sendSms($phone, $message) {
    // Placeholder for actual SMS API integration
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
    <style>
         body {
            background: url('bookings.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            position: relative;
            text-align: center;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0); 
        }

        header h1 {
            margin: 0;
            color: #03045E;
            font-size: 2.5em;
        }

        .menu {
            position: absolute;
            top: 30px;
            right: 20px;
        }

        .menu a {
            color: #caf0f8;
            background-color: #03045E;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .menu a:hover {
            background-color: #90e0ef;
        }

        .container {
            max-width: 70%; 
            margin: 30px auto;
            padding: 20px;
            background-color: rgba(144, 224, 239, 0.8);
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .container h2 {
            color: #03045e;
            margin-top: 0;
            font-size: 2em;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #0077b6;
        }

        th, td {
            padding: 15px;
            text-align: center;
            font-size: 1em;
        }

        th {
            background-color: #0077b6;
            color: #caf0f8;
        }

        td {
            background-color: #caf0f8;
            color: #03045e;
        }

        .logout {
            color: #0077b6;
            display: block;
            margin-top: 20px;
            font-weight: bold;
            text-decoration: none;
            font-size: 1.2em;
        }

        .logout:hover {
            color: #03045e;
        }

        button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            color: #ffffff;
            cursor: pointer;
        }

        button[type="submit"][value="accepted"] {
            background-color: #0077b6;
        }

        button[type="submit"][value="accepted"]:hover {
            background-color: #005f99;
        }

        button[type="submit"][value="rejected"] {
            background-color: #d9534f;
        }

        button[type="submit"][value="rejected"]:hover {
            background-color: #c9302c;
        }

        /* Popup Styles */
        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            padding: 30px;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            border-radius: 10px;
            z-index: 1000;
            text-align: center;
        }

        .popup button {
            background-color: #0077b6;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        .popup button:hover {
            background-color: #005f99;
        }
    </style>
</head>
<body>
<header>
    <h1>BOOKINGS</h1>
    <div class="menu">
        <a href="menu.html">Main Menu</a>
    </div>
</header>

<div class="container">
    <h2>Manage Your Bookings</h2>

    <?php if (isset($popup_message)): ?>
        <div class="popup" id="popupMessage">
            <p><?php echo $popup_message; ?></p>
            <button onclick="closePopup()">OK</button>
        </div>
    <?php endif; ?>

    <?php if (count($bookings) > 0): ?>
        <table>
            <tr>
                <th>Booking Name</th>
                <th>Phone</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['customer_phone']); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($booking['Service_date'])); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                            <input type="hidden" name="customer_phone" value="<?php echo htmlspecialchars($booking['customer_phone']); ?>">
                            <button type="submit" name="status" value="accepted">Accept</button>
                            <button type="submit" name="status" value="rejected">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No bookings found.</p>
    <?php endif; ?>

    <a href="view_profile.php" class="logout">Back</a>
</div>

<script>
    function closePopup() {
        document.getElementById("popupMessage").style.display = 'none';
    }

    // Show popup if message is set
    <?php if (isset($popup_message)): ?>
        document.getElementById("popupMessage").style.display = 'block';
    <?php endif; ?>
</script>
</body>
</html>
