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
    	print "<div id='error'>You must be logged in to search.</div>";

    else
    {
	    $db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
			die ("Hey loser, check your server connection.");

		$post_data = isset($_POST['search_area'], $_POST['search_query'], $_POST['title_or_post'])
						&& $_POST['search_area'] != "" && $_POST['search_query'] != "";

		if ($post_data)
		{
			$search_area = $_POST['search_area'];
			$title_or_post = $_POST['title_or_post'];
			$search_query = mysqli_real_escape_string($db, $_POST['search_query']);

			if ($title_or_post == 'post')
			{
				if ($search_area == "all")
				{
					$search_all_query = "SELECT * FROM p4_posts, p4_topics, p4_boards
										 WHERE MATCH(message) AGAINST ('$search_query' IN BOOLEAN MODE)
										 AND board_id = bid AND topic_id = tid;";
					$result = $db->query($search_all_query)
		        		or die ($db->error);
				}

				else
				{
					$search_area = (int)$search_area;
					$search_query = "SELECT * FROM p4_posts, p4_topics, p4_boards
									 WHERE MATCH(message) AGAINST ('$search_query' IN BOOLEAN MODE)
									 AND board_id = bid AND topic_id = tid AND board_id = $search_area;";
					$result = $db->query($search_query)
		        		or die ($db->error);
				}

				while ($row = $result->fetch_assoc())
				{
					$bname = $row['bname'];
					$topic_title = $row['topic_title'];
					$tid = $row['tid'];
					$pid_in_topic = $row['pid_in_topic'];
					$message = $row['message'];

					print "$bname > <a href=p4_messagelist.php?topicid=$tid>$topic_title</a> (Post #$pid_in_topic)<br />";
				}
			}

			else
			{
				if ($search_area = "all")
				{
					$search_all_query = "SELECT * FROM p4_topics, p4_boards
										 WHERE MATCH(topic_title) AGAINST ('$search_query' IN BOOLEAN MODE)
										 AND board_id = bid";
					$result = $db->query($search_all_query)
		        		or die ($db->error);
				}

				else
				{
					$search_area = (int)$search_area;
					$search_query = "SELECT * FROM p4_topics, p4_boards
									 WHERE MATCH(topic_title) AGAINST ('$search_query' IN BOOLEAN MODE)
									 AND board_id = bid AND board_id = $search_area;";
					$result = $db->query($search_query)
		        		or die ($db->error);
				}

				while ($row = $result->fetch_assoc())
				{
					$bname = $row['bname'];
					$topic_title = $row['topic_title'];
					$tid = $row['tid'];

					print "$bname > <a href=p4_messagelist.php?topicid=$tid>$topic_title</a><br />";
				}

			}
		}

		else
		{

			print '<form method="post" action=' . $_SERVER["REQUEST_URI"] . '>';

			$query = "SELECT * FROM p4_boards ORDER BY bid ASC";
			$result = $db->query($query) or
			    die ($db->error);

			print "Search topic titles or posts?<br />";
			print "<input type='radio' name='title_or_post' value='title'>Topic Title<br />";
			print "<input type='radio' name='title_or_post' value='post'>Posts<br />";

			print "Seach which boards?<br />";
			print "<input type='radio' name='search_area' value='all'>All Forums<br />";

			while ($row = $result->fetch_assoc())
			{
			    $bid = $row['bid'];
			    $bname = $row['bname'];

			    print "<input type='radio' name='search_area' value='$bid'>$bname<br />";
			}

			print "<input type='text' name='search_query'><br />";
			print "<input type='submit' value='Submit'>";
			print "</form>";

		}

		$db->close();

	}

    ?>
</div>
</body>
</html>