<?php
$uname = $_POST['uname'];
$passwd = $_POST['passwd'];
$ck_uname = 'meok';
$ck_passwd = 'lol';

if (isset($uname) && $uname==$ck_uname) exit('ok');

if (empty($uname) && empty($passwd)) {
    echo "<div>The username and password are required!</div>";
} else if (empty($uname)) {
    echo "<div>The username is required!</div>";
} else if (empty($passwd)) {
    echo "<div>The password is required!</div>";
} else {
    echo "<div>It works!</div>";
} ?>