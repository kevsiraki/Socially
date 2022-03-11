<?php
// Initialize the session
error_reporting(0);
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
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
	$usersBlogs = mysqli_query($link, "SELECT * FROM blogs WHERE created_by = '".$user."' ");
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
    if (isset($_POST["follow"]) && !isset($_GET['alreadyFollowing'][0])) //and not already following
    {
        $sql = "INSERT INTO follows (leadername, followername) VALUES (?, ?)";
        if ($stmt = mysqli_prepare($link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_leader,$param_follower);
            // Set parameters
            $param_leader = $user;
			$param_follower = $_SESSION["username"];
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect
               header("location: userProfileFollow.php?user=".$user);
            }
            else {
                echo "Oopsies.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
	else if (isset($_POST["follow"]) && isset($_GET['alreadyFollowing'][0])) //and already following
    {
        mysqli_query($link,"DELETE FROM follows WHERE leadername = '" . $user."' AND followername = '" . $_SESSION['username'] . "'");
		header("location: userProfileFollow.php?user=".$user);
	}
	$result = mysqli_query($link, "SELECT * FROM users WHERE username = '" . $user . "'");
	$ownUser = mysqli_query($link, "SELECT username FROM users WHERE username = '" . $_SESSION['username']."' AND username = '" . $user . "'");
	$_GET['ownUser'] = mysqli_fetch_array($ownUser);
	$followers = mysqli_query($link, "SELECT * FROM follows WHERE leadername = '" . $user . "'");
	$alreadyFollowing = mysqli_query($link, "SELECT leadername,followername FROM follows WHERE 
	leadername = '" . $user."' AND followername = '" . $_SESSION["username"] . "'");
	$_GET['alreadyFollowing'] = mysqli_fetch_array($alreadyFollowing);
	$hobbies = mysqli_query($link, "SELECT * FROM hobbies WHERE username = '".$user."'");
	$usersBlogs = mysqli_query($link, "SELECT * FROM blogs WHERE created_by = '".$user."' ");
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	
    <meta charset="UTF-8">
    <title><?php echo $user;?>'s Profile</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="style.css">
	 		<style>

#blueDIV {
  position:absolute;
  width:200px;
  padding:10px;
  border-radius:20px;
  background-color:white;
  right: 3%;
}
p:hover { transform: scale(1.1); }
a:hover { transform: scale(1.1); }
input:hover { transform: scale(1.2); }
textarea:hover { transform: scale(1.2); }
small:hover { transform: scale(1.4); }

.card:hover { transform: scale(1.1); }
	</style>
</head>
<body>
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<input type="hidden" name="user" value="<?php echo $user;?>"/>
	    <section>
		<div class = "container">
        <div class = "card">
		<br>
            <?php   
                while($rows=mysqli_fetch_assoc($result))
                {
             ?>
				<h1><a href><?php echo $rows['username'];?></a><br></h1>
				<h5><?php echo $rows['first_name']." ".$rows['last_name']?></h5>
             
            <?php
                }
             ?>
        <div class = "card_footer">
            <?php   
				
                while($rows=mysqli_fetch_assoc($hobbies))
                {
					
             ?>
				
                 <span class="tag tag-brown"><?php echo $rows['hobby'];?></span>
				
            <?php
                }
             ?><br><br>
       
		<?php   
				$i = 0;
                while($rows=mysqli_fetch_assoc($followers))
                {
					++$i;
				}
			 ?>
			 
		<?php   
				$j = 0;
                while($rows=mysqli_fetch_assoc($following))
                {
					++$j;
				}
			 ?>	 
			 
		<td><a class="btn btn-dark"href="followers.php?user= <?php echo $user ?>"><b><?php echo 'Followers: '.$i ;?></b></a></td>
		<br>
		<br>
		<td><a class="btn btn-dark" href="following.php?user= <?php echo $user ?>"><b><?php echo 'Following: '.$j;?> </b></a></td>
		
		</div><br><br></div></div>
    </section>
	
	
	<?php 
	if (empty($_GET['ownUser'][0]))
	{
		if(!empty($_GET['alreadyFollowing'][0]))
		{
	?>
	
	<div class="form-group">
            <input type="submit" class="btn btn-secondary" value="Unfollow" name = "follow">
    </div>
	
	<?php 
		}
		else {
		?>
			<div class="form-group">
            <input type="submit" class="btn btn-success" value="Follow" name = "follow">
    </div>
<?php 
	}
	}
	else {
		?>
		<a href="settings.php" class="btn btn-secondary">Account Settings</a>
		<?php
	}
	?>
	<br>
            <?php   
                while($rows=mysqli_fetch_assoc($usersBlogs))
                {	
             ?>
	<div class="container">
    <div class="card">
    <div class="card__body">
	 <a href="userProfileFollow.php?user= <?php echo $rows['created_by']?>"><?php echo $rows['created_by'];?></a>
          <small><?php echo $rows['pdate'];?></small>
	  
      <h4><a href="comment.php?blogid= <?php echo $rows['blogid'] ?>"><?php echo $rows['subject'];?></a></h4>
      <p><?php echo ($rows['description']);?></p>
    </div>
    <div class="card__footer">
	<?php 
	  $result2 = mysqli_query($link, "SELECT * FROM blogstags WHERE blogid = '" . $rows['blogid'] . "'");
	  while($rows2=mysqli_fetch_assoc($result2)) 
	  { 
  ?>
  <a href="tags.php?tag=<?php echo nl2br($rows2['tag']); ?>" ><span class="tag tag-blue"><?php echo trim('#'.(preg_replace("/\s+/", "",$rows2['tag'])));?></span></a><?php } 
  ?>
	
	
      <div class="user">
		  <?php 
					 if (!empty($_GET['ownUser'][0]) ) {
						?>
						<a href="editDeleteBlog.php?blogid=<?php echo $rows['blogid']?>">Edit/Delete</a>
						<?php
						}
					 
						?>	
 
     </div>
    </div></div>
  </div><br>
				
			<?php
                }
				mysqli_close($link);
			?>
       <br><br>
        <a href="followerBlogs.php" class="btn btn-secondary">Back to Followed Blogs</a>
		<a href="welcome.php" class="btn btn-secondary">Back to Community Blogs</a>
		<br><br><br><br> 
</form>
<br>	
 
   
</body>
</html>