<?php
include 'db.php';

if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($conn, $_GET['query']);
} else {
    $query = '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_service'])) {
    $service_id = mysqli_real_escape_string($conn, $_POST['service_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $sql_booking = "INSERT INTO bookings (service_id, customer_name, customer_phone, service_date) 
                    VALUES ('$service_id', '$name', '$phone', '$date')";

    if ($conn->query($sql_booking) === TRUE) {
        echo "<p style='color: green;'>Booking successful!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }
}


$sql = "
   SELECT 
    s.id AS service_id, 
    s.full_name, 
    s.business, 
    s.mobile_number, 
    s.location, 
    s.experience, 
    s.availability, 
    avs.name AS service_name, 
    -- Fetch specializations for each service
    (SELECT GROUP_CONCAT(sp.name ORDER BY sp.name ASC SEPARATOR ', ') 
     FROM specializations sp 
     WHERE sp.service_id = s.id) AS specialization_names,
    -- Fetch charges associated with each specialization for the service
    (SELECT GROUP_CONCAT(sc.charge ORDER BY sc.specialization_id ASC SEPARATOR ', ') 
     FROM specialization_charges sc 
     JOIN specializations sp ON sp.id = sc.specialization_id 
     WHERE sc.service_id = s.id) AS specialization_charges,
    -- Calculate average rating and number of ratings for the service
    ROUND(AVG(r.rating), 1) AS avg_rating,
    IFNULL(COUNT(r.id), 0) AS total_ratings,
    -- Fetch comments for the service
    (SELECT GROUP_CONCAT(r.comment ORDER BY r.created_at DESC SEPARATOR ' | ') 
     FROM ratings r 
     WHERE r.service_id = s.id) AS comments
FROM 
    services s
JOIN 
    available_services avs ON s.service_id = avs.id
LEFT JOIN 
    ratings r ON s.id = r.service_id
WHERE 
    avs.name LIKE '%$query%'
GROUP BY 
    s.id
ORDER BY 
    avg_rating DESC;
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    
    <script>
        function toggleBookingForm(serviceId) {
            var form = document.getElementById('booking-form-' + serviceId);
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }
    </script>
    <style>
      body {
        background-color: #caf0f8; 
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
        color: #03045e;
      }

      .menu {
        position: absolute;
        top: 30px;
        right: 20px;
      }

      .menu a {
        color: #fefae0;
        background-color: #0077b6; 
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
      }

      .menu a:hover {
        background-color: #00b4d8; 
      }

      .container {
        max-width: 900px;
        margin: 20px auto;
        padding: 20px;
        background-color: #90e0ef; 
        border-radius: 10px;
        border: 2px solid #0077b6; 
      }

      .service-details {
        background-color: #ffffff; 
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        color: #283618;
        border: 2px solid #0077b6; 
        position: relative;
      }

      .rating {
        position: absolute;
        top: 20px;
        right: 20px;
        color: #fefae0; 
        background-color: #0077b6; 
        padding: 5px 10px;
        border-radius: 5px;
      }

      h3 {
        color: #03045e; 
      }

      a {
        color: #0077b6; 
      }

      .booking-form {
        display: none; 
        margin-top: 20px;
        background-color: #fefae0;
        padding: 20px;
        border-radius: 8px;
        border: 2px solid #0077b6; 
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        max-width: 500px; 
        margin-left: auto;
        margin-right: auto;
      }

      .booking-form h2 {
        text-align: center;
        color: #03045e; 
        margin-bottom: 20px;
      }

      .booking-form .form-group {
        margin-bottom: 15px;
      }

      .booking-form .form-group label {
        display: block;
        font-weight: bold;
        color: #03045e; 
      }

      .booking-form .form-group input {
        width: 80%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #0077b6; 
        border-radius: 5px;
        font-size: 16px;
      }

      .booking-form .form-group input:focus {
        outline-color: #00b4d8; 
      }

      .rate-button, .submit-button, .cancel-button {
        padding: 8px 16px;
        background-color: #0077b6; 
        color: #fefae0; 
        border: none;
        border-radius: 5px;
        cursor: pointer;
	 min-width: 120px;
      }

      .buttons-container {
        display: flex;
        justify-content: space-between; 
        gap: 50px;
      }

      .cancel-button {
        background-color: #d4d2cd; 
        color: #03045e; 
      }

      .submit-button:hover, .cancel-button:hover {
        background-color: #00b4d8; 
      }

      .rate-button:hover, .submit-button:hover {
        background-color: #00b4d8; 
      }
    </style>
</head>
<body>
<header>
    <h1>Available Services</h1>
    <div class="menu">
        <a href="menu.html">Main Menu</a>
    </div>
</header>

<div class="container">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='service-details'>";
            echo "<h3 align=center>" . htmlspecialchars($row['full_name']) . "</h3>";
            echo "<p><strong>Service:</strong> " . htmlspecialchars($row['service_name']) . "</p>";
            echo "<p><strong>Business:</strong> " . htmlspecialchars($row['business']) . "</p>";
            echo "<p><strong>Mobile:</strong> " . htmlspecialchars($row['mobile_number']) . "</p>";
            echo "<p><strong>Location:</strong> <a href='https://www.google.com/maps/search/?api=1&query=" . urlencode($row['location']) . "' target='_blank'>" . htmlspecialchars($row['location']) . "</a></p>";
            echo "<p><strong>Experience:</strong> " . htmlspecialchars($row['experience']) . " years</p>";
            echo "<p><strong>Availability:</strong> " . htmlspecialchars($row['availability']) . "</p>";

            if (!empty($row['specialization_names'])) {
                echo "<p><strong>Specializations:</strong> " . htmlspecialchars($row['specialization_names']) . "</p>";
                echo "<p><strong>Charges:</strong> " . htmlspecialchars($row['specialization_charges']) . "</p>";
            } else {
                echo "<p><strong>Specializations:</strong> No Specializations</p>";
            }

            $avg_rating = $row['avg_rating'] ?: 0;
	    $total_ratings=$row['total_ratings'] ?:0;
            echo "<div class='rating'>";
            echo str_repeat("⭐", $avg_rating) . " " . number_format($avg_rating, 1) . "/5";
            echo "<p><strong>Total Ratings:</strong> " . htmlspecialchars($row['total_ratings']) . "</p>";
            echo "</div>";

            echo "<p><strong>Recent Comments:</strong> " . htmlspecialchars($row['comments']) . "</p>";
            echo "<div class='buttons-container'>";
            echo "<button class='rate-button' onclick='toggleBookingForm(" . $row['service_id'] . ")'>Book Service</button>";
            echo "<form action='rate.php' method='GET'>";
            echo "<input type='hidden' name='service_id' value='" . $row['service_id'] . "'>";
            echo "<button type='submit' class='rate-button'>Rate this Profile</button>";
            echo "</form>";
            echo "</div>";

            echo "<div id='booking-form-" . $row['service_id'] . "' class='booking-form'>";
            echo "<h2>Book Service</h2>";
            echo "<form method='POST'>";
            echo "<input type='hidden' name='service_id' value='" . $row['service_id'] . "'>";
            echo "<div class='form-group'><label for='name'>Name</label><input type='text' name='name' required></div>";
            echo "<div class='form-group'><label for='phone'>Phone Number</label><input type='text' name='phone' required></div>";
            echo "<div class='form-group'><label for='date'>Service Date</label><input type='date' name='date' required></div>";
            echo "<div class='buttons-container'>";
            echo "<button type='submit' name='book_service' class='submit-button'>Submit</button>";
            echo "<button type='button' class='cancel-button' onclick='toggleBookingForm(" . $row['service_id'] . ")'>Cancel</button>";
            echo "</div>";
            echo "</form>";
            echo "</div>";
            echo "</div>"; 
        }
    } else {
        echo "<p align=center text-size=30px>No services found.</p>";
    }
    ?>
</div>
</body>
</html>
