<?php
/**
 * file:		target_upload_batch.php
 * version:		3.0
 * package:		Simple Phishing Toolkit (spt)
 * component:	Target management
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

	//start session
	session_start();
	
	//check for authenticated session
	if($_SESSION['authenticated']!=1)
		{
			//for potential return
			$_SESSION['came_from']='targets';
			
			//set error message and send them back to login
			$_SESSION['login_error_message']="login first";
			header('location:../');
			exit;
		}
	
	//check for session hijacking
	elseif($_SESSION['ip']!=md5($_SESSION['salt'].$_SERVER['REMOTE_ADDR'].$_SESSION['salt']))
		{
			//set error message and send them back to login
			$_SESSION['login_error_message']="your ip address must have changed, please authenticate again";
			header('location:../');
			exit;
		}

//make sure the user is an admin
	if($_SESSION['admin']!=1)
		{
			$_SESSION['targets_alert_message'] = "you do not have permission to upload targets";
			header('location:../targets/#alert');
			exit;
		}

//ensure that the file is under 20M
	if($_FILES["file"]["size"] > 20000000)
		{
	  		$_SESSION['targets_alert_message'] = 'max file size is 20MB';
	  		header('location:../targets/#alert');
	  		exit;
	  	}

//ensure there are no errors
        if ($_FILES["file"]["error"] > 0)
		{
			$_SESSION['targets_alert_message'] = $_FILES["file"]["error"];
			header('location:../targets/#alert');
			exit;
		}

//set ini to detect line endings to prepare for csv import
ini_set("auto_detect_line_endings", true);

//pull in file
$lines = file($_FILES["file"]["tmp_name"]);

//initialize counters
$counter = 0;
$counter_total = 0;
$counter_bad_name = 0;
$counter_bad_emails = 0;
$counter_bad_group_name = 0;

$temp_counter_bad_name = 0;
$temp_counter_bad_emails = 0;
$temp_counter_bad_group_name = 0;

//ensure there is a comma in every line
foreach($lines as $line)
    {
        
        if(!preg_match('/[,]/',$line))
            {
                $_SESSION['targets_alert_message'] = "this file is not properly comma delimited";
       	    	header('location:../targets/#alert');
                exit;

            }
    }

//ensure there are exactly 3 columns
foreach($lines as $line)
	{
		//separate each line into an array based on the comma delimiter
		$line_contents = explode(',',$line);

		//ensure there are no more than three columns
		if(isset($line_contents[3]))
		    {
			$_SESSION['targets_alert_message'] = "you have too many columns, only populate a name, email and group column";
			header('location:../targets/#alert');
			exit;
		    }

		//ensure there are at least three columns
		if(!isset($line_contents[0]) || !isset($line_contents[1]) || !isset($line_contents[2]))
		    {
			$_SESSION['targets_alert_message'] = "you do not have at least three columns in all rows";
			header('location:../targets/#alert');
			exit;
		    }
	}

//validate each column of data and if all columns validate write the entire line to the database
foreach($lines as $line2)
	{
		//separate each line into an array based on the comma delimiter
		$line_contents2 = explode(',',$line2);
		
		//validate name
		if(eregi('/[^a-zA-Z0-9_-\s!.()]/', trim($line_contents2[0])))
			{
				//increment bad name counter
				$temp_counter_bad_name++;
			}
		else
			{
				$temp_name = trim($line_contents2[0]);
			}

		//validate email
		if(filter_var(trim($line_contents2[1]), FILTER_VALIDATE_EMAIL))
			{
				$temp_email = trim($line_contents2[1]);
			}
		else
			{
				//increment bad email counter
				$temp_counter_bad_emails++;
			}
				
		//validate the group name
		if(eregi('/[^a-zA-Z0-9_-\s!.()]/', trim($line_contents2[2])))
			{
				//increment bad group name count
				$temp_counter_bad_group_name++;
			}
		else
			{
				$temp_group = trim($line_contents2[2]);	
			}
							
		//if there are any errors increment counters, otherwise write values to database
		if($temp_counter_bad_name == 1 || $temp_counter_bad_emails == 1 || $temp_counter_bad_group_name == 1)
			{
				if($temp_counter_header_rows != 1)
					{
						$counter_bad_name = $temp_counter_bad_name + $counter_bad_name;
						$counter_bad_emails = $temp_counter_bad_emails + $counter_bad_emails;
						$counter_bad_group_name = $temp_counter_bad_group_name + $counter_bad_group_name;
					}					
			}

		else
			{									
				//connect to database
				include "../spt_config/mysql_config.php";

				//insert data
				mysql_query("INSERT INTO targets (name, email, group_name) VALUES ('$temp_name','$temp_email','$temp_group')") or die('<div id="die_error">There is a problem with the database...please try again later</div>');

				//increment counter
				$counter++;

			}
	
		$counter_total++;
		
		//set temp counters back to 0
		$temp_counter_bad_name = 0;
		$temp_counter_bad_emails = 0;
		$temp_counter_bad_group_name = 0;
		
	}


//send stats back if there were bad rows
	if($counter_bad_name > 0)
		{
			$_SESSION["bad_row_stats"][] = $counter_bad_name." rows excluded due to names with bad values";
		}
	if($counter_bad_emails > 0)
		{
			$_SESSION["bad_row_stats"][] = $counter_bad_emails." rows excluded due to bad email addresses";
		}
	if($counter_bad_group_name > 0)
		{
			$_SESSION["bad_row_stats"][] = $counter_bad_group_name." rows excluded due to bad group names";
		}
			
//send user back to targets page with success message
	$_SESSION['targets_alert_message'] = $counter." of ".$counter_total." targets uploaded successfully";
	header('location:../targets/#alert');
	exit;
?>