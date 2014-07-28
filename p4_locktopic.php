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

    if(isset($_GET['topicid'], $_GET['unlock']) && $_GET['topicid'] != "" && $_GET['unlock'] == 1)
	{
		$topicid = (int)$_GET['topicid'];
		$unlock_query = "UPDATE p4_topics SET locked_topic = 0 WHERE tid = $topicid";
		$db->query($unlock_query) or
			die ($db->error);

		$bid_query = "SELECT bid FROM p4_topics, p4_boards WHERE board_id = bid AND tid = $topicid";
		$result = $db->query($bid_query) or
			die ($db->error);
		$row = $result->fetch_assoc();
		$bid = $row['bid'];

		print "Topic Unlocked. Returning to topic list...";
		print "<script>setTimeout(function () {window.location.href = 'p4_topiclist.php?boardid=$bid';
			}, 1500);</script>";
	}

	else if (isset($_GET['topicid']) && $_GET['topicid'] != "")
	{
		$topicid = (int)$_GET['topicid'];
		$lock_query = "UPDATE p4_topics SET locked_topic = 1 WHERE tid = $topicid";
		$db->query($lock_query) or
			die ($db->error);

		$bid_query = "SELECT bid FROM p4_topics, p4_boards WHERE board_id = bid AND tid = $topicid";
		$result = $db->query($bid_query) or
			die ($db->error);
		$row = $result->fetch_assoc();
		$bid = $row['bid'];

		print "Topic Locked. Returning to topic list...";

		print "<script>setTimeout(function () {window.location.href = 'p4_topiclist.php?boardid=$bid';
			}, 1500);</script>";
	}

	$db->close();

    ?>
</div>
</body>
</html>