<?php
require_once('../config.php');

$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
    die ("Hey loser, check your server connection.");


$topicid = $_GET['topicid'];
$page = 1;
if (isset($_GET['page']) && $_GET['page'] != 0)
    $page = $_GET['page'];

#Set up navigation bar
$query = "SELECT bid, bname, topic_title, locked_topic FROM p4_boards, p4_topics
            WHERE tid = $topicid AND board_id = bid";
$result = $db->query($query) or
    die ($db->error);
$row = $result->fetch_assoc();
$board_name = $row['bname'];
$bid = $row['bid'];
$topic_title = $row['topic_title'];
$locked_topic = $row['locked_topic'];

$lock_text = "";
if ($active_level < 3 && $locked_topic == 0)
	$lock_text = " <span id='note'><a href='p4_locktopic.php?topicid=$topicid'>(Lock Topic)</a></span>";
else if ($active_level < 3 && $locked_topic == 1)
	$lock_text = " <span id='note'><a href='p4_locktopic.php?topicid=$topicid&unlock=1'>(Unlock Topic)</a></span>";

print "<div id='navigation'><a href='p4_boardlist.php'>Board List</a> >
            <a href='p4_topiclist.php?boardid=". $bid ."'>" . $board_name . "</a> >
            " . $topic_title . $lock_text . "</div><br />";

#Load pagination values
$query = "SELECT * FROM p4_page";
$result = $db->query($query) or
    die ($db->error);
$row = $result->fetch_assoc();
$pagination = $row['pagination'];


#Load posts
$query = "SELECT * FROM p4_posts, p4_users
            WHERE uid = user_id AND topic_id = $topicid
            AND pid_in_topic > (($page - 1) * $pagination) AND pid_in_topic <= ($page * $pagination)
            ORDER BY pid ASC";

$result = $db->query($query) or
    die ($db->error);

#Start printing
while ($row = $result->fetch_assoc())
    {
        $user = $row['username'];
        $uid = $row['uid'];
        $userlevel = $row['user_level'];
		$banned_user = $row['banned_user'];
        $number_of_posts = $row['number_of_posts'];
        $message = $row['message'];
        $time = $row['time'];

        $pid = $row['pid'];
        $pid_in_topic = $row['pid_in_topic'];

        $avatar = $row['avatar'];
        $avatar_filename = $row['avatar_filename'];

        $edited = $row['edited'];
        $edit_time = $row['edit_time'];
        $edit_user = $row['edit_user'];
		$deleted = $row['deleted'];
		$edit_username = "";
		if ($edited == 1 && $edit_user != -1)
		{
			$find_edit_username = "SELECT username FROM p4_users WHERE uid = $edit_user";
			$edit_result = $db->query($find_edit_username) or
				die ($db->error);
			$row = $edit_result->fetch_assoc();
			$edit_username = $row['username'];
		}

		if ($deleted == 1)
			print "<div id='post'>
					<div id='post_header'>
					 <span id='username'><a href='p4_user.php?userid=$uid'>$user</a>" . insert_user_level($userlevel, $number_of_posts, $banned_user) . "&nbsp;|&nbsp;</span>
					 <span id='time'>Post #$pid_in_topic - $time</span>"
					 . insert_edit_delete_post($user, $active_username, $active_level, $pid) .
					"</div>
					<div id='note'>Message deleted.</div></div>";
		else
			print "<div id='post'>
					<div id='post_header'>
					 <span id='username'><a href='p4_user.php?userid=$uid'>$user</a>" . insert_user_level($userlevel, $number_of_posts, $banned_user) . "&nbsp;|&nbsp;</span>
					 <span id='time'>Post #$pid_in_topic - $time</span>"
					 . insert_edit_delete_post($user, $active_username, $active_level, $pid) .
					"</div>
					<table><td id='message_td'><div id='message'>$message</div>"
					. insert_edit_text($edited, $edit_time, $edit_username) .
                    insert_images($db, $pid, $loggedin) .
				   "</td><td>" . insert_avatar($avatar, $avatar_filename) . "</td></table></div>";
    }

function insert_user_level($userlevel, $number_of_posts, $banned_user)
{
	if ($banned_user == 1)
		return " (Banned)";

    if ($userlevel == 1)
        return " (Administrator)";
    if ($userlevel == 2)
        return " (Moderator)";

    if ($number_of_posts <= 1)
        return " (Only post!)";
    if ($number_of_posts <= 5)
        return " (Rare poster)";
    if ($number_of_posts <= 20)
        return " (Comes here often)";
    if ($number_of_posts <= 100)
        return " (You know this guy by now)";
    if ($number_of_posts > 100)
        return " (The man that needs no introduction)";
    return "";
}

function insert_edit_delete_post($user, $active_username, $active_level, $pid)
{
    if ($user == $active_username || $active_level <= 2)
        return "<span id='edit_delete'>&nbsp;|&nbsp;<a href='p4_editpost.php?pid=$pid'>Edit</a>&nbsp;|&nbsp;<a href='p4_editpost.php?pid=$pid&delete=1'>Delete</a></span>";
    else
        return "";
}

function insert_edit_text($edited, $edit_time, $edit_username)
{
	if ($edited == 1 & $edit_username != "")
	{
		return "<div id='note'>Post edited by $edit_username at $edit_time</div>";
	}
	else
		return "";
}

#Lower navigation bar
$query = "SELECT COUNT(pid) AS count FROM p4_posts
            WHERE topic_id = $topicid";

$result = $db->query($query) or
    die ($db->error);
$row = $result->fetch_array();
$message_count = $row['count'];

if ($message_count >= $pagination)
    print "<br/><div>Jump to page: ";
for ($i = 1; $i * $pagination <= $message_count; $i++)
{
    print '<a href="p4_messagelist.php?topicid=' . $topicid . '&page=' . $i .'">Page ' . $i . '</a> | ';
    if (($i + 1) * $pagination > $message_count && $i * $pagination != $message_count)
        print '<a href="p4_messagelist.php?topicid=' . $topicid . '&page=' . ($i+1) .'">Page ' . ($i+1) . '</a>';
}
if ($message_count >= $pagination)
    print "</div>";
print "<br />";

$db->close();


function insert_images($db, $pid, $loggedin)
{
    $return_result = "";
    $image_query = "SELECT * FROM p4_pictures WHERE post_id = $pid";
    $result = $db->query($image_query)
        or die($db->error);

    while ($row = $result->fetch_assoc())
    {
        $url = $row['picture_filename'];
        if ($loggedin)
            $return_result .= "<a href='images/$url' target='_blank'><img src='images/t-$url' /></a> ";
        else
            $return_result .= "<img src='images.jpg' />";
    }

    return $return_result;
}

function insert_avatar($avatar, $avatar_filename)
{
    if (!$avatar)
        return "";

    $avatar_tnail = "t-" . $avatar_filename;

    return "<a href='avatars/$avatar_filename' target='_blank'><img src='avatars/$avatar_tnail' /></a>";
}