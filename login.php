<?php
// Initialize the session
session_start();
error_reporting(0);
require_once "config.php";
// Check if the user is already logged in, if yes then redirect him to welcome page
if ((isset($_COOKIE["remember"]) && $_COOKIE["remember"]==1)) {
	$_SESSION["loggedin"] = true;
	$_SESSION["username"] = $_COOKIE["username"];
    header("location: followerBlogs.php");
    exit();
}

$username = $password = $usernameO = "";
$username_err = $password_err = $login_err = $tfa_err = "";

include_once 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    //safely stores all login attempts. tested against SQL injection. dont allow spaces in the log, prevent buffer overlow aswell.
    if (isset($_POST["Submit"]))
    {
        date_default_timezone_set("America/Los_Angeles");
        $date = date("Y-m-d H:i:s"); //trim user inputs,remove spaces to avoid sql injections
		mysqli_query($link, " INSERT INTO all_login_attempts(username, password, attempt_date, ip) 
		VALUES ('" . trim(str_replace(' ', '', $_POST["username"])) . "' , '" . password_hash(trim($_POST["password"]) , PASSWORD_DEFAULT) . 
		"', '" . $date . "', '" . $_SERVER['REMOTE_ADDR'] . "' );");		
	}
    $sql3 = "SELECT * FROM users WHERE username = '" . $_POST['username'] . "' ";
    $result3 = mysqli_query($link, $sql3);
    $basics = mysqli_fetch_assoc($result3);
    // Check if username is empty
    if (empty(trim($_POST["username"])) && isset($_POST["Submit"]))
    {
        $username_err = "Please enter username.";
    }
    else
    {
        $usernameO =$username = trim($_POST["username"]);
    }
    // Check if password is empty
    if (empty(trim($_POST["password"])) && isset($_POST["Submit"]))
    {
        $password_err = "Please enter your password.";
    }
    else
    {
        $password = trim($_POST["password"]);
    }
	$sql4 = "SELECT * FROM users WHERE email = '" . $username . "' ";
	$result4 = mysqli_query($link, $sql4);
	$basics4 = mysqli_fetch_assoc($result4);
	if(!empty($basics4['username'])) {
		$usernameO = $username;
		$username = trim($basics4['username']);
	}
	$sql3 = "SELECT * FROM users WHERE username = '" . $username . "' ";
    $result3 = mysqli_query($link, $sql3);
    $basics = mysqli_fetch_assoc($result3);
	if ($basics["tfaen"] == 1 || $basics4["tfaen"] == 1) {
        $g = new \Google\Authenticator\GoogleAuthenticator();
        $secret = $basics["tfa"];
        $code = trim($_POST["2fa"]);
        if ($g->checkCode($secret, $code) && isset($_POST["Submit"])){}
        else if (!($g->checkCode($secret, $code)) && isset($_POST["Submit"])) {
            if (empty($code) && isset($_POST["Submit"])) {
                $tfa_err = " ";
            }
            else {
                $tfa_err = "Incorrect/Exipired.";
            }
        }
    }else{}
    // Validate credentials
    if (empty($username_err) && empty($password_err) && empty($tfa_err) && isset($_POST["Submit"]))
    {
        // Prepare a select statement
        $sql = "SELECT username, password FROM users WHERE username = ? ";
        $check = mysqli_query($link, "SELECT * FROM users WHERE username =  '$username'; ");
        $name = mysqli_fetch_assoc($check);
        if ($stmt = mysqli_prepare($link, $sql))
        {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            // Set parameters
			$param_username = $username;
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt))
            {
                // Store result
                mysqli_stmt_store_result($stmt);
                //check for 2FA
                if (($basics["tfaen"] == 0 ||  $basics4["tfaen"] == 0 || empty($tfa_err)))
                {
                    // Check if username exists, if yes then verify password
                    if (mysqli_stmt_num_rows($stmt) == 1 )
                    {
                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $username, $hashed_password);
                        if (mysqli_stmt_fetch($stmt) && isset($_POST["Submit"]))
                        {
                            if (password_verify($password, $hashed_password) && !empty($name["email_verified_at"]))
                            {
								if (isset($_POST['remember']) && $_POST['remember'] == 'Yes' )
								{
									setcookie("remember", 1, time() + (86400 * 30)); // 86400 = 1 day
									setcookie("username", $username, time() + (86400 * 30)); // 86400 = 1 day
								}
								// Password is correct and they are verified, so start a new session
								session_start();
								mysqli_query($link, "UPDATE users SET count=count+1 WHERE username = '$username';");
								date_default_timezone_set("America/Los_Angeles");
								$date = date("Y-m-d H:i:s");
								mysqli_query($link, "UPDATE users SET created_at='$date' WHERE username = '$username';");
								// Store data in session variables
								$_SESSION["loggedin"] = true;
								$_SESSION["username"] = $username;
								// Redirect user
								header("location: followerBlogs.php");
                            }
                            else {
                                // Password is not valid, display a generic error message
                                $login_err = "Invalid credentials, or email not verified.";
                            }
                        }
                    }
                    else {
                        // Username doesn't exist, display a generic error message
                        $login_err = "Invalid credentials, or email not verified.";
                    }
                }
            }
            else 
			{
                echo "Oops!";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Socially Login</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
		<style>
			body
			{ 
				font: 14px sans-serif; 
				background-color: #e8f3fd;
			}
			.wrapper
			{ 
				width: 360px; 
				padding: 20px; 
				margin-left: auto;
				margin-right: auto; 
				margin-top:10%; 
			}
			.center 
			{
				display: block;
				margin-left: auto;
				margin-right: auto;
				
			}
			
			input:hover { transform: scale(1.1); }
			
		</style>
	</head>
	<body>
		<div class="wrapper" >
		<h2><img src="logo.png" width="100" height="100" class="center" style="opacity:0.8;border-radius: 25px;padding: 20px; "></img></h2>
		
		<?php 
			if(!empty($login_err)){
				echo '<div class="alert alert-danger">' . $login_err . '</div>';
			}?> 
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
				<div class="form-group">
					<label class = "form-text">Username or Email</label>
					<input type="text" name="username" class="form-control 
						<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $usernameO; ?>">
					<span class="invalid-feedback"> <?php echo $username_err; ?> </span>
				</div>
				<div class="form-group">
					<label class = "form-text">Password</muted></label>
					<input type="password" name="password" class="form-control 
						<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"value="<?php echo $password; ?>">
					<span class="invalid-feedback"> <?php echo $password_err; ?> </span>
				</div>
				<?php if((isset($basics["tfaen"])||isset($basics4["tfaen"])))  : ?>
					<?php if($basics["tfaen"]==1 || $basics4["tfaen"] == 1): ?>
						<div class="form-group">
							<label>2FA Google Authenticator Code</label>
							<input type="2fa" name="2fa" id="2fa" class="form-control
							<?php echo (!empty($tfa_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $code; ?>">
							<span class="invalid-feedback"> <?php echo $tfa_err; ?> </span>
							<small id="2faHelp" class="form-text text-muted">2FA is Enabled.</small>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				<div class="form-group">
					<input type="checkbox"  name="remember" value="Yes" 
					<?php if($_POST["remember"]=='Yes'):?> checked <?php endif; ?>> Remember me</input>
				</div>
				
				<input name="Submit" type="submit"  value="Login" class="center btn btn-primary"> </input>
				<br>
				<a href="register.php" style="">Sign-Up</a>
				<br><br><br>
				<a href="fu.php" style="white-space: nowrap;">Forgot User</a>
				&nbsp;
				<a href="fp.php" style="white-space: nowrap;">Forgot Pass</a>
		</div>
		</form>
		</div>
	</body>
</html>