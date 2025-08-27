<?php
// like.php - Handle like action
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) exit();
 
$user_id = $_SESSION['user_id'];
$tweet_id = intval($_POST['tweet_id']);
 
// Check if already liked
$sql = "SELECT * FROM likes WHERE user_id = $user_id AND tweet_id = $tweet_id";
if (mysqli_num_rows(mysqli_query($conn, $sql)) == 0) {
    $sql = "INSERT INTO likes (user_id, tweet_id) VALUES ($user_id, $tweet_id)";
    mysqli_query($conn, $sql);
}
