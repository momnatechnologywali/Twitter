<?php
// profile.php - User profile page
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit();
}
 
$user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['id']) ? intval($_GET['id']) : $user_id;
 
// Fetch user info
$sql = "SELECT * FROM users WHERE id = $profile_user_id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql));
 
// Fetch tweets
$sql = "SELECT * FROM tweets WHERE user_id = $profile_user_id ORDER BY created_at DESC";
$tweets = mysqli_query($conn, $sql);
 
// Followers count
$sql = "SELECT COUNT(*) as count FROM follows WHERE following_id = $profile_user_id";
$followers = mysqli_fetch_assoc(mysqli_query($conn, $sql))['count'];
 
// Following count
$sql = "SELECT COUNT(*) as count FROM follows WHERE follower_id = $profile_user_id";
$following = mysqli_fetch_assoc(mysqli_query($conn, $sql))['count'];
 
// Check if following
$is_following = false;
if ($profile_user_id != $user_id) {
    $sql = "SELECT * FROM follows WHERE follower_id = $user_id AND following_id = $profile_user_id";
    $is_following = mysqli_num_rows(mysqli_query($conn, $sql)) > 0;
}
 
// Handle follow/unfollow
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['follow_action'])) {
    if ($_POST['follow_action'] == 'follow') {
        $sql = "INSERT INTO follows (follower_id, following_id) VALUES ($user_id, $profile_user_id)";
        mysqli_query($conn, $sql);
    } else {
        $sql = "DELETE FROM follows WHERE follower_id = $user_id AND following_id = $profile_user_id";
        mysqli_query($conn, $sql);
    }
    echo '<script>window.location.href = "profile.php?id=' . $profile_user_id . '";</script>';
}
 
// Handle profile edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bio'])) {
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    // Handle profile_pic upload if needed, simplified
    $sql = "UPDATE users SET bio = '$bio' WHERE id = $user_id";
    mysqli_query($conn, $sql);
    echo '<script>window.location.href = "profile.php";</script>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - @<?php echo $user['username']; ?></title>
    <style>
        /* Internal CSS - Similar to homepage, responsive */
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .profile-header { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; text-align: center; }
        .profile-header img { width: 100px; height: 100px; border-radius: 50%; }
        .profile-header h2 { margin: 10px 0; }
        .profile-stats { display: flex; justify-content: center; gap: 20px; }
        .edit-form { margin-top: 20px; }
        .edit-form textarea { width: 100%; height: 80px; }
        .edit-form button { background: #1da1f2; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; }
        .follow-btn { background: #1da1f2; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; }
        .follow-btn.unfollow { background: #e0245e; }
        .tweet { /* Reuse from homepage */ background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 15px; }
        @media (max-width: 600px) { .profile-stats { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <img src="<?php echo $user['profile_pic']; ?>" alt="Profile">
            <h2>@<?php echo $user['username']; ?></h2>
            <p><?php echo $user['bio']; ?></p>
            <div class="profile-stats">
                <div>Followers: <?php echo $followers; ?></div>
                <div>Following: <?php echo $following; ?></div>
            </div>
            <?php if ($profile_user_id == $user_id): ?>
                <div class="edit-form">
                    <form method="POST">
                        <textarea name="bio" placeholder="Update bio"><?php echo $user['bio']; ?></textarea>
                        <button type="submit">Update Profile</button>
                    </form>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="follow_action" value="<?php echo $is_following ? 'unfollow' : 'follow'; ?>">
                    <button class="follow-btn <?php echo $is_following ? 'unfollow' : ''; ?>" type="submit"><?php echo $is_following ? 'Unfollow' : 'Follow'; ?></button>
                </form>
            <?php endif; ?>
        </div>
        <div class="feed">
            <?php while ($row = mysqli_fetch_assoc($tweets)): ?>
                <div class="tweet">
                    <div><?php echo nl2br($row['content']); ?></div>
                    <div class="tweet-time"><?php echo $row['created_at']; ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
