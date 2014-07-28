<?php

$find_message = "SELECT * FROM p4_posts
                 WHERE topic_id = $topicid AND user_id = $active_uid AND message = '$message'
                 ORDER BY time DESC LIMIT 1";
$result = $db->query($find_message)
    or die ($db->error);
$row = $result->fetch_assoc();
$pid =  $row['pid'];

if (isset($_FILES['image_0']))
{
	$filename = $pid . '-0.jpg';
	if (move_uploaded_file($_FILES['image_0']['tmp_name'], 'images/' . $filename))
	{
		$query = "INSERT INTO p4_pictures (post_id, picture_filename)
				  VALUES ($pid, '$filename')";
		$db->query($query)
			or die ($db->error);

		makeThumbnails($filename);
	}
}
if (isset($_FILES['image_1']))
{
	$filename = $pid . '-1.jpg';
	if (move_uploaded_file($_FILES['image_1']['tmp_name'], 'images/' . $filename))
	{
		$query = "INSERT INTO p4_pictures (post_id, picture_filename)
				  VALUES ($pid, '$filename')";
		$db->query($query)
			or die ($db->error);

		makeThumbnails($filename);
	}
}
if (isset($_FILES['image_2']))
{
	$filename = $pid . '-2.jpg';
	if (move_uploaded_file($_FILES['image_2']['tmp_name'], 'images/' . $filename))
	{
		$query = "INSERT INTO p4_pictures (post_id, picture_filename)
				  VALUES ($pid, '$filename')";
		$db->query($query)
			or die ($db->error);

		makeThumbnails($filename);
	}
}
if (isset($_FILES['image_3']))
{
	$filename = $pid . '-3.jpg';
	if (move_uploaded_file($_FILES['image_3']['tmp_name'], 'images/' . $filename))
	{
		$query = "INSERT INTO p4_pictures (post_id, picture_filename)
				  VALUES ($pid, '$filename')";
		$db->query($query)
			or die ($db->error);

		makeThumbnails($filename);
	}
}
if (isset($_FILES['image_4']))
{
	$filename = $pid . '-4.jpg';
	if (move_uploaded_file($_FILES['image_4']['tmp_name'], 'images/' . $filename))
	{
		$query = "INSERT INTO p4_pictures (post_id, picture_filename)
				  VALUES ($pid, '$filename')";
		$db->query($query)
			or die ($db->error);

		makeThumbnails($filename);
	}
}

function makeThumbnails($img)
{
    $thumbnail_width = 100;
    $thumbnail_height = 100;
    $arr_image_details = getimagesize("images/$img"); // pass id to thumb name
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
        $old_image = $imgcreatefrom("images/$img");
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        $imgt($new_image, "images/t-$img");
    }
}