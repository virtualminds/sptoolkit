<?php
/**
 * file:		index.php
 * version:		17.0
 * package:		Simple Phishing Toolkit (spt)
 * component:	Campaign management
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

	// verify session is authenticated and not hijacked
	$includeContent = "../includes/is_authenticated.php";
	if(file_exists($includeContent)){
		require_once $includeContent;
	}else{
		header('location:../errors/404_is_authenticated.php');
	}
?>
<!DOCTYPE HTML> 
<html>
	<head>
		<title>spt - campaigns</title>
		
		<!--meta-->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="description" content="welcome to spt - simple phishing toolkit.  spt is a super simple but powerful phishing toolkit." />
		
		<!--favicon-->
		<link rel="shortcut icon" href="../images/favicon.ico" />
		
		<!--css-->
		<link rel="stylesheet" href="../spt.css" type="text/css" />
		<link rel="stylesheet" href="spt_campaigns.css" type="text/css" />
	</head>
	<body>
		<div id="wrapper">
			<!--popovers-->
			<?php
				//check to see if the alert session is set
				if(isset($_SESSION['campaigns_alert_message']))
					{
						//create alert popover
						echo "<div id=\"alert\">";

						//echo the alert message
						echo "<div>".$_SESSION['campaigns_alert_message']."<br /><br /><a href=\"\"><img src=\"../images/left-arrow.png\" alt=\"close\" /></a></div>";
						
						//unset the seession
						unset ($_SESSION['campaigns_alert_message']);				

						//close alert popover
						echo "</div>";
					}
			?>
			<div id="add_campaign">
				<div>
					<form method="post" action="start_campaign.php">
						<table id="new_campaign">
							<tr>
								<td>Name</td>
								<td><input name="campaign_name" /></td>
								<td>
									<a class="tooltip"><img src="../images/lightbulb.png" alt="help" /><span>To start a new campaign, specify the campaign name, select one or more groups of targets and then select the template to be used.<br /><br /><strong>WARNING:</strong>  Emails will be sent as soon as you click the email icon.</span></a>
								</td>
							</tr>
							<tr>
								<?php
									//pull current host and path
									$path = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
									
									//strip off the end
									$path = preg_replace('/\/campaigns.*/','',$path);

									//create a hidden field with the path of spt
									echo "<input type=\"hidden\" name=\"spt_path\" value=\"".$path."\" />";
									  
								?>
							</tr>
							<tr>
								<td>Group(s)</td>
								<td>
									<select name = "target_groups[]" multiple="multiple" size="5" style="width: 100%;">
										<?php
											//connect to database
											include('../spt_config/mysql_config.php');
											
											//query for all groups
											$r = mysql_query('SELECT DISTINCT group_name FROM targets');
											while($ra=mysql_fetch_assoc($r))
												{
													echo "<option>".$ra['group_name']."</option>";
												}
										?>	
									</select>
								</td>
							</tr>
							<tr>
								<td>Template</td>
								<td>
									<select name = "template_id">
										<?php
											//connect to database
											include('../spt_config/mysql_config.php');
											
											//query for all groups
											$r = mysql_query('SELECT id, name FROM templates');
											while($ra=mysql_fetch_assoc($r))
												{
													echo "<option value=".$ra['id'].">".$ra['name']."</option>";
												}
										?>	
									</select>
								</td>
							</tr>
							<tr>
								<td>Education</td>
								<td>
									<select name = "education_id">
										<option value="0">None</option>
										<?php
											//connect to database
											include('../spt_config/mysql_config.php');
											
											//query for all groups
											$r = mysql_query('SELECT id, name FROM education');
											while($ra=mysql_fetch_assoc($r))
												{
													echo "<option value=".$ra['id'].">".$ra['name']."</option>";
												}
										?>	
									</select>
								</td>
							</tr>
							<tr>
								<td></td>
								<td><input type="radio" name="education_timing" value="1" /> Educate Immediatly<br /><input type="radio" name="education_timing" value="2" /> Educate After Post</td>
							</tr>
							<tr>
								<td></td>
								<td><a href=""><img src="../images/x.png" alt="x" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="image" src="../images/email.png" alt="email" /></td>
							</tr>
						</table>
					</form>
				</div>
			</div>
			<div id="responses">
				<div>
					<?php
					
						//connect to database
						include "../spt_config/mysql_config.php";

						//pull in campaign id
						if(isset($_REQUEST['c']))
							{
								$campaign_id = filter_var($_REQUEST['c'], FILTER_SANITIZE_NUMBER_INT);
								
								//go ahead and perform validation
								$r = mysql_query("SELECT DISTINCT campaign_id FROM campaigns_responses") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								while($ra = mysql_fetch_assoc($r))
									{
										if($ra['campaign_id'] == $campaign_id)
											{
												$campaign_match =1;
											}
									}
								if($campaign_match!=1)
									{
										$_SESSION['campaigns_alert_message'] = "please select a valid campaign";
										header('location:../campaigns/#alert');
										exit;
									}
							}
							
						//pull in filter and group
						if(isset($_REQUEST['f']))
							{
								$filter = filter_var($_REQUEST['f'], FILTER_SANITIZE_STRING);
								
								//go ahead and preform validation
								if($filter!="link" && $filter!="post")
									{
										$_SESSION['campaigns_alert_message'] = "please use a valid filter";
										header('location:../campaigns/#alert');
										exit;
									}
							}
						if(isset($_REQUEST['g']))
							{
								$group = filter_var($_REQUEST['g'], FILTER_SANITIZE_STRING);
								
								//go ahead and perform validation
								$r = mysql_query("SELECT DISTINCT group_name FROM targets") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								while ($ra = mysql_fetch_assoc($r))
									{
										if($group == $ra['group_name'])
											{
												$group_match = 1;
											}
									}
								if(!isset($group_match)
									{
										$_SESSION['campaigns_alert_message'] = "please select a valid group";
										header('location:../campaigns/#alert');
										exit;
									}
								
							}
						
						//if group and filter are both set send them back
						if(isset($filter) && isset($group))
							{
								$_SESSION['campaigns_alert_message'] = "you cannot pass both a filter and a group";
								header('location:../campaigns/#alert');
								exit;
								
							}
						
						//pull data for entire campaign if group and filters are NOT set
						if(!isset($group) && !isset($filter) && isset($campaign_id))
							{
								$r = mysql_query("SELECT campaigns_responses.target_id AS target_id, campaigns_responses.campaign_id AS campaign_id, campaigns_responses.link AS link, campaigns_responses.post AS post, targets.id AS id, targets.email AS email, targets.name AS name, campaigns_responses.ip AS ip, campaigns_responses.browser AS browser, campaigns_responses.browser_version AS browser_version, campaigns_responses.os AS os, campaigns_responses.link_time AS link_time FROM campaigns_responses JOIN targets ON campaigns_responses.target_id=targets.id WHERE campaigns_responses.campaign_id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
							
								//title the page with the campaign number
								$title = "<h3>Campaign ".$campaign_id." Responses</h3>";
							}
						
						//pull data if a group is set
						if(isset($group))
							{
								$r = mysql_query("SELECT campaigns_responses.target_id AS target_id, campaigns_responses.campaign_id AS campaign_id, campaigns_responses.link AS link, campaigns_responses.post AS post, targets.id AS id, targets.email AS email, targets.name AS name, campaigns_responses.ip AS ip, campaigns_responses.browser AS browser, campaigns_responses.browser_version AS browser_version, campaigns_responses.os AS os, campaigns_responses.link_time AS link_time FROM campaigns_responses JOIN targets ON campaigns_responses.target_id=targets.id WHERE targets.group_name = '$group' AND campaigns_responses.campaign_id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								
								//title the page with the campaign number
								$title = "<h3>Campaign ".$campaign_id." Responses - ".$group."</h3>";

							}
							
						//pull data if a filter is set
						if(isset($filter))
							{
								//if filter is for links
								if($filter == "link")
									{
										$r = mysql_query("SELECT campaigns_responses.target_id AS target_id, campaigns_responses.campaign_id AS campaign_id, campaigns_responses.link AS link, campaigns_responses.post AS post, targets.id AS id, targets.email AS email, targets.name AS name, campaigns_responses.ip AS ip, campaigns_responses.browser AS browser, campaigns_responses.browser_version AS browser_version, campaigns_responses.os AS os, campaigns_responses.link_time AS link_time FROM campaigns_responses JOIN targets ON campaigns_responses.target_id=targets.id WHERE campaigns_responses.link = 1 AND campaigns_responses.campaign_id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
										
										//title the page with the campaign number
										$title = "<h3>Campaign ".$campaign_id." Responses";
										
										if(isset($group))
											{
												$title .= " - ".$group." Group";	
											}
										
										$title .= " - Links</h3>";
									}
								
								//if filter is for posts
								if($filter == "post")
									{
										$r = mysql_query("SELECT campaigns_responses.target_id AS target_id, campaigns_responses.campaign_id AS campaign_id, campaigns_responses.link AS link, campaigns_responses.post AS post, targets.id AS id, targets.email AS email, targets.name AS name, campaigns_responses.ip AS ip, campaigns_responses.browser AS browser, campaigns_responses.browser_version AS browser_version, campaigns_responses.os AS os, campaigns_responses.link_time AS link_time  FROM campaigns_responses JOIN targets ON campaigns_responses.target_id=targets.id WHERE campaigns_responses.post != \"\"  AND campaigns_responses.campaign_id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
										
										//title the page with the campaign number
										$title = "<h3>Campaign ".$campaign_id." Responses";
										
										if(isset($group))
											{
												$title .= " - ".$group." Group";
											}
											
										$title .= " - Posts</h3>";
									}
							}
					
						//print the table header
						echo "
							<table id=\"campaign_list_header\">
								<tr>
									<td class=\"left\">
										<h1>";if(isset($title)){echo $title;}echo "</h1>
									</td>
									<td class=\"right\">
										<a class=\"tooltip\">
											<img src=\"../images/lightbulb.png\" alt=\"help\" />
											<span>This list provides you with a filtered view of campaign responses.  The title at the top left describes what filter is in place.  For each individual response you can see various metrics or analytics of the response itself such as the target's IP address, browser, browser version and Operating System. 
											</span>
										</a>
										&nbsp;&nbsp;&nbsp;
										<a href=\".\"><img src=\"../images/x.png\" alt=\"close\" /></a>
									</td>
								</tr>
							</table>
							<br />
						<table id=\"response_table\">
							<tr>
								<td><h3>ID</h3></td>
								<td><h3>Name</h3></td>
								<td><h3>Email</h3></td>
								<td><h3>Link</h3></td>
								<td><h3>Clicked at</h3></td>
								<td><h3>Post</h3></td>
								<td><h3>IP</h3></td>
								<td><h3>Browser</h3></td>
								<td><h3>Version</h3></td>
								<td><h3>OS</h3></td>								
							</tr>
						";
						
						//dump data into table
						while($ra = mysql_fetch_assoc($r))
							{
								echo "<tr>";
								echo "<td>".$ra['target_id']."</td>";
								echo "<td>".$ra['name']."</td>";
								echo "<td>".$ra['email']."</td>";
								if($ra['link'] == 1)
									{
										$link = 'Y';
									}
								else
									{
										$link = 'N';	
									}
								echo "<td>".$link."</td>";
								echo "<td>".$ra['link_time']."</td>";
								if(strlen($ra['post'])<1)
									{
										$post = 'N';
									}
								else
									{
										$post = $ra['post'];
									}
								echo "<td>".$post."</td>";
								echo "<td><a href=\"http://geomaplookup.net/?ip=".$ra['ip']."\" target=\"blank\">".$ra['ip']."</a></td>";
								echo "<td>".$ra['browser']."</td>";
								echo "<td>".$ra['browser_version']."</td>";
								echo "<td>".$ra['os']."</td>";
								echo "</tr>";
							}
						
						echo "</table>";
					?>
				</div>
			</div>

			<!--sidebar-->
			<?php include '../includes/sidebar.php'; ?>					

			<!--content-->
			<div id="content">
				<span class="button"><a href="#add_campaign"><img src="../images/plus_sm.png" alt="add" /> Campaign</a></span>
				<table class="spt_table">
					<tr>
						<td><h3>ID</h3></td>
						<td><h3>Name</h3></td>
						<td><h3>Template</h3></td>
						<td><h3>Target Groups</h3></td>
						<td><h3>Responses (Links/Posts)</h3></td>
						<td><h3>Delete</h3></td>
					</tr>
					
					<?php
					
						//connect to database
						include "../spt_config/mysql_config.php";
						
						//pull in list of all campaigns
						$r = mysql_query("SELECT campaigns.id, campaigns.campaign_name, campaigns.template_id, templates.name as name FROM campaigns JOIN templates ON campaigns.template_id = templates.id") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
						while ($ra = mysql_fetch_assoc($r))
							{
								echo	"
									<tr>
										<td><a href=\"?c=".$ra['id']."#responses\">".$ra['id']."</a></td>\n
										<td>".$ra['campaign_name']."</td>\n
										<td><a href=\"../templates/".$ra['template_id']."/\" target=\"_blank\">".$ra['name']."</a></td>\n
										<td>
								";
								
								$campaign_id = $ra['id'];
								
								//pull in groups
								$r3=mysql_query("SELECT group_name FROM campaigns_and_groups WHERE campaign_id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								while($ra3=mysql_fetch_assoc($r3))
									{
										echo	"<a href=\"?c=".$ra['id']."&amp;g=".$ra3['group_name']."#responses\">".$ra3['group_name']."</a><br />\n";
									}
								echo "</td>";
										
								$r2 = mysql_query("SELECT count(target_id) as count, sum(link) as link, sum(if(length(post) > 0, 1, 0)) as post FROM campaigns_responses WHERE campaign_id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
								while($ra2=mysql_fetch_assoc($r2))
									{
										$link = $ra2['link'];
										$post = $ra2['post'];
										
									}
								
								echo	"<td><a href=\"?c=".$ra['id']."&amp;f=link#responses\">".$link."</a> / <a href=\"?c=".$ra['id']."&amp;f=post#responses\">".$post."</a></td>";
								echo	"<td><a href=\"delete_campaign.php?c=".$campaign_id."\"><img src=\"../images/trash_sm.png\" alt=\"delete\" /></a></td>";
								echo	"</tr>";								
							}
					
					?>
				</table>
			</div>
		</div>
	</body>
</html>
