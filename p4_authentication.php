<?php
require_once('../config.php');

$loggedin = 0;
$active_username = "";
$active_uid = "";
$active_level = 0;
$can_post = -1;


#Delete cookies and session on logout
if (isset($_GET['logout']) && $_GET['logout'] == 1)
    {
        session_destroy();
        if(isset($_COOKIE['active_username']))
            setcookie("active_username", "", time()-3600);
        if(isset($_COOKIE['active_uid']))
            setcookie("active_uid", "", time()-3600);
        if(isset($_COOKIE['active_level']))
            setcookie("active_level", "", time()-3600);
        if(isset($_COOKIE['can_post']))
            setcookie("can_post", "", time()-3600);

        $loggedin = -1;
    }

#Check for POST data
if (isset($_POST['username'], $_POST['password']) && $_POST['username'] != "" && $_POST['password'] != "" && $loggedin != -1)
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $savecookie = 0;
        if(isset($_POST['savecookie']))
            $savecookie = 1;

        #print "DEBUG: Logging in $username <br />";

        $db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
            die ("Hey loser, check your server connection.");

        $sql = "SELECT * FROM p4_users
            WHERE username = '$username' AND password = '$password'";

        $result = $db->query($sql)
            or die($db->error);

        $db->close();

        $row = $result->fetch_assoc();
        if ($row == "")
            {
                print "Invalid username or password.<br />";
            }
        else
            {
                $active_username = $row['username'];
                $active_uid = $row['uid'];
                $active_level = $row['user_level'];
                $cannot_post = $row['suspended_user'] + $row['banned_user'];

                #print "DEBUG: $active_username $active_uid <br />";

                if ($savecookie == 1)
                    {
                        setcookie('active_username', $active_username, time() + 60*60*24);
                        setcookie('active_uid', $active_uid, time() + 60*60*24);
                        setcookie('active_level', $active_level, time() + 60*60*24);
                        if ($cannot_post == 0)
                            setcookie('can_post', 1, time() + 60*60*24);
                        else setcookie('can_post', 0, time() + 60*60*24);
                    }
                else
                    {
                        $_SESSION['active_username'] = $active_username;
                        $_SESSION['active_uid'] = $active_uid;
                        $_SESSION['active_level'] = $active_level;
                        if ($cannot_post == 0)
                            $_SESSION['can_post'] = 1;
                        else $_SESSION['can_post'] = 0;
                    }

                $loggedin = 1;
            }

    }

#If already logged in
if ($loggedin == 1)
    {
         print "<div id='welcomebox'><div id='welcome'>Welcome, $active_username " . admin_panel($active_level) ."
               <span id='note'><a href='p4_search.php'>Search</a></span>
               <span id='note'><a href='p4_follow.php'>Follow</a></span></div>
                <div id='logout'><a href='p4_boardlist.php?logout=1'>Log Out</a></div></div> <br />";
    }

#Check for existing cookie
elseif (isset($_COOKIE['active_username'], $_COOKIE['active_uid'], $_COOKIE['active_level'])
    && $_COOKIE['active_username'] != "" && $_COOKIE['active_uid'] != "" && $_COOKIE['active_level'] != ""
    && $loggedin != -1)
    {
        #TODO: Verification
        $active_username = $_COOKIE['active_username'];
        $active_uid = $_COOKIE['active_uid'];
        $active_level = $_COOKIE['active_level'];
        $can_post = $_COOKIE['can_post'];

        $loggedin = 1;

        print "<div id='welcomebox'><div id='welcome'>Welcome, $active_username " . admin_panel($active_level) ."
               <span id='note'><a href='p4_search.php'>Search</a></span>
               <span id='note'><a href='p4_follow.php'>Follow</a></span></div>
               <div id='logout'><a href='p4_boardlist.php?logout=1'>Log Out</a></div></div> <br />";
    }

#Check for active session
elseif (isset($_SESSION['active_username'], $_SESSION['active_uid'], $_SESSION['active_level'])
    && $_SESSION['active_username'] != "" && $_SESSION['active_uid'] != "" && $_SESSION['active_level'] != ""
    && $loggedin != -1)
    {
        $active_username = $_SESSION['active_username'];
        $active_uid = $_SESSION['active_uid'];
        $active_level = $_SESSION['active_level'];
        $can_post = $_SESSION['can_post'];

        $loggedin = 1;

        print "<div id='welcomebox'><div id='welcome'>Welcome, $active_username " . admin_panel($active_level) ."
               <span id='note'><a href='p4_search.php'>Search</a></span>
               <span id='note'><a href='p4_follow.php'>Follow</a></span></div>
               <div id='logout'><a href='p4_boardlist.php?logout=1'>Log Out</a></div></div> <br />";
    }

#Handle guests
else
    {
        $active_username = "Guest";
        $active_uid = -1;

        print
            'Welcome, guest! (<a href="p4_newuser.php">New user?</a>) (<a href="p4_forgotpassword.php">Forgot your password?</a>) <br />
            <div id="login"><form method="post" action="p4_boardlist.php">
            Username: <input type="text" name="username" id="username" /><br />
            Password: <input type="password" name="password" id="password" /><br />
            Stay logged in? <input type="checkbox" name="savecookie" id="savecookie" /><br />
            <input type="submit" value="Submit"></form></div>';

    }

#If admin
function admin_panel($active_level)
{
	if ($active_level == 1)
		{
			return "<span id='note'> <a href='p4_adminpanel.php'>Access Admin Panel</a></span>";
		}
}

if (isset($adminpanel))
    if ($active_level == 1)
	   require "sub/p4_admincontents.php";
    else
        print "<div id='error'>You are not an administrator.</div>";