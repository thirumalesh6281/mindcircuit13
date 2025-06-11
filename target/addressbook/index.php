<?php
session_start();

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'auth_system';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Registration logic
        $username = $conn->real_escape_string($_POST['reg_username']);
        $email = $conn->real_escape_string($_POST['reg_email']);
        $password = password_hash($conn->real_escape_string($_POST['reg_password']), PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        
        if ($conn->query($sql) === TRUE) {
            $success_msg = "Registration successful! Please login.";
        } else {
            $error_msg = "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['login'])) {
        // Login logic
        $username = $conn->real_escape_string($_POST['login_username']);
        $password = $conn->real_escape_string($_POST['login_password']);
        
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                $error_msg = "Invalid password!";
            }
        } else {
            $error_msg = "User not found!";
        }
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Auth System</title>
    <link rel="stylesheet" href="style.css">
<style>
/* Reset and base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Arial', sans-serif;
}

body {
    background: linear-gradient(350deg , #000080, #0000ff, #008080, #00ffff,rgb(209, 125, 195));
    color: #333;
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Auth forms */
.auth-forms {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 100px;
    margin-bottom: 120px;
}

.form-container {
    background: transparent;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;

}

.form-container h2 {
    margin-bottom: 20px;
    text-align: center;
    color: #2c3e50;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

input:focus {
    outline: none;
    border-color: #3498db;
}

/* Buttons */
.btn {
    padding: 12px 30px;
    margin: 20px 20px 20px 20px;
    background-color: 	#fafad2;
    text-decoration: none;
    color: black;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #d3d3d3;
}

/* Messages */
.error {
    color: #e74c3c;
    margin-bottom: 15px;
    text-align: center;
}

.success {
    color: #2ecc71;
    margin-bottom: 15px;
    text-align: center;
}

/* Main content */
.main-content {
    text-align: center;
    margin-top: 100px;
}

.main-content h1 {
    margin-bottom: 20px;
    color: #2c3e50;
}
</style>
</head>
<body>
    <div class="container">
        <?php if ($is_logged_in): ?>
            <!-- Main content for logged in users -->
            <div class="main-content">
                <h1 style="color: white;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <p style="margin-bottom: 32%; color: white;">You are now logged in to the system.</p>
                <a href="index.php?logout=1" class="btn">Logout</a>
            </div>
        <?php else: ?>
            <!-- Show login/register forms for guests -->
            <div class="auth-forms">

                <div class="form-container">
                    <h2>Register</h2>
                    <form method="POST">
                        <input type="text" name="reg_username" placeholder="Username" required>
                        <input type="email" name="reg_email" placeholder="Email" required>
                        <input type="password" name="reg_password" placeholder="Password" required>
                        <button type="submit" name="register" class="btn">Register</button>
                    </form>
                </div>

                <div class="form-container">
                    <h2>Login</h2>
                    <?php if (isset($error_msg)) echo "<p class='error'>$error_msg</p>"; ?>
                    <?php if (isset($success_msg)) echo "<p class='success'>$success_msg</p>"; ?>
                    <form method="POST">
                        <input type="text" name="login_username" placeholder="Username" required>
                        <input type="password" name="login_password" placeholder="Password" required>
                        <button type="submit" name="login" class="btn">Login</button>
                    </form>
                </div>

            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>
