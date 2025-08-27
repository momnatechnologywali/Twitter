<?php
// delete_tweet.php - Delete tweet
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) exit();
 
$user_id = $_SESSION['user_id'];
$tweet_id = intval($_POST['tweet_id']);
 
// Verify ownership
$sql = "SELECT * FROM tweets WHERE id = $tweet_id AND user_id = $user_id";
if (mysqli_num_rows(mysqli_query($conn, $sql)) > 0) {
    $sql = "DELETE FROM tweets WHERE id = $tweet_id";
    mysqli_query($conn, $sql);
}
