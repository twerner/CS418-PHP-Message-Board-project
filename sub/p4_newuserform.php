<?php

require 'CaptchasDotNet.php';

$captchas = new CaptchasDotNet ('demo', 'secret',
                                'captcha/captchasnet-random-strings','3600',
                                'abcdefghkmnopqrstuvwxyz','6',
                                '240','80','000088');

if (isset($_POST['new_username'], $_POST['new_password'], $_POST['new_password2'], $_POST['new_email'], $_POST['new_email_type'], $_POST['captcha'], $_POST['random'])
		&& $_POST['new_username'] != "" && $_POST['new_password'] != "" && $_POST['new_password2'] != ""
		&& $_POST['new_email'] != "" && $_POST['captcha'] != "")
{
	$username = $_POST['new_username'];
	$password = $_POST['new_password'];
	$password2 = $_POST['new_password2'];
	$email = $_POST['new_email'];
	$email_type = $_POST['new_email_type'];

    $captcha = $_POST['captcha'];
    $random = $_POST['random'];

	$error_to_break_on = 0;

    if (!$captchas->validate($random))
    {
        print "Error: There was a server-side problem with your captcha. Please go back and try again. <br />";
        $error_to_break_on = 1;
    }

    else if (!$captchas->verify($captcha))
    {
        print "Error: You mistyped the captcha. Please go back and try again <br />";
        $error_to_break_on = 1;
    }

	if ($password != $password2)
	{
		print "Error: Passwords do not match.<br />";
		$error_to_break_on = 1;
	}

	$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB) or
            die ("Hey loser, check your server connection.");
    $check_username = "SELECT * FROM p4_users WHERE username = '$username'";
    $result = $db->query($check_username)
    	or die ($db->error);
    if ($result->num_rows)
    {
    	print "Error: Username already exists.<br />";
    	$error_to_break_on = 1;
    }

    $check_email = "SELECT * FROM p4_users WHERE email = '$email'";
    $result = $db->query($check_email)
    	or die ($db->error);
    if ($result->num_rows)
    {
    	print "Error: Email address already in use.<br />";
    	$error_to_break_on = 1;
    }

    $avatar = 0;
    if ($_FILES['avatar']['tmp_name'])
    {
    	$avatar = 1;
    	$avatar_filename = md5($username . 'abc' . $email . $_FILES['avatar']['name']) . '.jpg';
    	if (!move_uploaded_file($_FILES['avatar']['tmp_name'], 'avatars/' . $avatar_filename))
    		$avatar = 0;
    	makeThumbnails($avatar_filename);
    }

    if (!$error_to_break_on)
    {
	    $reg_code = md5($username . "blahblah");
	    #$password = crypt($password);

	    if ($email_type == 'plain')
	    	$email_bool = 0;
	    else
	    	$email_bool = 1;

	    if ($avatar == 1)
	    	$new_user = "INSERT INTO p4_users (username, password, email, email_type, avatar, avatar_filename, reg_code, date_registered)
	    		VALUES ('$username', '$password', '$email', '$email_bool', 1, '$avatar_filename', '$reg_code', CURDATE())";
	    else
	    	$new_user = "INSERT INTO p4_users (username, password, email, email_type, reg_code, date_registered)
	    		VALUES ('$username', '$password', '$email', '$email_bool', '$reg_code', CURDATE())";
	    $db->query($new_user)
	    	or die ($db->error);

	    $db->close();

	    send_email($username, $reg_code, $email, $email_type);

	    print "Registration complete. Please check your e-mail for a link to verify and activate your account.<br /><br />";
	}

}



print 'Register to become a new user:';
print ('<form method="post" action=' . $_SERVER["REQUEST_URI"] . ' enctype="multipart/form-data">
Username: <input type="text" name="new_username"><br />
Password: <input type="password" name="new_password"><br />
Confirm password: <input type="password" name="new_password2"><br />
Avatar: <input type="file" name="avatar"> <br />
E-mail: <input type="email" name="new_email"><br />
E-mail format: <input type="radio" name="new_email_type" value="plain">Plain text
<input type="radio" name="new_email_type" value="html">HTML<br />
<input type="hidden" name="random" value="' . $captchas->random () . '" />'
 .  $captchas->image ()  .  '<a href="javascript:captchas_image_reload("captchas.net")">Reload Image</a><br />
<input type="text" name="captcha" /><br />
<input type="submit" value="Submit">
</form>');

function send_email($username, $reg_code, $email, $email_type)
{

	#print ("DEBUG: email: $email username: $username email_type: $email_type <br />");
	$to = $email;
	$subject = "Welcome to Message Board Message Board";
	$headers = "From: twerner@cs.odu.edu" . "\r\n";
	if ($email_type == "plain")
	{
		$message = "To complete registration, please visit the following link: https://mweigle418.cs.odu.edu/~twerner/proj4/p4_accountverify.php?regcode=$reg_code";
		$mailsent = mail($to, $subject, $message, $headers);
		#print "DEBUG: $mailsent";
	}
	else
	{
		$message = "To complete registration, please visit
		 <a href='https://mweigle418.cs.odu.edu/~twerner/proj4/p4_accountverify.php?regcode=$reg_code'>this link</a>.";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		mail($to, $subject, $message, $headers);
	}
}

function makeThumbnails($img)
{
    $thumbnail_width = 150;
    $thumbnail_height = 100;
    $arr_image_details = getimagesize("avatars/$img"); // pass id to thumb name
    $original_width = $arr_image_details[0];
    $original_height = $arr_image_details[1];
    if ($original_width > $original_height) {
        $new_width = $thumbnail_width;
        $new_height = intval($original_height * $new_width / $original_width);
    } else {
        $new_height = $thumbnail_height;
        $new_width = intval($original_width * $new_height / $original_height);
    }
    $dest_x = intval(($thumbnail_width - $new_width) / 2);
    $dest_y = intval(($thumbnail_height - $new_height) / 2);
    if ($arr_image_details[2] == 1) {
        $imgt = "ImageGIF";
        $imgcreatefrom = "ImageCreateFromGIF";
    }
    if ($arr_image_details[2] == 2) {
        $imgt = "ImageJPEG";
        $imgcreatefrom = "ImageCreateFromJPEG";
    }
    if ($arr_image_details[2] == 3) {
        $imgt = "ImagePNG";
        $imgcreatefrom = "ImageCreateFromPNG";
    }
    if ($imgt) {
        $old_image = $imgcreatefrom("avatars/$img");
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        $imgt($new_image, "avatars/t-$img");
    }
}