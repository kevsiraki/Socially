<?php

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("location: login.php");
    exit;
}

require_once "config.php";

$todaysDate = date("Y/m/d");

if(isset($_GET["blogid"])) {
	$blogid = trim($_GET["blogid"]);
	$result = mysqli_query($link, "SELECT * FROM blogs JOIN blogstags ON blogs.blogid = '" . $blogid . "' AND blogstags.blogid = '" . $blogid . "'");
	$resultC = mysqli_query($link, "SELECT * FROM comments WHERE blogid = '" . $blogid . "'");
	$username = mysqli_fetch_array($result);
	$dailyLimit = mysqli_query($link, "SELECT COUNT(cdate) FROM comments WHERE posted_by = '" . $_SESSION['username'] . "' AND cdate = '" . $todaysDate . "'");
	$_GET['sum'] = mysqli_fetch_array($dailyLimit);
	$blogLimit = mysqli_query($link, "SELECT COUNT(blogid) FROM comments WHERE posted_by = '" . $_SESSION['username'] . "'AND blogid = '" . $_GET['blogid'] . "'");
	$_GET['sumC'] = mysqli_fetch_array($blogLimit);
	$ownLimit = mysqli_query($link, "SELECT COUNT(created_by) FROM blogs WHERE created_by = '" . $_SESSION['username'] . "'AND blogid = '" . $_GET['blogid'] . "'");
	$_GET['ownBlog'] = mysqli_fetch_array($ownLimit);
	$ownComment = mysqli_query($link, "SELECT (posted_by) FROM comments WHERE posted_by = '" . $_SESSION['username']."'");
	$_GET['ownComment'] = mysqli_fetch_array($ownComment);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$blogid = $_POST['blogid'];
    $dailyLimit = mysqli_query($link, "SELECT COUNT(cdate) FROM comments WHERE posted_by = '" . $_SESSION['username'] . "' AND cdate = '" . $todaysDate . "'");
    $_GET['sum'] = mysqli_fetch_array($dailyLimit);
    $blogLimit = mysqli_query($link, "SELECT COUNT(blogid) FROM comments WHERE posted_by = '" . $_SESSION['username'] . "'AND blogid = '" . $_POST['blogid'] . "'");
    $_GET['sumC'] = mysqli_fetch_array($blogLimit);
    $result = mysqli_query($link, "SELECT * FROM blogs JOIN blogstags ON blogs.blogid = '" . $blogid . "' AND blogstags.blogid = '" . $blogid . "'");
    $resultC = mysqli_query($link, "SELECT * FROM comments WHERE blogid = '" . $blogid . "'");
	$ownLimit = mysqli_query($link, "SELECT created_by FROM blogs WHERE created_by = '" . $_SESSION['username'] . "'");
	$ownBlog = mysqli_fetch_assoc($ownLimit);
	$ownComment = mysqli_query($link, "SELECT (posted_by) FROM comments WHERE posted_by = '" . $_SESSION['username']."'");
	$_GET['ownComment'] = mysqli_fetch_array($ownComment);
    $sentiment = "";
    if ($_POST["sentiments"] == "Positive") {
        $sentiment = "positive";
    }
    else {
        $sentiment = "negative";
    }
    if (($_GET['sum'][0] <= 100) && ($_GET['sumC'][0] < 50) && isset($_POST["submit"]) && !(empty(trim($_POST["comment"])))) {
        $sql = "INSERT INTO comments (sentiment, description, cdate, blogid, posted_by) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssis", $param_sentiment, $param_comment, $param_date, $blogid, $param_username);
            $param_sentiment = $sentiment;
            $param_comment = trim($_POST["comment"]);
            $param_date = date("Y/m/d");
            $param_username = $_SESSION["username"];
            if (mysqli_stmt_execute($stmt)) {
               header("location: comment.php?blogid=".$blogid);

            }
            else {            
                echo "Oopsies.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
$result = mysqli_query($link, "SELECT * FROM blogs JOIN blogstags ON blogs.blogid = '" . $blogid . "' AND blogstags.blogid = '" . $blogid . "'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $username[4] ?>'s Comments</title>
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
	<input type="hidden" name="blogid" value="<?php echo $blogid;?>"/>
	<section>
		<br>
		<br>
            <?php   
				$previousID = "";
                while($rows=mysqli_fetch_assoc($result))
                {
					$ownLimit2 = mysqli_query($link, "SELECT created_by FROM blogs WHERE created_by = '" . $_SESSION['username'] . "'");
					$_GET['ownBlog2'] = mysqli_fetch_array($ownLimit2);
             ?>
			<?php if($previousID != $rows['blogid']): ?>
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
								<a href="tags.php?tag=<?php echo nl2br($rows2['tag']); ?>" ><span class="tag tag-blue"><?php echo trim('#'.(preg_replace("/\s+/", "",$rows2['tag'])));?></span></a>
						<?php 	
							} 
						?>
					</div>
					      <div class="user">
					<?php 
					 if ($rows['created_by']==$_SESSION["username"]) {
						?>
						<a href="editDeleteBlog.php?blogid=<?php echo $rows['blogid']?>">&nbsp;Edit/Delete</a>
						<?php
						}
					
						?>
						
     </div>
	
				</div>
			</div>
			<br>
			<?php endif; ?>
			<?php
					$previousID = $rows['blogid'];
                }
			?>
		<h1>Comments</h1>
            <?php   
                while($rows=mysqli_fetch_assoc($resultC))
                {
             ?>
					<div class = "container">
						<div class = "card">
							<a href="userProfileFollow.php?user= <?php echo $rows['posted_by'] ?>"><?php echo $rows['posted_by'].":";?></a>
							<?php echo $rows['description'];?> 
							<br>
							<b>  
							<?php 
								if($rows['sentiment']=='negative') {
									echo '👎';
								}
								else {
									echo '👍';
								}
							?> 
							</b>
							<small><?php echo $rows['cdate'];?></small>
							<?php 
								if (!empty($_GET['ownComment'][0]) && $_GET['ownComment'][0] == $rows["posted_by"])
								{ 
							?>
								<small><a href="editDeleteComment.php?commentid= <?php echo $rows['commentid'] ?>&blogid=<?php echo $rows['blogid'] ?>"> Edit/Delete  </a></small>
							<?php 
								}  
								else if (!empty($_GET['ownComment'][0]) &&$_GET['ownComment'][0] != $rows["posted_by"])
								{ 
							?>
									<small>Community</small>
							<?php 
								} 
							?>
						</div>
					<?php
						}
					?>
					</div>
    </section>
	
	<div class="form-group">
		<textarea name = "comment" placeholder="Comment..." rows="5" cols="35"></textarea>
		<br>
		<br>
		<label for="cars">Choose a sentiment:</label>
		<select name="sentiments" id="sentiments">
			<option value="Positive">Positive</option>
			<option value="Negative">Negative</option>
		</select>
		<br>
		<br>
		<input type="submit" class="btn btn-primary" value="Post" name = "submit">
    </div>
	
	<br>
	<br>
<a href="followerBlogs.php" class="btn btn-secondary">Back to Followed Blogs</a>
	<a href="welcome.php" class="btn btn-secondary">Back to Community Blogs</a>
	<br>
	<br>
	<br>
	<br>	
</form>
<br>

    

</body>
</html>