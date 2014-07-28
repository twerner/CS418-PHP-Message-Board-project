<?php
require_once('../config.php');

$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
    die ("Hey loser, check your server connection.");

$userid = $_GET['userid'];

if(isset($_POST['ban_uid']) && $_POST['ban_uid'] > 0 && ($active_level == 1 || $active_level == 2))
{
	$ban_uid = (int)$_POST['ban_uid'];

	$ban_code = "UPDATE p4_users SET banned_user = 1, username = 'banned - $ban_uid', email = 'banned$ban_uid' WHERE uid = $ban_uid ";
	$db->query($ban_code) or
		die ($db->error);

	$get_email = "SELECT * FROM p4_users WHERE uid = $ban_uid";
	$result = $db->query($get_email) or
		die ($db->error);
	$row = $result->fetch_assoc();
	$email = $row['email'];
	$email_type = $row['email_type'];
	$username = $row['username'];

	send_email($username, $email, $email_type);

	print "<div id='important_note'>User banned</div><br />";
}

if(isset($_POST['sus_uid']) && $_POST['sus_uid'] > 0 && ($active_level == 1 || $active_level == 2))
{
	$sus_uid = (int)$_POST['sus_uid'];

	$sus_code = "UPDATE p4_users SET suspended_user = 1 WHERE uid = $sus_uid ";
	$db->query($sus_code) or
		die ($db->error);

	print "<div id='important_note'>User suspended</div><br />";
}

if(isset($_POST['unsus_uid']) && $_POST['unsus_uid'] > 0 && $active_level <= 2)
{
	$unsus_uid = (int)$_POST['unsus_uid'];

	$unsus_code = "UPDATE p4_users SET suspended_user = 0 WHERE uid = $unsus_uid ";
	$db->query($unsus_code) or
		die ($db->error);

	print "<div id='important_note'>User unsuspended</div><br />";
}

$get_user_info = "SELECT * from p4_users WHERE uid = $userid";
$result = $db->query($get_user_info) or
    die ($db->error);
$row = $result->fetch_assoc();

$username = $row['username'];
$uid = $row['uid'];
$user_level = $row['user_level'];
$number_of_posts = $row['number_of_posts'];
$number_of_topics = $row['number_of_topics'];
$date_registered = $row['date_registered'];
$date_last_post = $row['date_last_post'];
$suspended_user = $row['suspended_user'];
$banned_user = $row['banned_user'];

print ("<span id='header'>Username:</span> $username<br />");
print ("<span id='header'>User level:</span> " . insert_user_level($user_level, $number_of_posts, $banned_user) . "<br />");
print ("<span id='header'>Number of posts:</span> $number_of_posts<br />");
print ("<span id='header'>Number of topics:</span> $number_of_topics<br />");
print ("<span id='header'>Date registered:</span> $date_registered<br />");
print ("<span id='header'>Date of last post:</span> $date_last_post<br />");

if (($active_level == 1 || $active_level == 2) && $user_level > 1 && $suspended_user == 1)
{
	print '<form method="post" action=' . $_SERVER["REQUEST_URI"] . '>
        Unsuspend user?
		<input type="hidden" name="unsus_uid" value=' . $uid . '/>
        <input type="submit" value="Unsuspend">
        </form>';
}

if (($active_level == 1 || $active_level == 2) && $user_level > 1 && $suspended_user == 0)
{
	print '<form method="post" action=' . $_SERVER["REQUEST_URI"] . '>
        Suspend user?
		<input type="hidden" name="sus_uid" value=' . $uid . '/>
        <input type="submit" value="Suspend">
        </form>';
}

if ($active_level == 1 && $user_level > 1)
{
	print '<form method="post" action=' . $_SERVER["REQUEST_URI"] . '>
        Ban user?
		<input type="hidden" name="ban_uid" value=' . $uid . '/>
        <input type="submit" value="Ban">
        </form>';
}

$db->close();

function insert_user_level($userlevel, $number_of_posts, $banned_user)
{
	if ($banned_user == 1)
		return "Banned";

    if ($userlevel == 1)
        return "Administrator";
    if ($userlevel == 2)
        return "Moderator";

	if ($number_of_posts == 0)
		return "Might be alive?";
    if ($number_of_posts <= 1)
        return "Only post!";
    if ($number_of_posts <= 5)
        return "Rare poster";
    if ($number_of_posts <= 20)
        return "Comes here often";
    if ($number_of_posts <= 100)
        return "You know this guy by now";
    if ($number_of_posts > 100)
        return "The man that needs no introduction";
    return "";
}

function send_email($username, $email, $email_type)
{

	$to = $email;
	$subject = "Welcome to Message Board Message Board";
	$headers = "From: twerner@cs.odu.edu" . "\r\n";
	if ($email_type == "plain")
	{
		$message = "$username: You have been banned from Message Board Message Board";
		mail($to, $subject, $message, $headers);
	}
	else
	{
		$message = "$username: You have been banned from Message Board Message Board";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		mail($to, $subject, $message, $headers);
	}
}