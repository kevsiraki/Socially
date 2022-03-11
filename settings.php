<?php

include_once 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

session_start();

require_once "config.php";

$sql3 = "SELECT * FROM users WHERE username = '" . $_SESSION['username'] . "' ";
$result3 = mysqli_query($link, $sql3);
$basics = mysqli_fetch_assoc($result3);

if ( (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) ) {
    header("location: login.php"); 
    exit;
}

if(isset($_POST['formSubmit']) && $_POST['del'] == 'Yes') {
	mysqli_query($link, "DELETE FROM hobbies WHERE username = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM follows WHERE leadername = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM follows WHERE followername = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM comments WHERE posted_by = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM blogstags WHERE blogid IN (SELECT blogid FROM blogs WHERE created_by = '" . trim($_SESSION["username"]) . "') ;");
	mysqli_query($link, "DELETE FROM blogs WHERE created_by = '" . trim($_SESSION["username"]) . "';");
	mysqli_query($link, "DELETE FROM users WHERE username = '" . trim($_SESSION["username"]) . "';");
	header("location: logout.php");
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" http-equiv="refresh" content="300;url=logout.php"/> 
		<title>Account Settings</title>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<style>
			body{ width: 360px; padding: 20px; margin-left: auto;
			margin-right: auto; margin-top:10%; font: 14px sans-serif; background-color: #e8f3fd;}
			
		</style>	
	</head>	
	<body>
	<h1>Account Settings</h1><br><br>
	<form  method="post"  >
		<input type="checkbox"  name="2fa" value="Yes" ><b>Toggle 2FA</b></input>
		<input type="submit" name="formSubmit2" value="Update 2FA Status" class="btn-secondary btn-sm" />
		<br>
		<?php
			ob_start();
			if($basics["tfaen"] == 0 && isset($_POST['formSubmit2']) &&$_POST["2fa"]!="Yes") {
				ob_end_clean(); 
				echo "2FA is already Disabled."; 
			}
			else if (isset($_POST['formSubmit2']) && $_POST['2fa']!='Yes' && $basics["tfaen"] == 1 ) {
				ob_end_clean();
				echo "2FA has been Disabled";
				mysqli_query($link, "UPDATE users SET tfaen=0 WHERE username = '" . $basics["username"] . "';");
				mysqli_query($link, "UPDATE users SET tfa='0' WHERE username = '" . $basics["username"] . "';");
			}
			else if($basics["tfaen"] == 1) {
				ob_end_clean(); 
				echo "2FA is already Enabled. Your secret: ".$basics["tfa"];
			}
		?>
		<?php if(isset($_POST['2fa']) && $basics["tfaen"] == 0 && $_POST["2fa"]=="Yes")  : ?>	
			<div class="form-group" id="myDIV">
				<b>
					<label>2FA has been Enabled. <br> Remember this Google Authenticator Code:</label>
					<?php 
						$g = new \Google\Authenticator\GoogleAuthenticator();
						$secret = str_shuffle('XVQ2UIGO75XRUKJ2');
						echo (htmlspecialchars($secret));
						mysqli_query($link,"UPDATE users SET tfaen=1 WHERE username = '".$basics["username"]."';");
						mysqli_query($link,"UPDATE users SET tfa='".$secret ."' WHERE username = '".$basics["username"]."';");   
						$url =  \Google\Authenticator\GoogleQrUrl::generate(urlencode($basics["username"]), urlencode($secret),urlencode("Socially 2FA"));
					?>	
				</b> 
				<br>
				<img src = "<?php echo $url; ?>" alt = "QR Code" />
			</div>
			<?php endif; ?>
	</form>
	<br>
	<form  method="post">
		<input type="checkbox" name="del" value="Yes" > <b>Delete Account</b></input>
		<input type="submit" name="formSubmit" value="Are You Sure?" class="btn-secondary btn-sm" />
	</form>
	<br><br><br><br><br>
	<p>
		<a href="userProfileFollow.php?user= <?php echo $_SESSION['username'] ?>" class="btn btn-info">Back to <?php echo $_SESSION['username'];?></a>
		<br><br><br>
		<a href="logout.php" class="btn btn-secondary" value="Submit"><b>Sign Out</b></a>
		<a href="reset-password.php" class="btn btn-secondary"><b>Reset Your Password</b></a>
	</p>
  </body>
</html>