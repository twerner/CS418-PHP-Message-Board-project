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
    require('p4_authentication.php');

    require('sub/p4_viewboards.php');

    ?>
</div>
</body>
</html>