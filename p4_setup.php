<?php
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

function init(){
    $db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
        die ("Hey loser, check your server connection.");

    $create_users =
        "CREATE TABLE IF NOT EXISTS p4_users
            (uid INT AUTO_INCREMENT,
             username VARCHAR(64) UNIQUE NOT NULL,
             password VARCHAR(128) NOT NULL,
             email VARCHAR(128) UNIQUE NOT NULL,
             email_type BOOLEAN DEFAULT 0,
             reg_code VARCHAR(128),

             user_level INT DEFAULT 3,
             number_of_posts INT DEFAULT 0,
             number_of_topics INT DEFAULT 0,
             date_registered DATE,
             date_last_post DATE DEFAULT NULL,

             confirmed_user BOOLEAN DEFAULT 0,
             suspended_user BOOLEAN DEFAULT 0,
             banned_user BOOLEAN DEFAULT 0,

             avatar BOOLEAN DEFAULT 0,
             avatar_filename VARCHAR(128),
             PRIMARY KEY (uid)
            )";

    $create_boards =
        "CREATE TABLE IF NOT EXISTS p4_boards
            (bid INT AUTO_INCREMENT,
             bname VARCHAR(64) UNIQUE,
             b_order int UNIQUE,
             PRIMARY KEY (bid)
            )";

    $create_topics =
        "CREATE TABLE IF NOT EXISTS p4_topics
            (tid INT AUTO_INCREMENT,
             board_id INT NOT NULL,
             user_id INT NOT NULL,
             topic_title VARCHAR(256),
             create_time TIMESTAMP,
			 locked_topic BOOLEAN DEFAULT 0,
             PRIMARY KEY (tid),
             FULLTEXT (topic_title)
            ) ENGINE=MYISAM;";

    $create_posts =
    "CREATE TABLE IF NOT EXISTS p4_posts
            (pid INT AUTO_INCREMENT,
             topic_id INT NOT NULL,
             pid_in_topic INT NOT NULL,
             user_id INT NOT NULL,
             message VARCHAR(4096) NOT NULL,
             time TIMESTAMP,
             edited BOOLEAN DEFAULT 0,
             edit_time TIMESTAMP,
             edit_user INT DEFAULT -1,
             deleted BOOLEAN DEFAULT 0,
             PRIMARY KEY (pid),
             FULLTEXT (message)
            ) ENGINE=MYISAM;";

    $create_pagination =
    "CREATE TABLE IF NOT EXISTS p4_page
            (pagination INT DEFAULT 5
            )";

    $create_picture_upload =
    "CREATE TABLE IF NOT EXISTS p4_pictures
            (post_id INT,
             picture_filename VARCHAR(128)
            )";

    $create_person_subscribe =
    "CREATE TABLE IF NOT EXISTS p4_follow
            (follower_id INT,
             followee_id INT
            )";

    $insert_pagination = "INSERT INTO p4_page (pagination) VALUES (5)";

    $db->query($create_users)
        or die ($db->error);
    $db->query($create_boards)
        or die ($db->error);
    $db->query($create_topics)
        or die ($db->error);
    $db->query($create_posts)
        or die ($db->error);
    $db->query($create_pagination)
        or die ($db->error);
    $db->query($insert_pagination)
        or die ($db->error);
    $db->query($create_picture_upload)
        or die ($db->error);
    $db->query($create_person_subscribe)
        or die ($db->error);

    $db->close();
}

print "Creating tables...<br />";
init();
print "Created.";

?>
</div>
</body>
</html>