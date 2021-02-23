<?php
session_start();
# Служит для отладки, показывает все ошибки, предупреждения и т.д.
error_reporting(E_ALL);
# Данные для подключения к БД
$dbhost="10.1.32.24:3306";
$dbuser="meok";
$dbpass="lolita";
$dbname="CDRan";
$title="Телефония";
# Вызываем функцию подключения к БД
$link = mysqli_connect("$dbhost", "$dbuser", "$dbpass", "$dbname");
if (mysqli_connect_errno()) {
	printf("Не удалось подключиться: %s", mysqli_connect_error());
	exit;
}
?>
<head>
<link href="/styles/style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php print $title; ?></title>
</head>
<body>