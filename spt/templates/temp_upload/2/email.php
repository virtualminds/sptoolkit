<?php

/**
 * file:    email.php
 * version: 3.0
 * package: Simple Phishing Toolkit (spt)
 * component:	Email template - Quick Start campaign templates
 * copyright:	Copyright (C) 2011 The SPT Project. All rights reserved.
 * license: GNU/GPL, see license.htm.
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
 * */

//this is the email template

//populate the variables below with what you want the email to look like
//the variable @link will be generated by the application, just place the
//variable @link somewhere in the email.

//You can also use @fname (first name), @lname (last name) and @url (phishing url).

//This will populate the subject line of the email that is sent
$subject = 'Charles Schweb Account Alert';

//This will set the sender's name and email address as well as reply to address
$sender_email = "notify@schweb.com";
$sender_friendly = "Charles Schweb";
$reply_to = "no-reply@schweb.com";

//Set the Content Type and transfer encoding
$content_type = "text/html; charset=utf-8";

//Set the fake link
$fake_link = "https://client.schweb.com/Login/SignOn/CustomerCenterLogin.aspx";

//This will populate the body of the email
$message = '<html><body>We have recently received many reports from our customers about fraudulent online transactions.  We have launched a new security system to secure new and old accounts from this kind of fraud.  To prevent your investment account from this fraud, update your information on by clicking the link below.<br /><br />@link<br /><br />Thank You,<br />Charles Schweb Security Team</body></html>';
?>