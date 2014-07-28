<?php

$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
    die ("Hey loser, check your server connection.");

$self = $_SERVER['PHP_SELF'];

#------------------------------------------------------------------------

#Changing user level
if(isset($_POST['uid'], $_POST['level']))
{
    $uid = $_POST['uid'];
    $level = $_POST['level'];

    $query = "UPDATE p4_users SET user_level = $level
            WHERE uid = $uid";

    $db->query($query)
        or die ($db->error);


    echo '<script>parent.window.location.reload(true);</script>';
}

#Changing pagination
if(isset($_POST['pagination']))
{
    $pagination = $_POST['pagination'];

    $query = "UPDATE p4_page SET pagination = $pagination";

    $db->query($query)
        or die ($db->error);
}

#Creating new forum
if(isset($_POST['new_forum_name']) && $_POST['new_forum_name'] != "")
{
    $new_forum_name = $_POST['new_forum_name'];
    $query = "INSERT INTO p4_boards (bname) VALUES ('$new_forum_name')";

    $db->query($query)
        or die ($db->error);
}

#Delete board
if(isset($_POST['delete_bid']))
{
    $delete_bid = $_POST['delete_bid'];

    $query = "DELETE p4_posts
                FROM p4_posts
                INNER JOIN p4_topics
                ON topic_id = tid
                INNER JOIN p4_boards
                ON board_id = bid
                WHERE bid = $delete_bid";

    $db->query($query)
        or die ($db->error);

    $query = "DELETE p4_topics FROM p4_topics
                INNER JOIN p4_boards
                ON board_id = bid
                WHERE bid = $delete_bid";

    $db->query($query)
        or die ($db->error);

    $query = "DELETE FROM p4_boards WHERE bid = $delete_bid";

    $db->query($query)
        or die ($db->error);
}
#------------------------------------------------------------------------

#List users
print "<div id='admin_sec'>List of Users:</div>";

$query = "SELECT * FROM p4_users ORDER BY uid ASC";

$result = $db->query($query) or
    die ($db->error);

print "<table>
        <thead>
            <td>Name</td>
            <td>Level</td>
        </thead>";

while ($row = $result->fetch_assoc())
{
    $uid = $row['uid'];
    $username = $row['username'];
    $user_level = $row['user_level'];

    print "<tr>
                <td><a href='p4_user.php?userid=$uid'>$username</a></td>
                <td>" . print_userlevel($active_uid, $uid, $user_level) . "</td>
           </tr>";
}

function print_userlevel($active_uid, $uid, $user_level)
{
    if ($active_uid == $uid)
       $return_text = "Administrator -- Cannot Change";

    else
    {
        $return_text = "<form method='post' action=" . $_SERVER['PHP_SELF'] . ">";;

        if ($user_level == 1)
            $return_text .= '
                <input type="radio" name="level" value="1" checked /> Administrator
                <input type="radio" name="level" value="2" /> Moderator
                <input type="radio" name="level" value="3" /> User
                ';

        elseif ($user_level == 2)
            $return_text .= '
                <input type="radio" name="level" value="1" /> Administrator
                <input type="radio" name="level" value="2" checked /> Moderator
                <input type="radio" name="level" value="3" /> User
                ';

        else
            $return_text .= '
                <input type="radio" name="level" value="1" /> Administrator
                <input type="radio" name="level" value="2" /> Moderator
                <input type="radio" name="level" value="3" checked /> User
                ';

        $return_text .= '<input type="hidden" name="uid" value="$uid" />';
        $return_text .= '<input type="submit" value="Change" /> </form>';
    }

    return $return_text;
}

print "</table>";

#------------------------------------------------------------------------

#Set pagination
print "<div id='admin_sec'>Pagination:</div>";

$query = "SELECT * FROM p4_page";
$result = $db->query($query) or
    die ($db->error);
$row = $result->fetch_assoc();
$pagination = $row['pagination'];

print "Select posts per page: ";
print "<form method='post' action='$self'>
        <input type='number' name='pagination' min='1' max='20' value='$pagination'>
        <input type='submit' value='Change'> </form>
        ";


#------------------------------------------------------------------------

#Add forum
print "<div id='admin_sec'>Add Forum:</div>";

print "<form method='post' action='$self'>
        New Forum Name: <input type='text' name='new_forum_name' />
        <input type='submit' value='Add' /></form>";

#------------------------------------------------------------------------

#Delete forum
print "<div id='admin_sec'>Delete Forum:</div>";

$query = "SELECT * FROM p4_boards ORDER BY bid ASC";

$result = $db->query($query) or
    die ($db->error);


print "<table>";

while ($row = $result->fetch_assoc())
{
    $bid = $row['bid'];
    $bname = $row['bname'];

    print "<tr>
                <td>$bname</td>
                <td><form method='post' action='$self'>
                      <input type='hidden' name='delete_bid' value='$bid' />
                      <input type='submit' value='Delete' /></form></td>
           </tr>";
}

print "</table>";

#------------------------------------------------------------------------

$db->close();