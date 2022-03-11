<?php 
error_reporting(0);
session_start();
// Include config file
require_once "config.php";
include_once 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include_once 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';
// Define variables and initialize with empty values
$email = $ans = $us = $password =$password_err = $ans_err = $code = $tfa_err = "";

    //the list of questions
    $array = array(
        "1" => "What is your mother's maiden name?",
        "2" => "What is your favorite pet's name?",
        "3" => "What city was your first job in?",
        "4" => "Where did you go to for 6th grade?",
        "5" => "Who was your 3rd grade teacher?",
        "6" => "What was your childhood nickname?"
    );
if(isset($_GET["key"])) {
$email = trim($_GET["key"]);
//get the users selected question:
    $sql4 = "SELECT * FROM users WHERE email = '" . $_GET["key"] . "' ";
    $result4 = mysqli_query($link, $sql4);
    $question = mysqli_fetch_assoc($result4);
    $index = $question["ques"];//echo 1;
}    
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$email = $_POST["email"];
	$sql4 = "SELECT * FROM users WHERE email = '" . $email . "' ";
    $result4 = mysqli_query($link, $sql4);
    $question = mysqli_fetch_assoc($result4);
    $index = $question["ques"];//echo 1;
    if (empty(trim($_POST["answer"])))
    {
       $ans_err = "Please enter an answer.";
    }
    else if (!(password_verify(trim(strtolower($_POST["answer"])) , $question['ans'])))
    {
        $ans_err = "Incorrect answer.";
    }
    else
    {
        $ans = trim($_POST["answer"]);	
    }
	$sql3 = "SELECT * FROM users WHERE email = '" . $email . "' ";
    $result3 = mysqli_query($link, $sql3);
    $basics = mysqli_fetch_assoc($result3);
	if ($basics["tfaen"] == 1 )
    {
        $g = new \Google\Authenticator\GoogleAuthenticator();
        $secret = $basics["tfa"];
        $code = trim($_POST["2fa"]);
        if ($g->checkCode($secret, $code) && isset($_POST["submit"]))
        {
			$us = $question[1];
        }
        else if (!($g->checkCode($secret, $code)) && isset($_POST["submit"]))
        {
            if (empty($code) && isset($_POST["submit"]))
            {
                $tfa_err = " ";
            }
            else
            {
                $tfa_err = "Incorrect/Exipired.";
            }
        }
    }
	if ($basics["tfaen"] == 0 && empty($ans_err)) {
		$us = $question['username'];
	}
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Retrieve Username</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; background-color: #e8f3fd;}
        .wrapper{ width: 360px; padding: 20px; margin-left: auto;
			margin-right: auto; margin-top:10%; }
			label:hover { transform: scale(1.2); }
a:hover { transform: scale(1.1); }
meter:hover { transform: scale(1.5); }
select:hover { transform: scale(1.1); }
input:hover { transform: scale(1.2); }
textarea:hover { transform: scale(1.2); }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Retrieve Username</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"   method="post"> 
		<div class="form-group">
	<input type="hidden" name="key2" value="<?php echo htmlspecialchars($email);?>">
		<label>Question: <?php echo nl2br(htmlspecialchars($array[$index])); ?></label>
          <input type="answer" name="answer" class="form-control <?php echo (!empty($ans_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $ans; ?>">
                <span class="invalid-feedback"><?php echo $ans_err; ?></span>
        </div>
		<?php if($basics["tfaen"]==1 || $question['tfaen']==1)  : ?>
			<div class="form-group">
          <label>2FA Google Authenticator Code</label>
          <input type="2fa" name="2fa" id="2fa" class="form-control
		  <?php echo (!empty($tfa_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $code; ?>">
        <span class="invalid-feedback"> <?php echo $tfa_err; ?> </span>
		<small id="2faHelp" class="form-text text-muted">2FA is Enabled.</small>
        </div>
		<?php endif; ?>
		<input type="hidden" name="email" value="<?php echo $email;?>"/>
		<?php if (empty($ans_err) && empty($tfa_err) && isset($_POST["submit"]) ): ?>
		<a class="btn btn-secondary btn-sm" href="login.php">Username: <?php echo nl2br(htmlspecialchars($us)); ?> 
		<small> Login</a>
		</small>
		</a><br><br>
		<?php endif; ?>
            <div class="form-group">
			
                <input type="submit" name="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link ml-2" href="login.php">Cancel</a>
            </div>
        </form>
    </div> 
</body>
</html>