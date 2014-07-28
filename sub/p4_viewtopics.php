<?php
require_once('../config.php');

#Get topics
$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
    die ("Hey loser, check your server connection.");

$boardid = $_GET['boardid'];

$query = "SELECT bname FROM p4_boards WHERE bid = $boardid";
$result = $db->query($query) or
    die ($db->error);
$row = $result->fetch_assoc();
$board_name = $row['bname'];
print "<div id='navigation'><a href='p4_boardlist.php'>Board List</a> > " . $board_name . "</div><br />";


$query = "SELECT topic_title, username, tid, locked_topic, uid FROM p4_topics, p4_users
            WHERE board_id = $boardid AND user_id = uid
            ORDER BY create_time";
$result = $db->query($query) or
    die ($db->error);

$db->close();


#Print topics
print "<div id='list_header'>Topic List</div>";

while ($row = $result->fetch_assoc())
{
    $topic_title = $row['topic_title'];
    $user = $row['username'];
    $tid = $row['tid'];
    $uid = $row['uid'];
	$locked_topic = $row['locked_topic'];

    #$post_count = $row['post_count'];
    #$last_post_time = $row['last_post_time'];

    print "<div id='list_row'>
                <div><span id='list_item_name'><a href='p4_messagelist.php?topicid=$tid'>$topic_title</a></span>" . post_count($tid) .  locked_topic_code($locked_topic) . "</div>
                <div id='list_item_user'>Posted by <a href='p4_user.php?userid=$uid'>$user</a></div>
               </div>";
}

print "<br />";

function post_count($tid)
{
	$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
		die ("Hey loser, check your server connection.");
		
	$get_post_count = "SELECT COUNT(*) AS count FROM p4_posts WHERE topic_id = $tid";
	$result = $db->query($get_post_count)
		or die ($db->error);
		
	$row = $result->fetch_assoc();
	$post_count = $row['count'];
	
	return " <span id='note'>Posts: $post_count</span>";
}

function locked_topic_code($locked_topic)
{
	if ($locked_topic == 1)
		return " (Locked) ";
	else return "";
}