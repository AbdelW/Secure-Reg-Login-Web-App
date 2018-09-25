  <?php

//doing mailing through composer
require './vendor/autoload.php';




//function to help us clean our url of weird symbols
function clean ($string)
{
	return htmlentities($string);
}

//function to help us redirect 
function redirect ($location)
{
	return header("Location: {$location}");
}

//function to help us declare messages to our users
function set_message($message)
{
	if(!empty($message))
	{
		$_SESSION['$message'] = $message;
	}
	else
	{
		$message = "";
	}
}

//function to help us display above mentioned messages
function display_message()
{
	if(isset($_SESSION['$message']))
	{
		echo $_SESSION['$message'];
		//unset($_SESSION['$messsage']);
		session_unset();
	}
}

//funtion to help us generate tokens for security reasons
function token_generator()
{
	$token = $_SESSION['token'] = md5(uniqid(mt_rand(), true));
	return $token;
}

//funtion to help us show bootstrap error message
function validation_errors($error_message)
{
	$error_message = <<<DELIMITER
	<div class="bs-error">
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <strong>Error!</strong>
         $error_message 
    </div>
</div>	
DELIMITER;
return $error_message;
}

//function to help us to activate email
function send_email($p_email=null, $subject=null, $msg=null, $headers=null )
{
		//Server settings
		$mail = new PHPMailer\PHPMailer\PHPMailer();
	//	$mail->SMTPDebug = 2;                                 // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host =     Config::SMTP_HOST;                  // Specify main and backup SMTP servers
		$mail->Username = Config::SMTP_USERNAME;              // SMTP username
		$mail->Password = Config::SMTP_PASSWORD;              // SMTP password
		$mail->Port =     Config::SMTP_PORT;                  //SMTP port
		$mail->SMTPAuth = true;                               // Enable SMTP authentication   
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		//$mail->CharSet='UTF-8';
		$mail->setFrom('abdelw@mymail.com', 'Abdel Wedoud Oumar');
		$mail->addAddress('email@email.com');
		    //Content
			//$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $msg;
			$mail->AltBody = $msg;
		
			if(!$mail->send())
			{
				//return false;
				echo "Email could not be sent.";
				echo "Mailer Error: " . $mail->ErrorInfo;
			}
			else 
			{
				echo "<p class='bg-success text-center'>Email has been sent. Please check your email inbox</p>";
			}
		// 	$mail->send();
		// 	echo 'Message has been sent';
		// } catch (Exception $e) {
		// 	echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
		// }




	// return mail($p_email, $subject, $msg, $headers );
}

//function to help us to deter duplicate emails
function email_exists($p_email)
{
	$sql = "SELECT p_id FROM patients WHERE p_email = '$p_email'";
	$result = query($sql);
	if(row_count($result) == 1)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//funtion to help us to deter duplicate names
function name_exists($p_name)
{
	$sql = "SELECT p_id FROM patients WHERE p_name = '$p_name'";
	$result = query($sql);
	if(row_count($result) == 1)
	{
		return true;
	}
	else
	{
		return false;
	}
} 

//function to help us check wether the inputs are empty
function validate_user_registration()
{
	$errors = [];
	$min = 3;
	$max = 20;
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		$p_name   = clean($_POST['p_name']);
		$p_pass   = clean($_POST['p_pass']);
		$p_repass = clean($_POST['p_repass']);
		$p_gender = clean($_POST['p_gender']);
		$p_email  = clean($_POST['p_email']);

		if(name_exists($p_name))
		{
			$errors[] = "Name already exists, Please enter a different Name";
		}

		if(strlen($p_name) <$min)
		{
			$errors[] = "Your name can't be too short or less than {$min} characters";
		}

		if(strlen($p_name) >$max)
		{
			$errors[] =  "Your name can't be too long or more than {$max} characters";
		}

		if(email_exists($p_email))
		{
			$errors[] = "Email already exists, Please enter a different email";
		}

		if(empty($p_email))
		{
			$errors[] = "you must provide your mail";
		}

		if($p_pass !== $p_repass)
		{
			$errors[] = "your passwords don't match";
		}

		if(!empty($errors))
		{
			foreach ($errors as $error) 
			{
				echo validation_errors($error);
			}
		}
		else
		{
			if(register_user ($p_name, $p_email, $p_pass, $p_repass, $p_gender))
			{
				//echo "You've Registered Successfully";
				set_message("<p class ='bg-success text-center'>Please check your mail for an activation link.</p>");
				redirect("index.php");
			
			}
			else
			{
				set_message("<p class ='bg-success text-center'>Please check if you've registered.</p>");
				redirect("index.php");
			}
		}
	}
}

//funtion to help us insert the data 
function register_user ($p_name, $p_email, $p_pass, $p_repass, $p_gender)
{
		$p_name   = escape($p_name);
		$p_pass   = escape($p_pass);
		$p_repass = escape($p_repass);
		$p_gender = escape($p_gender);
		$p_email  = escape($p_email);

	if(email_exists($p_email))
	{
		return false;
	}
	elseif (name_exists($p_name)) 
	{
		return false; 
	}
	else
	{
		$p_pass = md5($p_pass);
		$p_repass = md5($p_repass);
		// $p_pass = password_hash($p_pass, PASSWORD_BCRYPT, array('cost'=>12));
		// $p_repass = password_hash($p_repass, PASSWORD_BCRYPT, array('cost'=>12));
		$p_valid_code = md5($p_name . microtime());
		$sql = "INSERT INTO patients (p_name,  p_gender, p_pass, p_repass, p_email,  p_valid_code, p_active) ";
		$sql.= " VALUES ('$p_name', '$p_gender','$p_pass','$p_repass','$p_email','$p_valid_code','0')";
		$result = query($sql);
		confirm($result);
		$subject = "Activate Your Account!!";
		$msg = "Click on the <a href='".Config::localurl."activate.php?p_email=$p_email&p_valid_code=$p_valid_code'>Link</a> to acivate your account with us.";
		$headers = "From: noreply@yourwebsite.com";
		send_email($p_email, $subject, $msg, $headers);
		return true;

	}

}

//function to help us activate user
function activate_user()
{
	if($_SERVER['REQUEST_METHOD'] == "GET")
	{
		if(isset($_GET['p_email']))
		{
			$p_email = clean($_GET['p_email']);
		    $p_valid_code = clean($_GET['p_valid_code']);
		    $sql = "SELECT p_id FROM patients WHERE p_email = '".escape($_GET['p_email'])."' AND p_valid_code = '".escape($_GET['p_valid_code'])."' ";
		    $result = query($sql);
		    confirm($result);
		    if(row_count($result) == 1)
		    {
		    	$sql2 = "UPDATE patients SET p_active = 1, p_valid_code = 0 WHERE p_email = '".escape($p_email)."' AND p_valid_code = '".escape($p_valid_code)."'";
		    	$result2 = query($sql2);
		    confirm($result2);
		    	set_message( "<p class='bg-success'>Your Account has been activated, Please log-in</p>");
		    	redirect("login.php");
		    }
		    else
		    {
		    	set_message( "<p class='bg-danger'>Your Account was not activated, Please try again!</p>");
		    	redirect("login.php");
		    }
		    
		}
	}
}

//function to help us validate login
function validate_user_login()
{
	$errors = [];
	$min = 3;
	$max = 20;
	if ($_SERVER['REQUEST_METHOD'] == "POST") 
	{

	 $p_email  = clean($_POST['p_email']);
	 $p_pass   = clean($_POST['p_pass']);
	 $p_remember   = isset($_POST['p_remember']);

		if (empty($p_email)) 
		{
			$errors[] = "Please enter your email";
		}
		if (empty($p_pass)) 
		{
			$errors[] = "Please enter your password";
		}
		if(!empty($errors))
		{
			foreach ($errors as $error) 
			{
				echo validation_errors($error);
			}
		}
		else
		{
			if(login_user($p_email, $p_pass, $p_remember))
			{
				redirect("admin.php");
			}
			else
			{
				echo $p_pass;
				echo "<br>";
				echo validation_errors("Your credentials are not correct");
			}
		}
	}
}

//funtion to help us login the user from the db
function login_user($p_email, $p_pass, $p_remember)
{
	$sql = "SELECT p_pass, p_id FROM patients WHERE p_email = '".escape($p_email)."' AND p_active = 1";
	$result = query($sql);
	if(row_count($result) == 1)
	{
		$row = fetch_array($result);
		$db_password = $row['p_pass'];
		if(md5($p_pass) == $db_password)
		{
			if($p_remember == "on")
			{
				setcookie('p_email', $p_email, time() + 60);
			}

			$_SESSION['p_email'] = $p_email;
			return true;
		}
		else
		{
			return false; 
		}

		// if(password_verify($p_pass, $db_password))
		// {
		// 	if($p_remember == "on")
		// 	{
		// 		setcookie('p_email', $p_email, time() + 86400);
		// 	}
		// 		$_SESSION['p_email'] = $p_email;
		// 		return true;
		// }
	 //    else
		// {
		// 	return false;
		// }
	}
}

//funtion to help us session the login
function logged_in()
{
	if(isset($_SESSION['p_email']) || isset($_COOKIE['p_email']))
	{
		return true;
	}
	else
	{
		return false;
	}
}

//function to help us recover user password
function recover_password()
{
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		if(isset($_SESSION['token']) && $_POST['token'] === $_SESSION['token'])
		{
		 	$p_email  = clean($_POST['p_email']);
		 //	$p_email = isset($_POST['p_email']);

			if(email_exists($p_email))
			{
				$p_valid_code = md5($p_email . microtime());
				setcookie('temp_access_code', $p_valid_code, time()+900);
				$sql = "UPDATE patients SET p_valid_code = '".escape($p_valid_code)."' WHERE p_email = '".escape($p_email)."'";
				$result = query($sql);
				confirm($result);

				$subject = "Reset your password";
				$message = "Here is the password reset code: 
				<b>
				{$p_valid_code}
				<b>
				Click <a href='".Config::localurl."code.php?p_email={$p_email}&p_valid_code={$p_valid_code}'>here</a> to reset your password";
				$headers = "From: noreply@yourwebsite.com";


				// if(!send_email($p_email, $subject, $message, $headers))
				// {
				// 	echo validation_errors("The email could not be sent.");
			 //    }
				send_email($p_email, $subject, $message, $headers);
			    set_message("<p class='bg-success text-center'>Email has been sent for password reset code.</p>");
			    //redirect("index.php");
			    
				
			}
			else
			{
				echo validation_errors("This email doesn't exist");
			}
		}
		else
		{
			redirect("index.php");
		}
		
		if (isset($_POST['cancel_submit'])) 
		{
			redirect("login.php");
		}
	}
}

//function to help us send code
function validate_code()
{
	if(isset($_COOKIE['temp_access_code']))
	{
	//	if($_SERVER['REQUEST_METHOD'] == "GET")
	//	{
			if (!isset($_GET['p_email']) && !isset($_GET['p_valid_code']))
			{
			// $p_email = clean($_GET['p_email']);
			 //$p_valid_code = clean($_GET['p_valid_code']);
				redirect("index.php");
			}
			elseif (empty($_GET['p_email']) || empty($_GET['p_valid_code'])) 
			{
				redirect("index.php");
			}
	//	}
		else
		{
			if (isset($_POST['p_code'])) 
			{
				$p_email = clean($_GET['p_email']);
				$p_valid_code = clean($_GET['p_valid_code']);
				$sql = "SELECT p_id FROM patients WHERE p_valid_code = '".escape($p_valid_code)."' AND p_email='".escape($p_email)."'";
				$result = query($sql);
				confirm($result);
				if (row_count($result) == 1) 
				{
					setcookie('temp_access_code', $p_valid_code, time()+300);
					redirect("reset.php?p_email=$p_email&p_valid_code=$p_valid_code");
				}
				else
				{
					echo validation_errors("Sorry wrong validation code");
				}

			}
		}
	}
	else
	{
		set_message("<p class='bg-danger text-center'>Sorry, Your session has expired</p>");
			 //   redirect("index.php");
		redirect("recover.php");
	}
}

//funtion to help us rest the password
function password_reset()
{
	if (isset($_GET['p_email']) && isset($_GET['p_valid_code'])) 
	{

		if (isset($_SESSION['token']) && isset($_POST['token'])) 
		{
			if ($_POST['token'] === $_SESSION['token']) 
			{
				if($_POST['reset_password'] === $_POST['reset_confirm_password'])
				{
					$p_email = clean($_GET['p_email']);
					$updated_password = md5($_POST['reset_password']);
					$sqlu = "UPDATE patients SET p_pass ='".escape($updated_password)."',p_repass ='".escape($updated_password)."', p_valid_code = 0, p_active = 1 WHERE p_email = '".escape($p_email)."'";
					query($sqlu);
					echo "$updated_password";
					confirm($sqlu);
					set_message("<p class='bg-success text-center'>your password has been updated. Please login again.</p>");
					redirect("login.php");
				}
				else
				{
					echo validation_errors("passwords don't match");
				}
				
			}
		}
	}
	else
	{
		set_message("<p class='bg-danger text-center'>Sorry but the vaild code has expired</p>");
	}
}


?>