<?php
// register.php - Fixed user signup with MD5 for consistency with login.php
session_start();
include 'db.php';
 
// Prevent logged-in users from accessing signup
if (isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "index.php";</script>';
    exit();
}
 
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
 
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check for existing username or email using prepared statement
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            $error = "Database error: Unable to prepare statement.";
        } else {
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "Username or email already taken.";
            } else {
                // Hash password with MD5 to match login.php
                $hashed_password = md5($password); // Note: MD5 is insecure, consider updating login.php to use password_hash
                $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt_insert = mysqli_prepare($conn, $sql);
                if ($stmt_insert === false) {
                    $error = "Database error: Unable to prepare insert statement.";
                } else {
                    mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $hashed_password);
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $_SESSION['user_id'] = mysqli_insert_id($conn);
                        echo '<script>window.location.href = "index.php";</script>';
                        exit();
                    } else {
                        $error = "Error creating account: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt_insert);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        /* Internal CSS - Professional, consistent with other pages, responsive */
        body { 
            font-family: Arial, sans-serif; 
            background: #f0f2f5; 
            margin: 0; 
            padding: 0; 
            color: #333; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }
        .container { 
            max-width: 400px; 
            width: 100%; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
        }
        .form-title { 
            text-align: center; 
            font-size: 24px; 
            font-weight: bold; 
            margin-bottom: 20px; 
            color: #1da1f2; 
        }
        form { 
            display: flex; 
            flex-direction: column; 
        }
        input { 
            margin-bottom: 15px; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-size: 16px; 
        }
        button { 
            background: #1da1f2; 
            color: white; 
            border: none; 
            padding: 12px; 
            border-radius: 20px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold; 
        }
        button:hover { 
            background: #0c84d6; 
        }
        .error { 
            color: #e0245e; 
            text-align: center; 
            margin-bottom: 15px; 
            font-size: 14px; 
        }
        .login-link { 
            text-align: center; 
            margin-top: 15px; 
        }
        .login-link a { 
            color: #1da1f2; 
            text-decoration: none; 
        }
        .login-link a:hover { 
            text-decoration: underline; 
        }
        @media (max-width: 600px) { 
            .container { 
                width: 90%; 
                padding: 15px; 
            } 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-title">Sign Up</div>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>
