<?php

if ($loggedin == 1 && $can_post == 1 && $locked_topic == 0)
{
    if (isset($_POST['message']) && $_POST['message'] != "")
    {
        $db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
            die ("Hey loser, check your server connection.");

        $get_pid = "SELECT max(pid_in_topic) AS max FROM p4_posts
                        WHERE topic_id = $topicid";
        $result = $db->query($get_pid)
            or die ($db->error);
        $row = $result->fetch_assoc();
        $new_pid = $row['max'] + 1;

        $message = mysqli_real_escape_string($db, $_POST['message']);
        $insert_message = "INSERT INTO p4_posts (topic_id, pid_in_topic, user_id, message)
            VALUES ($topicid, $new_pid, $active_uid, '$message')";
        $db->query($insert_message)
            or die ($db->error);

        require('p4_insertimages.php');

        #update user data
        $update_user = "UPDATE p4_users
            SET number_of_posts = number_of_posts+1,
            date_last_post = CURDATE() WHERE uid = $active_uid";

        $db->query($update_user)
            or die ($db->error);

        update_followers($db, $active_username, $active_uid, $topicid);

        $db->close();

        echo '<script>parent.window.location.reload(true);</script>';
    }
    //Print message submission form
    print
        'Add a new message:
        <form method="post" action=' . $_SERVER["REQUEST_URI"] . ' enctype="multipart/form-data">
        <textarea cols="40" rows="4" name="message" id="message"></textarea>
        <br /> ' . print_upload_dialog($active_level) . '
        <input type="submit" value="Submit">
        </form>';
}

if ($can_post == 0)
{
    print ("<div id='important_note'>Note: You are suspended/banned and cannot post new messages. Check your e-mail for further details.</div>");
}

if ($locked_topic == 1)
{
	print ("<div id='important_note'>Note: Topic is locked. Posting is disabled.</div>");
}

function print_upload_dialog($active_level)
{
    $return = "Upload photos: (optional) <br />";
    if ($active_level == 1 || $active_level == 2 || $active_level >= 5)
        $picture_count = 5;
    else $picture_count = $active_level - 2;

    for ($i = 0; $i<$picture_count; $i++)
        $return .= '<input type="file" name="image_' . $i . '"> <br />';

    return $return;
}

function update_followers($db, $active_username, $active_uid, $topic_id)
{
    $find_followers_query = "SELECT * FROM p4_follow, p4_users
                                WHERE followee_id = $active_uid AND follower_id = uid";
    $results = $db->query($find_followers_query)
        or die($db->error);

    while ($row = $results->fetch_assoc())
    {
        #$username = $row['username'];
        $email = $row['email'];
        $email_type = $row['email_type'];
        send_email($active_username, $topic_id, $email, $email_type);

    }
}

function send_email($active_username, $topic_id, $email, $email_type)
{

    #print ("DEBUG: email: $email username: $username email_type: $email_type <br />");
    $to = $email;
    $subject = "$active_username has posted something!";
    $headers = "From: twerner@cs.odu.edu" . "\r\n";
    if ($email_type == "plain")
    {
        $message = "See $active_username's new post at https://mweigle418.cs.odu.edu/~twerner/proj4/messagelist.php?topicid=$topic_id";
        $mailsent = mail($to, $subject, $message, $headers);
        #print "DEBUG: $mailsent";
    }
    else
    {
        $message = "See $active_username's new post at
         <a href='https://mweigle418.cs.odu.edu/~twerner/proj4/messagelist.php?topicid=$topic_id'>this link</a>.";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        mail($to, $subject, $message, $headers);
    }
}