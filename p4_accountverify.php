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
    #require('p4_authentication.php');

    #check code against db
    #switch flag

    if (isset($_GET['regcode']) && $_GET['regcode'] != "")
    {
        $reg_code = $_GET['regcode'];

        $db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
            die ("Hey loser, check your server connection.");
        $check_code = "SELECT * FROM p4_users WHERE reg_code = '$reg_code'";
        $result = $db->query($check_code)
            or die ($db->error);
        if ($result->num_rows)
        {
            $swtich_flag_code = "UPDATE p4_users SET confirmed_user = 1, reg_code = NULL WHERE reg_code = '$reg_code'";
			$db->query($swtich_flag_code)
				or die ($db->error);
            print "Account active!";
        }
        else
        {
            print "Invalid code.";
        }

        $db->close();
    }

    ?>
</div>
</body>
</html>