<?php
/**
 * file:		delete_campaign.php
 * version:		3.0
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

//validate that the currently logged in user is an admin
if($_SESSION['admin']!=1)
	{
		$_SESSION['campaigns_alert_message'] = "you do not have permission to delete a campaign";
		header('location:../campaigns/#alert');
		exit;
	}

//get campaign id
$campaign_id = filter_var($_REQUEST['c'], FILTER_SANITIZE_NUMBER_INT);


//validate the campaign id
include "../spt_config/mysql_config.php";
$r = mysql_query("SELECT id FROM campaigns") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
while($ra = mysql_fetch_assoc($r))
	{
		if($ra['id']==$campaign_id)
			{
				$match = 1;
			}
	}
if(isset($match))
	{
		$_SESSION['campaigns_alert_message'] = "you can only delete real campaign ids";
		header('location:../campaigns/#alert');
		exit;
	}

//delete all traces of this specific campaign
mysql_query("DELETE FROM campaigns WHERE id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
mysql_query("DELETE FROM campaigns_and_groups WHERE campaign_id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');
mysql_query("DELETE FROM campaigns_responses WHERE campaign_id = '$campaign_id'") or die('<div id="die_error">There is a problem with the database...please try again later</div>');

//send them back to the campaigns home page
$_SESSION['campaigns_alert_message'] = "campaign deleted successfully";
header('location:../campaigns/#alert');
exit;

?>
