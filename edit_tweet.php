<?php
// edit_tweet.php - Edit tweet
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) exit();
 
$user_id = $_SESSION['user_id'];
$tweet_id = intval($_POST['tweet_id']);
$content = mysqli_real_escape_string($conn, $_POST['content']);
 
// Verify ownership
$sql = "SELECT * FROM tweets WHERE id = $tweet_id AND user_id = $user_id";
if (mysqli_num_rows(mysqli_query($conn, $sql)) > 0) {
    $sql = "UPDATE tweets SET content = '$content' WHERE id = $tweet_id";
    mysqli_query($conn, $sql);
}
