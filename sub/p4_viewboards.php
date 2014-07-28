<?php
require_once('../config.php');

#Get data
$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
    die ("Hey loser, check your server connection.");

$query = "SELECT * FROM p4_boards
            ORDER BY bid ASC";

$result = $db->query($query) or
    die ($db->error);

$db->close();

#Start printing
print "<div id='list_header'>Board List</div>";

while ($row = $result->fetch_assoc())
{
    $bname = $row['bname'];
    $bid = $row['bid'];

    #$topic_count = $row['topic_count'];
    #$last_post_time = $row['last_post_time'];

    print "<div id='list_row'>
                <div><span id='list_item_name'><a href='p4_topiclist.php?boardid=$bid'>$bname</a></span>" . topic_count($bid) . "</div>
               </div>";
}

function topic_count($bid)
{
	$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
		die ("Hey loser, check your server connection.");
		
	$get_post_count = "SELECT COUNT(*) AS count FROM p4_topics WHERE board_id = $bid";
	$result = $db->query($get_post_count)
		or die ($db->error);
		
	$row = $result->fetch_assoc();
	$topic_count = $row['count'];
	
	return " <span id='note'>Topics: $topic_count</span>";
}
