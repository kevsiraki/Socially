<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

$empty_field = "";
$todaysDate = date("Y/m/d");
$dailyLimit = mysqli_query($link,"SELECT COUNT(pdate) FROM blogs WHERE created_by = '".$_SESSION['username']."' AND pdate = '".$todaysDate."'");
$_GET['sum'] = mysqli_fetch_array($dailyLimit);
$ownLimit = mysqli_query($link, "SELECT (created_by) FROM blogs WHERE created_by = '" . $_SESSION['username']."'");
$_GET['ownBlog'] = mysqli_fetch_array($ownLimit);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	if(($_GET['sum'][0]<=1)&& isset($_POST["post"]) && !(empty(trim($_POST["subject"])) || empty(trim($_POST["description"])) || empty(trim($_POST["tags"]))))
	{
        // Prepare an insert statement
        $sql = "INSERT INTO blogs (blogid, subject, description, pdate, created_by) VALUES (?, ?, ?, ?, ?)";
        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "issss",$id, $param_subject, $param_description, $param_date, $param_username);
            
            // Set parameters
            $param_subject = $_POST["subject"];
			$param_description = $_POST["description"];
			$param_date = date("Y/m/d");
			$param_username = $_SESSION["username"];
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect 
            } else{
                echo "Oops.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
       $id = mysqli_insert_id($link);
		//echo($id);
		$tags_arr = explode (",", $_POST["tags"]); 
        // Prepare an insert statement
		for ($i = 0; $i < count($tags_arr); $i++) {
        $sql = "INSERT INTO blogstags (blogid, tag) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "is", $id, $param_tags);
            // Set parameters
			$param_tags = preg_replace("/\s+/", "",$tags_arr[$i]);
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect 
				 header("location: welcome.php");
            } else{
                echo "Oopsie Doopsie.";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
		}
	}	
}
$categories = mysqli_query($link, "SELECT DISTINCT tag  FROM blogstags");
$result = mysqli_query($link, "SELECT * FROM blogs INNER JOIN blogstags ON blogstags.blogid = blogs.blogid");
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="style.css">
		<style>

#blueDIV {
  position: fixed;
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
<body >
<section>
<title>Community Posts</title>
<a href = "userProfileFollow.php?user=<?php echo $_SESSION["username"] ?> ">
<h1 class="my-5"><b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></h1>
</a>
	<form  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<div id="blueDIV"><u>Categories:</u><br>
  <?php   
				$previousID = "";
                while($rows=mysqli_fetch_assoc($categories))
                {
					
					?>
					
					<a href="tags.php?tag=<?php echo nl2br($rows['tag']); ?>" ><?php echo nl2br("#".$rows['tag']); ?></a>
					<br>
					<?php 
				}
				?>
</div>
	<div class="form-group">
         <textarea  wrap = "hard" rows="2" cols="30" placeholder = "Subject..." name="subject" ></textarea>
    </div>   
	<div class="form-group">
          <textarea wrap = "hard" rows="4" cols="30" placeholder = "Description..." name="description"  ></textarea>
    </div>   
	<div class="form-group"> 
         <textarea wrap = "hard" rows="2" cols="30" placeholder = "Tag 1, Tag 2, Tag n..." name="tags" ></textarea>
    </div>   
	<div class="form-group">
        <input type="submit" class="btn btn-primary" value="Post" name = "post">
    </div>
	    
        <h1>Community Posts</h1>
		<a class="btn btn-info" href ="followerBlogs.php">Switch to Followed Posts</a>
		<br>
		<br>
            <?php   
				$previousID = "";
                while($rows=mysqli_fetch_assoc($result))
                {
					if(!empty($result)) {
             ?>
				<?php if($previousID != $rows['blogid']): ?>
					
					<?php 
					$alreadyFollowing = mysqli_query($link, "SELECT leadername,followername FROM follows WHERE 
					leadername = '" . $rows['created_by']."' AND followername = '" . $_SESSION["username"] . "'");
					$_GET['afol'] = mysqli_fetch_array($alreadyFollowing);
					 ?>
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
					 if (!empty($_GET['ownBlog'][0]) && $_GET['ownBlog'][0] == $rows["created_by"]) {
						?>
						<a href="editDeleteBlog.php?blogid=<?php echo $rows['blogid']?>">Edit/Delete</a>
						<?php
						}
					 else if (!empty($_GET['afol'][0])) {
						?>
						<small> Following </small>
						<?php
						}
						else {
						?>
						<small>Community</small>
						<?php
						}
						?>	
     </div>
    </div>
  </div><br>
					<?php endif; ?>
			<?php
				$previousID =$rows['blogid'];
						}
                }
				mysqli_close($link);
			?>
    </section>
	<a href="phase3.php" style = "position:relative; top:10px;" class="btn btn-info">Search for Users</a>
		<br><br><br><br>

        <a href="logout.php" class="btn btn-secondary">Sign Out of Your Account</a>
		<br><br><br><br>
</form>

</body>
</html>