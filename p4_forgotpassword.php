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

    #prompt for e-mail
    #send e-mail if address valid

    #prompt for new pw
    #update db

    if (isset($_GET['pw_code'], $_POST['new_pw']) && $_GET['pw_code'] != "" && $_POST['new_pw'] != "")
    {
        $pw_code = $_GET['pw_code'];
        $new_pw = $_POST['new_pw'];

        $db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
            die ("Hey loser, check your server connection.");
        $check_code = "SELECT * FROM p4_users WHERE reg_code = '$pw_code'";
        $result = $db->query($check_code)
            or die ($db->error);
        if ($result->num_rows)
        {
            $row = $result->fetch_array();
            $uid = $row['uid'];

            $update_pw = "UPDATE p4_users SET password = '$new_pw', reg_code = NULL
                WHERE uid = '$uid'";
			$db->query($update_pw)
				or die ($db->error);
				
            print "Password updated.";
        }
        else
        {
            print "Invalid code.";
        }

        $db->close();
    }

    else if (isset($_GET['pw_code']) && $_GET['pw_code'] != "")
    {
        $pw_code = $_GET['pw_code'];

        $db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
            die ("Hey loser, check your server connection.");
        $check_code = "SELECT * FROM p4_users WHERE reg_code = '$pw_code'";
        $result = $db->query($check_code)
            or die ($db->error);
        if ($result->num_rows)
        {
            $row = $result->fetch_array();
            $uid = $row['uid'];

            print "Enter new password:";
            print '<form method="post" action=' . $_SERVER["REQUEST_URI"] . '>
                    New password: <input type="password" name="new_pw"><br />
                    <input type="submit" value="Submit">
                    </form>';
        }
        else
        {
            print "Invalid code.";
        }

        $db->close();

    }

    else if (isset($_POST['email']) && $_POST['email'] != "")
    {
        $email = $_POST['email'];

        $db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
                    die ("Hey loser, check your server connection.");
        $check_email = "SELECT * FROM p4_users WHERE email = '$email'";
        $result = $db->query($check_email)
            or die ($db->error);
        if ($result->num_rows)
        {
            $pw_code = md5($email . 'esdjioxv' . 'time()');
            $row = $result->fetch_array();
            $username = $row['username'];
            $email_type = $row['email_type'];

			$add_pwcode = "UPDATE p4_users SET reg_code = '$pw_code' WHERE email = '$email'";
			$db->query($add_pwcode)
				or die ($db->error);
			
            send_email($username, $pw_code, $email, $email_type);
			
			print 'E-mail sent. Check your e-mail account to reset your password.';
        }
        else
        {
            print 'Invalid e-mail address.';
        }

        $db->close();
    }

    else
    {
        print 'Forgot your password? Enter your e-mail address here to begin the reset process.';
        print '<form method="post" action=' . $_SERVER["REQUEST_URI"] . '>
        E-mail address: <input type="email" name="email"><br />
        <input type="submit" value="Submit">
        </form>';
    }

    ?>
</div>
</body>
</html>

<?php
function send_email($username, $reg_code, $email, $email_type)
{
    $to = $email;
    $subject = "Message Board Message Board Password Reset";
    if ($email_type == "plain")
    {
        $message = "To reset your password, please visit the following link: https://mweigle418.cs.odu.edu/~twerner/proj4/p4_accountverify.php?pw_code=$reg_code";
        mail($to, $subject, $message, $headers);
    }
    else
    {
        $message = "To reset your password, please visit
         <a href='https://mweigle418.cs.odu.edu/~twerner/proj4/p4_forgotpassword.php?pw_code=$reg_code'>this link</a>.";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        mail($to, $subject, $message, $headers);
    }
}