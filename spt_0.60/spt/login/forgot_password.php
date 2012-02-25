<?php
/**
 * file:		forgot_password.php
 * version:		4.0
 * package:		Simple Phishing Toolkit (spt)
 * component:	Core files
 * copyright:	Copyright (C) 2011 The SPT Project. All rights reserved.
 * license:		GNU/GPL, see license.htm.
 * 
 * This file is part of the Simple Phishing Toolkit (spt).
 * 
 * spt is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, under version 3 of the License.
 *
 * spt is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with spt.  If not, see <http://www.gnu.org/licenses/>.
**/

	//this is reset password script
	//allows for a temporary bypass of authentication with a random generated script

	//start the session
	session_start();

	if(isset($_REQUEST['key']))
		{
			//pull in key
			$key = $_REQUEST['key'];

			//connect to the database
			include '../spt_config/mysql_config.php';

			//pull in all relevant data
			$r = mysql_query('SELECT * FROM users') or die('<div id="die_error">There is a problem with the database...please try again later</div>');
			while($ra = mysql_fetch_assoc($r))
				{
					$u = $ra['username'];
					$admin = $ra['admin'];
					if($ra['disabled']==0)
						{
							if(date("Y-m-d") == $ra['preset_day'])
								{
									if($ra['preset_enabled']==1)
										{
											if($key == $ra['preset_key'])
												{
													//pull the unique salt value
													include 'get_salt.php';

													//set an ip session variable with a salt to avoid session hijacking
													$_SESSION['ip']=md5($_SESSION['salt'].$_SERVER['REMOTE_ADDR'].$_SESSION['salt']);

													//create an authenticated session
													$_SESSION['authenticated']=1;

													//create a username session
													$_SESSION['username']=$u;

													//check to see if they are an admin
													if($admin==1)
														{
															//create an admin session
															$_SESSION['admin']=1;
														}
													
													//disable key
													mysql_query("UPDATE users SET preset_enabled = '0' WHERE username = '$u'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');

													//send them to their edit user page
													header('location:../users/#edit_user');
													exit;
												}
										}
								}
						}
				}
			
			//if they haven't logged in by now shoot them back to the login page
			$_SESSION['alert_message']="your key is not valid";
			header('location:../#alert');
			exit;
		}
	else
		{
			//ensure that something was posted
			if(!isset($_POST['email']))
				{
					$_SESSION['alert_message'] = "you must enter an email address";
					header('location:../#alert');
					exit;
				}

			//pull in entered email address
			$email = $_POST['email'];

			//validate email address
			if(!filter_var($email, FILTER_VALIDATE_EMAIL))
				{
					$_SESSION['alert_message'] = "you must enter a valid email address";
					header('location:../#alert');
					exit;
				}
			
			//validate that the username is not too long
			if(strlen($email) > 50)
				{
					$_SESSION['alert_message']="you must enter a valid email address";
					header('location:../#alert');
					exit;
				}

			//connect to the database
			include '../spt_config/mysql_config.php';

			//pull in all email addresses
			$match = 0;
			$r = mysql_query("SELECT username FROM users") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
			while($ra=mysql_fetch_assoc($r))
				{
					if($email == $ra['username'])
						{
							$match = 1;
						}
				}

			//if the email address entered matches a username then proceed with the password reset	
			if($match == 1)
				{
					//get today's date for the window in which the password reset will be allowed
					$today = date("Y-m-d");

					//generate a random key
					$random_number = mt_rand(1000000000,9999999999);
					$key = sha1($random_number);

					//mark the specified username for password reset
					mysql_query("UPDATE users SET preset_day = '$today', preset_key = '$key', preset_enabled = 1 WHERE username = '$email'");

					//prep the email
					$subject = 'SPT - Forgot Your Password?';

					//This will populate the headers of the message
					$headers = "From: no-reply@sptoolkit.com\r\n";
					$headers .= "Reply-To: no-reply@sptoolkit.com\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
					
					//This will populate the body of the email
					$message = '<html><body>';
					$message .= '<br>This email was generated because you have initiated the forgot password process with SPT.  The link below is a one-time use link that will only work once and will only work for the remainder of the day.  Once you follow the link, please change your password immediately.<br><br>If you did not generate this link, please follow the link to expire this one-time link and ensure that no one else can use it.';
					$message .= '<br><br><br>';
					
					//pull current host and path
					$path = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					$path .= "?key=".$key;
					
					//finish the message
					$message .= '<a href="http://'.$path.'">One-Time Login</a>';
					$message .= '<br><br><br>';
					$message .= '</body></html>';
			
					//send the email
					mail($email, $subject, $message, $headers);

					//send back to login page with instructions to check their email
					$_SESSION['alert_message'] = "check your email for a one-time use link that will allow you to login and reset your password";
					header('location:../#alert');
					exit;

				}

			//if the email address entered does not match an existing username then send them back
			else
				{
					$_SESSION['alert_message']="you must enter a valid email address";
					header('location:../#alert');
					exit;
				}
		}
 
 ?>