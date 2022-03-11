<?php

session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

if(isset($_GET["user"])) {
	$user = trim($_GET["user"]);
	$result = mysqli_query($link, "SELECT * FROM users where username = '" . $user . "'");
	$ownUser = mysqli_query($link, "SELECT username FROM users WHERE username = '" . $_SESSION['username']."' AND username = '" . $user . "'");
	$_GET['ownUser'] = mysqli_fetch_array($ownUser);
	$alreadyFollowing = mysqli_query($link, "SELECT leadername,followername FROM follows WHERE 
	leadername = '" . $user."' AND followername = '" . $_SESSION["username"] . "'");
	$_GET['alreadyFollowing'] = mysqli_fetch_array($alreadyFollowing);
	$followers = mysqli_query($link, "SELECT * FROM follows WHERE leadername = '" . $user . "'");
	$following = mysqli_query($link, "SELECT * FROM follows WHERE followername = '" . $user . "'");
	$hobbies = mysqli_query($link, "SELECT * FROM hobbies WHERE username = '".$user."'");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$user = $_POST['user'];
    $result = mysqli_query($link, "SELECT * FROM users WHERE username = '" . $user . "'");
	$ownUser = mysqli_query($link, "SELECT username FROM users WHERE username = '" . $_SESSION['username']."' AND username = '" . $user . "'");
	$_GET['ownUser'] = mysqli_fetch_array($ownUser);
	$alreadyFollowing = mysqli_query($link, "SELECT leadername,followername FROM follows WHERE 
	leadername = '" . $user."' AND followername = '" . $_SESSION["username"] . "'");
	$_GET['alreadyFollowing'] = mysqli_fetch_array($alreadyFollowing);
	$followers = mysqli_query($link, "SELECT * FROM follows WHERE leadername = '" . $user . "'");
	$hobbies = mysqli_query($link, "SELECT * FROM hobbies WHERE username = '".$user."'");
	$result = mysqli_query($link, "SELECT * FROM users WHERE username = '" . $user . "'");
	$ownUser = mysqli_query($link, "SELECT username FROM users WHERE username = '" . $_SESSION['username']."' AND username = '" . $user . "'");
	$_GET['ownUser'] = mysqli_fetch_array($ownUser);
	$followers = mysqli_query($link, "SELECT * FROM follows WHERE leadername = '" . $user . "'");
	$alreadyFollowing = mysqli_query($link, "SELECT leadername,followername FROM follows WHERE 
	leadername = '" . $user."' AND followername = '" . $_SESSION["username"] . "'");
	$_GET['alreadyFollowing'] = mysqli_fetch_array($alreadyFollowing);
	$hobbies = mysqli_query($link, "SELECT * FROM hobbies WHERE username = '".$user."'");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta charset="UTF-8">
    <title>Following <?php echo $user;?></title>
    <link rel="stylesheet" href="style.css">
    <script>
	function refreshPage(){
    window.location.reload();
} 
	</script>
</head>
<body>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<input type="hidden" name="user" value="<?php echo $user;?>"/>
<h1>Following <?php echo $user;?></h1>
<br>
            <?php   
				$i = 0;
                while($rows=mysqli_fetch_assoc($following))
                {
             ?>
				<span class="tag tag-white">
				<a href="userProfileFollow.php?user= <?php echo $rows['leadername'] ?>"><?php echo $rows['leadername'];?></a>
                </span>
				<br><br>
            <?php
                }
             ?>
	<br>
	<br>
	<a href="userProfileFollow.php?user= <?php echo $user ?>" class="btn btn-info">Back to <?php echo $user ?></a>
</body>
</html>