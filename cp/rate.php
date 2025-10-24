<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Insert the rating and comment into the database
    $sql = "INSERT INTO ratings (service_id, rating, comment) VALUES ($service_id, $rating, '$comment')";
    if ($conn->query($sql) === TRUE) {
        header("Location: search.php?query=" . urlencode($_GET['query']));
        exit();
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }
} else {
    // Check if the service_id is passed through GET method
    if (isset($_GET['service_id'])) {
        $service_id = $_GET['service_id'];
    } else {
        // Handle missing service_id error or redirect
        die("Service ID is missing.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Service</title>
    <style>
        /* CSS styles */
        body {
            background-color: #caf0f8;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background-color: #90e0ef; 
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #0077b6;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #03045e;
        }
        select, textarea, button {
            width: 90%;
            padding: 10px;
            border: 2px solid #00b4d8;
            border-radius: 5px;
            color: #03045e;
            background-color: #fefefe; 
            font-size: 1em;
        }
        .submit-button {
            margin-top: 15px;
            background-color: #0077b6; 
            color: #fefefe;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            padding: 10px;
            transition: background-color 0.3s ease;
            width: 70%;
            align-items: center;
            text-align:center;
        }
        .submit-button:hover {
            background-color: #00b4d8;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 align=center>Rate Service</h2>
    <form action="rate.php" method="POST">
        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_id); ?>">
        
        <label for="rating">Rating (1-5):</label>
        <select name="rating" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>

        <label for="comment">Comment:</label>
        <textarea name="comment" rows="4"></textarea>
        
        <button type="submit" class="submit-button">Submit Rating</button>
    </form>
</div>

</body>
</html>
