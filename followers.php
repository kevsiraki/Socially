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
	$ownUser = mysqli_query($link, "SELECT username FROM users WHERE username = '" . $_SESSION['username']."' AND username = '" . $user . "'");
	$_GET['ownUser'] = mysqli_fetch_array($ownUser);
	$alreadyFollowing = mysqli_query($link, "SELECT leadername,followername FROM follows WHERE 
	leadername = '" . $user."' AND followername = '" . $_SESSION["username"] . "'");
	$_GET['alreadyFollowing'] = mysqli_fetch_array($alreadyFollowing);
	$followers = mysqli_query($link, "SELECT * FROM follows WHERE leadername = '" . $user . "'");
	$following = mysqli_query($link, "SELECT * FROM follows WHERE followername = '" . $user . "'");
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$user = $_POST['user'];
	$ownUser = mysqli_query($link, "SELECT username FROM users WHERE username = '" . $_SESSION['username']."' AND username = '" . $user . "'");
	$_GET['ownUser'] = mysqli_fetch_array($ownUser);
	$alreadyFollowing = mysqli_query($link, "SELECT leadername,followername FROM follows WHERE 
	leadername = '" . $user."' AND followername = '" . $_SESSION["username"] . "'");
	$_GET['alreadyFollowing'] = mysqli_fetch_array($alreadyFollowing);
	$followers = mysqli_query($link, "SELECT * FROM follows WHERE leadername = '" . $user . "'");
	$ownUser = mysqli_query($link, "SELECT username FROM users WHERE username = '" . $_SESSION['username']."' AND username = '" . $user . "'");
	$_GET['ownUser'] = mysqli_fetch_array($ownUser);
	$followers = mysqli_query($link, "SELECT * FROM follows WHERE leadername = '" . $user . "'");
	$alreadyFollowing = mysqli_query($link, "SELECT leadername,followername FROM follows WHERE 
	leadername = '" . $user."' AND followername = '" . $_SESSION["username"] . "'");
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta charset="UTF-8">
    <title><?php echo $user;?>'s Followers</title>
    <link rel="stylesheet" href="style.css">
    <script>
	</script>
</head>
<body>


<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<input type="hidden" name="user" value="<?php echo $user;?>"/>
<h1><?php echo $user;?>'s Followers</h1>
<br>

            <?php   
				$i = 0;
                while($rows=mysqli_fetch_assoc($followers))
                {
					
             ?>

				<span class="tag tag-white">
				<a href="userProfileFollow.php?user= <?php echo $rows['followername'] ?>"><?php echo $rows['followername'];?></a>
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