<?php
   if(!isset($_SERVER['HTTP_REFERER'])){
    // redirect them to your desired location
    header('location: login.php');
    exit;
}
// Initialize the session
// Include config file
require_once "config.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Define variables and initialize with empty values
$username = $email = $new_password = $confirm_password = $ans = "";
$new_password_err = $confirm_password_err = $email_err = $username_err = $ans_err = "";


// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
 
    // Check if email is valid
    if (empty(trim($_POST["email"])))
    {
        $email_err = "Please enter email.";
    }
    else
    {
        $email = trim($_POST["email"]);
        $query = mysqli_query($link, "SELECT * FROM users WHERE email='" . $email . "'");
        if (!$query)
        {
            die('Error: ' . mysqli_error($link));
        }
        if (mysqli_num_rows($query) == 0)
        {
            $email_err = "Email not found.";
        }
        else{}
    }

    // Check if email is validated
    $sql3 = "SELECT * FROM users WHERE email = '" . $email . "' ";
    $result3 = mysqli_query($link, $sql3);
	$basics = mysqli_fetch_assoc($result3);
	
    if (empty($basics["email_verified_at"]) && empty($email_err))
    {
        $email_err = "Email not verified. Check your email.";
    }

	if(empty($email_err)) {
	            //sends email
            $link2 = "<a href='donttrip.technologists.cloud/final/forgot-username.php?key=" . $_POST["email"] . "&token=" . $basics["email_verification_link"] . "'>Retrieve Username</a>";
            require "phpmail/src/Exception.php";
            require "phpmail/src/PHPMailer.php";
            require "phpmail/src/SMTP.php";
            $mail = new PHPMailer(true);
            try
            {
                $mail->CharSet = "utf-8";
                $mail->IsSMTP();
                // enable SMTP authentication
                $mail->SMTPAuth = true;
                // GMAIL username
                $mail->Username = "compsciemail123@gmail.com";
                // GMAIL password
                $mail->Password = "comp424smtp123*";
                $mail->SMTPSecure = "ssl";
                // sets GMAIL as the SMTP server
                $mail->Host = "smtp.gmail.com";
                // set the SMTP port for the GMAIL server
                $mail->Port = "465";
                $mail->From = "compsciemail123@gmail.com";
                $mail->FromName = "WebMaster";
                //$mail->AddAddress('reciever_email_id', 'reciever_name');
                //var_dump($email);
                $mail->addAddress(trim($_POST["email"]), "kevin");
                $mail->Subject = "Retrieve your Username";
                $mail->IsHTML(true);
                date_default_timezone_set("America/Los_Angeles");
                $date = date("Y-m-d H:i:s");
                $greeting = "";
                if (date('H') < 12)
                {
                    $greeting = "Good morning";
                }
                else if (date('H') >= 12 && date('H') < 18)
                {
                    $greeting = "Good afternoon";
                }
                else if (date('H') >= 18)
                {
                    $greeting = "Good evening";
                }
                $mail->Body = " " . $greeting . " " . $basics["first_name"] . ". Click On This Link to Retrieve Username: " . $link2 . ". (Note: If this isn't you, ignore this email).";
            }
            catch(phpmailerException $e)
            {
                echo $e->errorMessage();
            }
            catch(Exception $e)
            {
                header("location: login.php"); //Boring error messages from anything else!
                
            }
            if ($mail->Send())
            {
				//session_start();
				//$_SESSION["email"] = trim($_POST["email"]);
                header("location: login.php");
            }
            else
            {
                echo "Mail Error - >" . $mail->ErrorInfo;
            }
	}
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
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
		<div class="form-group">
          <label for="exampleInputEmail1">Email address: </label>
          <input type="email" name="email"  id="email" required="" aria-describedby="emailHelp" class="form-control
		  <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
          <span class="invalid-feedback"> <?php echo $email_err; ?> </span> 
		  <small id="emailHelp" class="form-text text-muted">Retrieve Username Link will be sent to your email (if verified).</small>
        </div>
		<b> 
		<div class="form-group">
                <input type="submit" name="submit" class="btn btn-primary" value="Submit">
                <a class="btn btn-link ml-2" href="login.php">Cancel</a>
            </div>
        </form>
    </div>    
</body>
</html>