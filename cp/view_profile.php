<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
body {
    background: url('edit profile.jpeg') no-repeat center center fixed;
    background-size: cover;
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}
header {
    position: relative;
    text-align: center;
    padding: 24px; 
    background-color: transparent;
}
header h1 {
    margin: 0;
    font-size: 2.4em; 
    color: #03045E;
}
.menu {
    position: absolute;
    top: 32px; 
    right: 24px;
}
.menu a {
    color: #caf0f8;
    background-color: #003459;
    padding: 12px 16px;
    font-size: 1em; 
    text-decoration: none;
    border-radius: 6px; 
    font-weight: bold;
}
.menu a:hover {
    background-color: #90e0ef;
}
.container {
    max-width: 480px; 
    margin: 40px auto;
    padding: 24px; 
    background-color: rgba(144, 224, 239, 0.8);
    border-radius: 12px;
    box-shadow: 0px 0px 12px rgba(0, 0, 0, 0.2); 
    text-align: center;
}
.container h2 {
    font-size: 2em; 
    color: #003459;
    margin-top: 0;
}
.container input[type="text"],
.container input[type="password"],
.container button {
    width: 90%;
    padding: 12px; 
    margin: 12px 0; 
    border-radius: 6px; 
    border: 1.6px solid #0077b6; 
    font-size: 1em; 
}
.container button {
    background-color: #00b4d8;
    color: #ffffff;
    cursor: pointer;
    border: none;
    width:70%;
}
.container button:hover {
    background-color: #0077b6;
}
.logout {
    color: #03045e;
    font-size: 1em; 
    display: block;
    margin-top: 24px; 
    font-weight: bold;
    text-decoration: none;
}
body, html {
    overflow: hidden;
    height: 100%;
}


</style>

</head>
<body>
    <?php
    session_start();

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'services');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Log out the user
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_SESSION['service_id'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Query for user credentials in the login table
        $stmt = $conn->prepare("SELECT * FROM login WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $loginData = $result->fetch_assoc();
            // Verify password
            if (password_verify($password, $loginData['password'])) {
                $_SESSION['service_id'] = $loginData['service_id'];
                // Stay on the same page, just show profile options after login
            } else {
                $login_error = "Invalid credentials. Please try again.";
            }
        } else {
            $login_error = "Invalid credentials. Please try again.";
        }

        $stmt->close();
    }

    // If user is logged in, show profile options
    if (isset($_SESSION['service_id'])) {
        ?>
        <header>
            <h1>MY PROFILE</h1>
            <div class="menu">
                <a href="menu.html">Main Menu</a> 
            </div>
        </header>
        <div class="container">
            <h2 color=>WELCOME</h2>
            <div>
                <button onclick="window.location.href='view_bookings.php'">View Bookings</button>
                <button onclick="window.location.href='edit_profile.php'">Edit Profile</button>
            </div>
            <a href="?logout=true" class="logout">Logout</a>
        </div>
        <?php
    } else {
        // Show login form if not logged in
        ?>
        <header>
            <h1>MY PROFILE</h1>
            <div class="menu">
                <a href="menu.html">Main Menu</a>
            </div>
        </header>
        <div class="container">
            <h2>Enter Credentials</h2>
            <form name="f1" method="POST" action="">
                <label><b>Username:</label>
                <input type="text" name="username" required><br>

                <label><b>Password:</label>
                <input type="password" name="password" required><br>

                <button type="submit">Login</button>
            </form>
            <?php if (isset($login_error)) { ?>
                <p style="color:red;"><?php echo $login_error; ?></p>
            <?php } ?>
        </div>
        <?php
    }

    $conn->close();
    ?>
</body>
</html>