<?php
/**
 * file:		index.php
 * version:		2.0
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

	//starts php session
	session_start();
	
	//if install file exists prompt user to delete it
	if(isset($_POST['delete_install']))
		{
			unlink('install.php');	
		}

	if(file_exists('install.php') && !preg_match('/installfiles=true/', $_SERVER['REQUEST_URI']))
		{
			header('location:index.php?installfiles=true#alert');
		}

	//sends you to the spt dashboard if your already authenticated
	if(isset($_SESSION['authenticated']))
		{
			header('location:dashboard/');
			exit;
		}

?>
<!DOCTYPE HTML> 
<html>
	<head>
		<title>spt - simple phishing toolkit</title>
		
		<!--meta-->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="description" content="welcome to spt - simple phishing toolkit.  spt is a super simple but powerful phishing toolkit." />
		
		<!--favicon-->
		<link rel="shortcut icon" href="images/favicon.ico" />
		
		<!--css-->
		<link rel="stylesheet" href="spt.css" type="text/css" />
	</head>
	
	<body>
		
		<!--login wrapper -->
		<div id="login_wrapper">
			
			<!--logo-->
			<div id="login_logo"><img src="images/logo.png" alt="logo"/></div>
			
			<!--login form-->
			<form id="login_form" method="post" action="login/validator.php">
				<table>
					<tr>
						<td class="td_right">email</td>
						<td><input name="u" type="text" id="u" class="login_field" /></td>
					</tr>
					<tr>
						<td class="td_right">password</td>
						<td><input name="p" type="password" id="p" class="login_field" /></td>
					</tr>
					<?php 
						//look for login errors
						if(isset($_SESSION['login_error_message']))
							{
								echo "<tr><td colspan=\"2\"><h1>".$_SESSION['login_error_message']."</h1></td></tr>";
								
								//clear login error
								unset($_SESSION['login_error_message']);
							}
					?>
					<tr>
						<td colspan="2"><input type="submit" value="login" /></td>
					</tr>
				</table>
			</form>
		</div>

		<!--alert-->
		<div id="alert">
			<div id="delete_install_message">
				Installation files still exist!  What do you want to do?<br /><br />
				<table class="center">
					<tr>
						<td>Go to Install</td>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<td>Delete & Login</td>
					</tr>
					<tr>
						<td>
							<form name="install_message_install" method="post" action="install.php" />
								<input type="image" src="images/right-arrow.png" alt="begin installation" />
							</form>
						</td>
						<td>
						</td>
						<td>
							<form name="install_message_delete" method="post" action="">
								<input type="hidden" name="delete_install" value="delete_install" />
								<input type="image" src="images/trash.png" alt="delete and login" />
							</form>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<!--forgot password-->
		<div id="forgot_password">
			<div>
				<table>
					<tr>
						<td colspan=2>Provide your email address and we will<br />send you instructions on how to reset your password.</td>
					</tr>
					<tr>
						<td>Email</td>
						<td>
							<form id="forgot_password_form" method="post" action="login/forgot_password.php">
								<input type="text" name="email" />
						</td>
					</tr>
					<tr>
						<td colspan=2><a href="."><img src="images/x.png" alt="close" /></a>&nbsp;&nbsp;&nbsp;&nbsp;<input type="image" src="images/right-arrow.png" alt="edit" /></a></td>
						</form>
					</tr>
				</table>
			</div>
		</div>

	</body>
</html>
