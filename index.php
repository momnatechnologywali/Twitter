<?php
// index.php - Homepage with tweet feed and creation box
session_start();
include 'db.php';
 
// Check if user is logged in, otherwise redirect to login (using JS as per instruction)
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit();
}
 
$user_id = $_SESSION['user_id'];
 
// Handle tweet posting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tweet_content'])) {
    $content = mysqli_real_escape_string($conn, $_POST['tweet_content']);
    $sql = "INSERT INTO tweets (user_id, content) VALUES ($user_id, '$content')";
    mysqli_query($conn, $sql);
    echo '<script>window.location.href = "index.php";</script>'; // JS redirect
}
 
// Fetch feed: tweets from followed users and own tweets, ordered by recent
$sql = "SELECT t.*, u.username, u.profile_pic 
        FROM tweets t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.user_id = $user_id OR t.user_id IN (SELECT following_id FROM follows WHERE follower_id = $user_id)
        ORDER BY t.created_at DESC LIMIT 50";
$result = mysqli_query($conn, $sql);
 
// For real-time, we'll use meta refresh for simplicity (no separate JS file, internal script for polling if needed, but keeping simple)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Homepage</title>
    <meta http-equiv="refresh" content="30"> <!-- Auto-refresh every 30s for semi-real-time -->
    <style>
        /* Internal CSS - Kamal ka, real-looking, responsive */
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .tweet-box { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .tweet-box textarea { width: 100%; height: 60px; border: 1px solid #ddd; border-radius: 4px; padding: 10px; font-size: 16px; }
        .tweet-box button { background: #1da1f2; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: bold; }
        .tweet-box button:hover { background: #0c84d6; }
        .feed { }
        .tweet { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 15px; display: flex; }
        .tweet img { width: 50px; height: 50px; border-radius: 50%; margin-right: 15px; }
        .tweet-content { flex: 1; }
        .tweet-header { font-weight: bold; margin-bottom: 5px; }
        .tweet-time { color: #657786; font-size: 14px; }
        .tweet-actions { margin-top: 10px; display: flex; gap: 20px; }
        .tweet-actions button { background: none; border: none; cursor: pointer; color: #657786; }
        .tweet-actions button:hover { color: #1da1f2; }
        @media (max-width: 600px) { .container { padding: 10px; } .tweet { flex-direction: column; align-items: center; } .tweet img { margin-bottom: 10px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="tweet-box">
            <form method="POST">
                <textarea name="tweet_content" placeholder="What's happening?" required></textarea>
                <button type="submit">Tweet</button>
            </form>
        </div>
        <div class="feed">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="tweet">
                    <img src="<?php echo $row['profile_pic']; ?>" alt="Profile">
                    <div class="tweet-content">
                        <div class="tweet-header">@<?php echo $row['username']; ?></div>
                        <div><?php echo nl2br($row['content']); ?></div>
                        <div class="tweet-time"><?php echo $row['created_at']; ?></div>
                        <div class="tweet-actions">
                            <button onclick="likeTweet(<?php echo $row['id']; ?>)">Like (<?php echo getLikeCount($conn, $row['id']); ?>)</button>
                            <button onclick="commentTweet(<?php echo $row['id']; ?>)">Comment</button>
                            <!-- Edit/Delete if own tweet -->
                            <?php if ($row['user_id'] == $user_id): ?>
                                <button onclick="editTweet(<?php echo $row['id']; ?>)">Edit</button>
                                <button onclick="deleteTweet(<?php echo $row['id']; ?>)">Delete</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script>
        // Internal JS for actions and redirection
        function likeTweet(tweetId) {
            // AJAX like (simplified, assume separate like.php)
            fetch('like.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'tweet_id=' + tweetId
            }).then(() => location.reload());
        }
        function commentTweet(tweetId) {
            let comment = prompt('Enter comment:');
            if (comment) {
                fetch('comment.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'tweet_id=' + tweetId + '&content=' + encodeURIComponent(comment)
                }).then(() => location.reload());
            }
        }
        function editTweet(tweetId) {
            let newContent = prompt('Edit tweet:');
            if (newContent) {
                fetch('edit_tweet.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'tweet_id=' + tweetId + '&content=' + encodeURIComponent(newContent)
                }).then(() => location.reload());
            }
        }
        function deleteTweet(tweetId) {
            if (confirm('Delete tweet?')) {
                fetch('delete_tweet.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'tweet_id=' + tweetId
                }).then(() => location.reload());
            }
        }
    </script>
</body>
</html>
<?php
function getLikeCount($conn, $tweet_id) {
    $sql = "SELECT COUNT(*) as count FROM likes WHERE tweet_id = $tweet_id";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['count'];
}
?>
