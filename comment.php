<?php
// comment.php - Handle comment
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) exit();
 
$user_id = $_SESSION['user_id'];
$tweet_id = intval($_POST['tweet_id']);
$content = mysqli_real_escape_string($conn, $_POST['content']);
 
$sql = "INSERT INTO comments (user_id, tweet_id, content) VALUES ($user_id, $tweet_id, '$content')";
mysqli_query($conn, $sql);
