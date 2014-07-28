<?php session_start();
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
?>

<html>
<head>
    <title>Message Board Message Board</title>
    <link rel="stylesheet" type="text/css" href="p4.css" />
</head>

<body>

<h1><a href='p4_boardlist.php'>Message Board Message Board</a></h1>

<div id='content'>
    <?php
    require_once('../config.php');
    require('p4_authentication.php');

	$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
		die ("Hey loser, check your server connection.");

    if(isset($_GET['pid'], $_GET['delete']) && $_GET['pid'] != "" && $_GET['delete'] == 1)
	{
		$pid = (int)$_GET['pid'];
		$delete_query = "UPDATE p4_posts SET deleted = 1 WHERE pid = $pid";
		$db->query($delete_query) or
			die ($db->error);

		$tid_query = "SELECT tid FROM p4_topics, p4_posts WHERE topic_id = tid AND pid = $pid";
		$result = $db->query($tid_query) or
			die ($db->error);
		$row = $result->fetch_assoc();
		$tid = $row['tid'];

		print "Post Deleted. Returning to topic...";
		print "<script>setTimeout(function () {window.location.href = 'p4_messagelist.php?topicid=$tid';
			}, 1500);</script>";
	}

	else if (isset($_GET['pid'], $_POST['editmessage']) && $_GET['pid'] != "" && $_POST['editmessage'] != "")
	{
		$pid = (int)$_GET['pid'];
		$new_message = mysqli_real_escape_string($db, $_POST['editmessage']);
		$edit_query = "UPDATE p4_posts SET message = '$new_message',
			edited = 1, edit_user = $active_uid, edit_time = NOW()
			WHERE pid = $pid";
		$db->query($edit_query) or
			die ($db->error);

		$tid_query = "SELECT tid FROM p4_topics, p4_posts WHERE topic_id = tid AND pid = $pid";
		$result = $db->query($tid_query) or
			die ($db->error);
		$row = $result->fetch_assoc();
		$tid = $row['tid'];

		print "Post Edited. Returning to topic...";

		print "<script>setTimeout(function () {window.location.href = 'p4_messagelist.php?topicid=$tid';
			}, 1500);</script>";
	}

	else if (isset($_GET['pid']) && $_GET['pid'])
	{
		$pid = (int)$_GET['pid'];
		$old_message_query = "SELECT message FROM p4_posts WHERE pid = $pid";
		$result = $db->query($old_message_query) or
			die ($db->error);
		$row = $result->fetch_assoc();
		$old_message = $row['message'];

		print "<form method='post' action=" . $_SERVER["REQUEST_URI"] . ">
			<textarea name='editmessage' rows='4' cols='50'>$old_message</textarea>
			<input type='submit' value='Submit'>
			</form>";
	}

	$db->close();

    ?>

</div>
</body>
</html>