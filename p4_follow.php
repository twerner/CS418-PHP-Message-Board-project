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

    if (!$loggedin)
    	print "<div id='error'>You must be logged in to access this page.</div>";

    else
    {
    	$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
			die ("Hey loser, check your server connection.");

		$post_data = isset($_POST['followee']) && $_POST['followee'] != "";

		if ($post_data)
		{
			$followee = mysqli_real_escape_string($db, $_POST['followee']);
			$valid_user_query = "SELECT uid FROM p4_users WHERE username = '$followee'";
			$results = $db->query($valid_user_query)
				or die($db->error);

			if ($results->num_rows)
			{
				$row = $results->fetch_assoc();
				$followee_uid = $row['uid'];
				$follow_query = "INSERT INTO p4_follow (follower_id, followee_id)
						VALUES ($active_uid, $followee_uid)";
				$db->query($follow_query)
					or die($db->error);

				print "You are now following $followee.";
			}
		}

		else
		{
			print '<form method="post" action=' . $_SERVER["REQUEST_URI"] . '>';
			print 'Enter user name to follow:';
			print '<input type="text" name="followee"><br />';
			print '<input type="submit" value="Submit"></form>';
		}

		$db->close();
    }

    ?>
</div>
</body>
</html>