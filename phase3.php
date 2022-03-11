<?php
// Initialize the session
session_start();
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
require_once "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta charset="UTF-8">
	<title>User Search</title>
	<link rel="stylesheet" href="style.css">
	<style>
	p:hover { transform: scale(1.4); }
a:hover { transform: scale(1.1); }
input:hover { transform: scale(1.2); }
textarea:hover { transform: scale(1.2); }
small:hover { transform: scale(1.4); }

.card:hover { transform: scale(1.1); }
	</style>
	
</head>
<body >
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<h1>Search For Users</h1><br>
        <input name="userX" id = "userX" ></input>
		<br>
		<br>
		<title>User Search</title>
		<?php
		echo nl2br("\n");
		if(isset($_POST['userX'])) 
		{
			$userx = (trim($_POST['userX']));
			$result = mysqli_query($link, "SELECT * FROM users WHERE username = '".$userx."'"); 
			if(!empty(mysqli_fetch_array($result))){
				$result = mysqli_query($link, "SELECT * FROM users WHERE username = '".$userx."'"); 
			}
			else if(empty(mysqli_fetch_array($result))) {
				$result = mysqli_query($link, "SELECT * FROM users WHERE username LIKE '%{$userx}%' ");
			}
			while($rows=mysqli_fetch_assoc($result))
			{
				?>
				<span class="tag tag-white">
				<a href="userProfileFollow.php?user= <?php echo $rows['username'];?>"><?php echo $rows['username'];?></a>
				</span>
				<br> 
				<br>
				<?php
			}
		}
		?>
		<br> 
				<br>
				<br> 
				<br>
		        <a href="followerBlogs.php" class="btn btn-secondary">Back to Followed Blogs</a>
		<a href="welcome.php" class="btn btn-secondary">Back to Community Blogs</a>
	<br> 
				<br>
				
	</form>
  

   
</body>
</html>