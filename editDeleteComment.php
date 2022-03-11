<?php
if(!isset($_SERVER['HTTP_REFERER'])){
    // redirect them to your desired location
    header('location: logout.php');
    exit;
}

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

if(isset($_GET["commentid"])) {
	$commentid = trim($_GET["commentid"]);
	$result = mysqli_query($link, "SELECT * FROM comments WHERE commentid = '" . $commentid . "'");
	$blogid = trim($_GET["blogid"]);
}
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$commentid = $_POST['commentid'];
	$blogid = $_POST['blogid'];
    $dailyLimit = mysqli_query($link, "SELECT COUNT(cdate) FROM comments WHERE posted_by = '" . $_SESSION['username'] . "' AND cdate = '" . $todaysDate . "'");
    $_GET['sum'] = mysqli_fetch_array($dailyLimit);
    $blogLimit = mysqli_query($link, "SELECT COUNT(commentid) FROM comments WHERE posted_by = '" . $_SESSION['username'] . "'AND commentid = '" . $_POST['commentid'] . "'");
    $_GET['sumC'] = mysqli_fetch_array($blogLimit);
    $result = mysqli_query($link, "SELECT * FROM blogs WHERE commentid = '" . $commentid . "'");
    $resultC = mysqli_query($link, "SELECT * FROM comments WHERE commentid = '" . $commentid . "'");
	$sentiment = "";
    if ($_POST["sentiments"] == "Positive") 
	{
        $sentiment = "positive";
    }
    else 
	{
        $sentiment = "negative";
    }
	if(!empty(trim($_POST["description"]))) 
	{
		$sql = "UPDATE comments SET description = (?), sentiment = (?) WHERE commentid = '".$commentid."' ";
	    if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss",$param_description,$param_sentiment );
            // Set parameters
			$param_description = $_POST["description"];
			$param_sentiment = $sentiment;
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect 
				header("location: comment.php?blogid=".$blogid);
            } else{
                echo "Oops.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
	}
	if(isset($_POST["delete"]))
	{
		mysqli_query($link, "DELETE FROM comments WHERE commentid = '".$commentid."'");
		header("location: comment.php?blogid=".$blogid);
	}
$result = mysqli_query($link, "SELECT * FROM comments WHERE commentid = '" . $commentid . "'");
mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
	    <style>
a:hover { transform: scale(1.1); }
meter:hover { transform: scale(1.5); }
select:hover { transform: scale(1.1); }
input:hover { transform: scale(1.1); }
textarea:hover { transform: scale(1.08); }
    </style>
</head>
	<body>
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<input type="hidden" name="commentid" value="<?php echo $commentid;?>"/>
		<input type="hidden" name="blogid" value="<?php echo $blogid;?>"/>		
		<section>
			<h1>Edit Comment</h1>
			<div class = "card">
				<div class = "container">      
						<?php   
							while($rows=mysqli_fetch_assoc($result))
							{
						?>
								<textarea rows="4" cols="50" name = "description" placeholder = "Comment Body..."><?php echo ($rows['description']);?></textarea>
						<?php
							}
						?>
					<label for="cars">Edit sentiment:</label>
					<select name="sentiments" id="sentiments">
						<option value="Positive">Positive</option>
						<option value="Negative">Negative</option>
					</select>
				</div>
			</div> 
		</section>
		<br>
		<br>
		<div class="form-group">
			<input type="submit" class="btn btn-primary" value="Edit and Submit" name = "post">
			<input type="submit" class="btn btn-primary" value="Delete" name = "delete">
		</div>        
		<br>    
	</form>
	<p>
		<a href="followerBlogs.php" class="btn btn-secondary">Back to Followed Blogs</a>
		<a href="welcome.php" class="btn btn-secondary">Back to Community Blogs</a>
	</p>
	</body>
</html>