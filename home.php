<?php
// home.php - Homepage with real-time tweet feed and tweet creation
session_start();
include 'db.php';
 
// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit();
}
 
$user_id = $_SESSION['user_id'];
 
// Handle tweet posting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tweet_content'])) {
    $content = mysqli_real_escape_string($conn, trim($_POST['tweet_content']));
    if (!empty($content) && strlen($content) <= 280) {
        $sql = "INSERT INTO tweets (user_id, content) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $user_id, $content);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo '<script>window.location.href = "home.php";</script>';
        exit();
    } else {
        $error = "Tweet must be between 1 and 280 characters.";
    }
}
 
// Fetch feed: tweets from followed users and own tweets
$sql = "SELECT t.*, u.username, u.profile_pic 
        FROM tweets t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.user_id = ? OR t.user_id IN (SELECT following_id FROM follows WHERE follower_id = ?)
        ORDER BY t.created_at DESC LIMIT 50";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Home</title>
    <meta http-equiv="refresh" content="30"> <!-- Auto-refresh for semi-real-time feed -->
    <style>
        /* Internal CSS - Professional, Twitter-like, responsive */
        body { 
            font-family: Arial, sans-serif; 
            background: #f0f2f5; 
            margin: 0; 
            padding: 0; 
            color: #333; 
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        .tweet-box { 
            background: white; 
            padding: 15px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            margin-bottom: 20px; 
        }
        .tweet-box textarea { 
            width: 100%; 
            height: 60px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            padding: 10px; 
            font-size: 16px; 
            resize: none; 
        }
        .tweet-box button { 
            background: #1da1f2; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 20px; 
            cursor: pointer; 
            font-weight: bold; 
        }
        .tweet-box button:hover { 
            background: #0c84d6; 
        }
        .error { 
            color: #e0245e; 
            font-size: 14px; 
            margin-bottom: 10px; 
        }
        .feed { }
        .tweet { 
            background: white; 
            padding: 15px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            margin-bottom: 15px; 
            display: flex; 
        }
        .tweet img { 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            margin-right: 15px; 
        }
        .tweet-content { 
            flex: 1; 
        }
        .tweet-header { 
            font-weight: bold; 
            margin-bottom: 5px; 
        }
        .tweet-header a { 
            color: #1da1f2; 
            text-decoration: none; 
        }
        .tweet-header a:hover { 
            text-decoration: underline; 
        }
        .tweet-time { 
            color: #657786; 
            font-size: 14px; 
        }
        .tweet-actions { 
            margin-top: 10px; 
            display: flex; 
            gap: 20px; 
        }
        .tweet-actions button { 
            background: none; 
            border: none; 
            cursor: pointer; 
            color: #657786; 
            font-size: 14px; 
        }
        .tweet-actions button:hover { 
            color: #1da1f2; 
        }
        .navbar { 
            background: white; 
            padding: 10px; 
            border-bottom: 1px solid #ddd; 
            margin-bottom: 20px; 
            text-align: center; 
        }
        .navbar a { 
            margin: 0 15px; 
            color: #1da1f2; 
            text-decoration: none; 
            font-weight: bold; 
        }
        .navbar a:hover { 
            text-decoration: underline; 
        }
        @media (max-width: 600px) { 
            .container { 
                padding: 10px; 
            } 
            .tweet { 
                flex-direction: column; 
                align-items: center; 
            } 
            .tweet img { 
                margin-bottom: 10px; 
            } 
            .navbar a { 
                display: block; 
                margin: 10px 0; 
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="home.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <div class="tweet-box">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <textarea name="tweet_content" placeholder="What's happening?" maxlength="280" required></textarea>
                <button type="submit">Tweet</button>
            </form>
        </div>
        <div class="feed">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
