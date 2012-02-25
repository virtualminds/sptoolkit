<?php
/**
 * file:		index.php
 * version:		14.0
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
?>
<!DOCTYPE HTML> 
<html>
	<head>
		<title>spt - targets</title>
		
		<!--meta-->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="description" content="welcome to spt - simple phishing toolkit.  spt is a super simple but powerful phishing toolkit." />
		
		<!--favicon-->
		<link rel="shortcut icon" href="../images/favicon.ico" />
		
		<!--css-->
		<link rel="stylesheet" href="../spt.css" type="text/css" />
		<link rel="stylesheet" href="spt_targets.css" type="text/css" />

		<!--scripts-->
		<script language="Javascript" type="text/javascript">
			function updateCustom(custom,value) 
				{ 
					//begin new request
					xmlhttp = new XMLHttpRequest();

					//send update request
					xmlhttp.open("POST","custom_update.php",false);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					xmlhttp.send("c="+custom+"&n="+value);					
				}
		</script>
		<script language="Javascript" type="text/javascript">
			function updateTarget(id,column,data) 
				{ 
					//begin new request
					xmlhttp = new XMLHttpRequest();

					//send update request
					xmlhttp.open("POST","target_update.php",false);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					xmlhttp.send("id="+id+"&column="+column+"&data="+data);
					
				}
		</script>
		
	</head>
	<body>
		<?php
			//check to see if the alert session is set
			if(isset($_SESSION['targets_alert_message']))
				{
					//create alert popover
					echo "<div id=\"alert\">";

					//echo the alert message
					echo "<div>".$_SESSION['targets_alert_message']."<br />";
					
					if(isset($_SESSION['bad_row_stats']))
						{
							//count how many stats there are
							$count = count($_SESSION['bad_row_stats']);
							
							//start the list
							echo "<ul>";
							
							//echo all bad row stats
							while($count > 0)
								{
									echo "<li>".$_SESSION['bad_row_stats'][($count-1)]."</li>";
									$count--;
								}
							
							//end the list
							echo "</ul>";
							
							//unset bad row stat session
							unset ($_SESSION['bad_row_stats']);
						}

					//close the alert message
					echo "<br /><a href=\"\"><img src=\"../images/left-arrow.png\" alt=\"close\" /></a></div>";

					//close alert popover
					echo "</div>";

					//unset the seession
					unset ($_SESSION['targets_alert_message']);		
							
				}
		?>
		<div id="add_one">
			<div>
				<form action="target_upload_single.php" method="post" enctype="multipart/form-data">
					<table id="add_single">
						<tr>
							<td>Name</td>
							<td><input type="text" name="name" /></td>
							<td>
								<a class="tooltip"><img src="../images/lightbulb.png" alt="help" /><span>Enter the target's name, valid email address and then select an existing or new group to add the new target to.</span></a>
							</td>
						</tr>
						<tr>
							<td>Email</td>
							<td><input type="text" name="email" /></td>
						</tr>
						<tr>
							<td>Existing Group</td>
							<td>
								<select name="group_name">
									<option value="Select an Existing Group...">Select an Existing Group...</option>
									<?php
										//connect to database
										include "../spt_config/mysql_config.php";
										
										//pull in current group names
										$r = mysql_query("SELECT DISTINCT group_name FROM targets ORDER BY group_name") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
										while ($ra = mysql_fetch_assoc($r))
											{
												echo "<option value=\"".$ra['group_name']."\">".$ra['group_name']."</option>";
											}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td></td>
							<td>
								OR
							</td>
						</tr>
						<tr>
							<td>New Group</td>
							<td> 
								<input type="text" name="group_name_new" />
							</td>
						</tr>
						<tr>
							<td><br /></td>
							<td><br /></td>
						</tr>
						<tr>
							<td><h3>Custom Attributes</h3</td>
							<td></td>
						</tr>
						<?php
								//determine what the custom field names are
								$r = mysql_query("SELECT * FROM targets");
								$custom1 = mysql_field_name($r,4);
								$custom2 = mysql_field_name($r,5);
								$custom3 = mysql_field_name($r,6);

						?>
						<tr>
							<td><?php echo $custom1 ?></td>
							<td><input type="text" name="custom1" /></td>
						</tr>
						<tr>
							<td><?php echo $custom2 ?></td>
							<td><input type="text" name="custom2" /></td>
						</tr>
						<tr>
							<td><?php echo $custom3 ?></td>
							<td><input type="text" name="custom3" /></td>
						</tr>
						<tr>	
							<td></td>
							<td>
								<br />
								<a href=""><img src="../images/x.png" alt="cancel" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="image" src="../images/plus.png" alt="add" />
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
		<div id="add_many">
			<div>
				<table id="add_bunches">
					<form action="target_upload_batch.php" method="post" enctype="multipart/form-data">
						<tr>
							<td>
								<input type="file"  name="file" />
							</td>
							<td>
								<a class="tooltip"><img src="../images/lightbulb.png" alt="help" /><span>Upload a csv file with a header row that contains a column for the required columns (name, email, group) as well as any additional attributes you have added.</span></a>
							</td>
						</tr>
						<tr>
							<td>
								<br />
								<a href=""><img src="../images/x.png" alt="cancel" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="image" src="../images/plus.png" alt="add" />
							</td>
							<td></td>
					</form>
				</table>
			</div>
		</div>
		<div  id="group_list">
			<div>
				<table id="group_list_header">
					<tr>
						<td class="left">
							<h1><?php if(isset($_REQUEST['g'])){echo $_REQUEST['g'];}else{echo "All Targets";}?></h1>
						</td>
						<td class="right">
							<a class="tooltip">
								<img src="../images/lightbulb.png" alt="help" />
								<span>You can easily edit any individual, underlined cell by clicking on it and making your changes.  Changes are automatically saved when you click anywhere <strong>outside</strong> of the cell just edited. Changing the 3 custom column headings will require you to edit the header row of bulk target upload files. 
								</span>
							</a>
							&nbsp;&nbsp;&nbsp;
							<a href="."><img src="../images/x.png" alt="close" /></a>
						</td>
					</tr>
				</table>
				<br />
				<table id="group_user_list">
					<tr>
						<td><h3>Name</h3></td>
						<td><h3>Email</h3></td>
						<td><h3>Group</h3></td>
						<?php 
							
							//get the current custom column Names
							$r = mysql_query("SELECT * FROM targets");
							$custom1 = mysql_field_name($r,4);
							$custom2 = mysql_field_name($r,5);
							$custom3 = mysql_field_name($r,6);

						?>
						<td class="target_cell"><h3><input id="custom1" onchange="updateCustom('custom1',this.value)" value="<?php echo $custom1; ?>" class="invisible_input"/></h3></td>
						<td class="target_cell"><h3><input id="custom2" onchange="updateCustom('custom2',this.value)" value="<?php echo $custom2; ?>" class="invisible_input"/></h3></td>
						<td class="target_cell"><h3><input id="custom3" onchange="updateCustom('custom3',this.value)" value="<?php echo $custom3; ?>" class="invisible_input"/></h3></td>
						<td></td>
					</tr>
					<?php

						//connect to database
						include "../spt_config/mysql_config.php";

						if(isset($_REQUEST['g']))
							{

								$group = $_REQUEST['g'];

								//ensure the group name is under 50 characters
								if(strlen($group) > 50)
									{
										$_SESSION['targets_alert_message'] = "group names cannot be over 50 characters";
										header("location:../targets/#alert");
										exit;
									}

								//ensure the group name passed only has letters in it
								if(preg_match('/[^a-zA-Z0-9_-\s!.()]/', $group))
									{
										$_SESSION['targets_alert_message'] = "group names may only contain letters";
										header("location:../targets/#alert");
										exit;
									}
									
								//ensure that the group name exists in the database
								$r = mysql_query("SELECT DISTINCT group_name FROM targets") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								while ($ra = mysql_fetch_assoc($r))
									{
										if($ra['group_name'] == $group)
											{
												$match = 1;
											}
									}

								if($match!=1)
									{
										$_SESSION['targets_alert_message'] = "this group does not exist";
										header("location:../targets/#alert");
										exit;
									}
								
								//determine what the custom field names are
								$r = mysql_query("SELECT * FROM targets");
								$custom1 = mysql_field_name($r,4);
								$custom2 = mysql_field_name($r,5);
								$custom3 = mysql_field_name($r,6);
					
								//query for a list of group members ordered alphabetically
								$r = mysql_query("SELECT * FROM targets WHERE group_name = '$group' ORDER BY name") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								while ($ra = mysql_fetch_array($r))
									{
										
										//build a row for each member of the group wrapped in a form that will dynamically edit each entry as changes are made
										echo 
											"
												<tr>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_name\" onchange=\"updateTarget(".$ra[0].",'name',this.value)\" type=\"text\" value=\"".$ra[2]."\" class=\"invisible_input\"/></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_email\" onchange=\"updateTarget(".$ra[0].",'email',this.value)\" type=\"text\" value=\"".$ra[1]."\" class=\"invisible_input\" /></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_group\" onchange=\"updateTarget(".$ra[0].",'group_name',this.value)\" type=\"text\" value=\"".$ra[3]."\" class=\"invisible_input\" /></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_".$custom1."\" onchange=\"updateTarget(".$ra[0].",'".$custom1."',this.value)\" type=\"text\" value=\"".$ra[4]."\" class=\"invisible_input\" /></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_".$custom2."\" onchange=\"updateTarget(".$ra[0].",'".$custom2."',this.value)\" type=\"text\" value=\"".$ra[5]."\" class=\"invisible_input\" /></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_".$custom3."\" onchange=\"updateTarget(".$ra[0].",'".$custom3."',this.value)\" type=\"text\" value=\"".$ra[6]."\" class=\"invisible_input\" /></td>\n
														<td><a href=\"target_delete.php?g=".$ra[3]."&u=".$ra[0]."\"><img src=\"../images/trash_sm.png\" alt=\"delete\" /></a></td>\n
												</tr>
											";
									}
							}
						else
							{
								//query for a list of group members ordered alphabetically
								$r = mysql_query("SELECT * FROM targets") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								while ($ra = mysql_fetch_array($r))
									{
										//build a row for each member of the group wrapped in a form that will dynamically edit each entry as changes are made
										echo 
											"
												<tr>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_name\" onchange=\"updateTarget(".$ra[0].",'name',this.value)\" type=\"text\" value=\"".$ra[2]."\" class=\"invisible_input\"/></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_email\" onchange=\"updateTarget(".$ra[0].",'email',this.value)\" type=\"text\" value=\"".$ra[1]."\" class=\"invisible_input\" /></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_group\" onchange=\"updateTarget(".$ra[0].",'group_name',this.value)\" type=\"text\" value=\"".$ra[3]."\" class=\"invisible_input\" /></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_".$custom1."\" onchange=\"updateTarget(".$ra[0].",'".$custom1."',this.value)\" type=\"text\" value=\"".$ra[4]."\" class=\"invisible_input\" /></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_".$custom2."\" onchange=\"updateTarget(".$ra[0].",'".$custom2."',this.value)\" type=\"text\" value=\"".$ra[5]."\" class=\"invisible_input\" /></td>\n
														<td class=\"target_cell\"><input id=\"".$ra[0]."_".$custom3."\" onchange=\"updateTarget(".$ra[0].",'".$custom3."',this.value)\" type=\"text\" value=\"".$ra[6]."\" class=\"invisible_input\" /></td>\n
														<td><a href=\"target_delete.php?g=".$ra[3]."&u=".$ra[0]."\"><img src=\"../images/trash_sm.png\" alt=\"delete\" /></a></td>\n
												</tr>
											";
									}
							}
						?>
				</table>
			</div>
		</div>
		<div id="wrapper">
			<!--sidebar-->
			<?php include '../includes/sidebar.php'; ?>					

			<!--content-->
			<div id="content">
				<span class="button"><a href="#add_one"><img src="../images/plus_sm.png" alt="add" /> One</a></span>
				<span class="button"><a href="#add_many"><img src="../images/plus_sm.png" alt="add" /> Many</a></span>
				<table class="spt_table">
					<tr>
						<td><h3>Group Name</h3></td>
						<td><h3>Quantity</h3></td>
						<td><h3>Delete</h3></td>
					</tr>
					<tr>
						<td><a href="#group_list"><strong>All Targets</strong></a></td>
						<?php
							//connect to database
							include "../spt_config/mysql_config.php";

							//query for total count of targets
							$r = mysql_query("SELECT COUNT(id) AS target_count FROM targets");
							while($ra = mysql_fetch_assoc($r))
								{
									echo "<td>".$ra['target_count']."</td>";		
								}
						?>
						<td></td>
					</tr>
					<?php
						//connect to database
						include "../spt_config/mysql_config.php";
						
						//query for a list of groups ordered alphabetically
						$r = mysql_query("SELECT DISTINCT group_name FROM targets ORDER BY group_name") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
						while ($ra = mysql_fetch_assoc($r))
							{
								echo "<tr>";
								echo "<td><a href=\"?g=".$ra['group_name']."#group_list\">".$ra['group_name']."</a></td>";
								$group_name = $ra['group_name'];
								$r1 = mysql_query("SELECT COUNT(group_name) FROM targets WHERE group_name = '$group_name'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								while($ra1 = mysql_fetch_assoc($r1))
									{
										echo "<td>".$ra1['COUNT(group_name)']."</td>";
									}
								echo "<td><a href=\"group_delete.php?g=".$ra['group_name']."\"><img src=\"../images/trash_sm.png\" alt=\"delete\" /></a></td>";
								echo "</tr>";
							}
					?>
				</table>
			</div>
		</div>	
	</body>
</html>
