<?php
session_start();
# Служит для отладки, показывает все ошибки, предупреждения и т.д.
error_reporting(E_ALL);
# Данные для подключения к БД
$dbhost="127.0.0.1";
$dbuser="root";
$dbpass="lolita";
$dbname="tasks";
$title="Задачи";
$importance_names=array("высокий","обычный","низкий");
$status_names=array("Запланировано","в&nbspработе","Приостановлено","Выполнено","Отменено");
# Ширина столбцов
$col_width=array(40,40,60,240,70,100,120,65,65,65,40,30,60,60,80,80); # сумма 1210
# Недопустимы символы в input строке
$disabled_symbols="/[\\\~^°!\"§$%\/()=?`';,\.:_{\[\]}\|<>@+#]/";
# Вызываем функцию подключения к БД
$link = mysqli_connect("$dbhost", "$dbuser", "$dbpass", "$dbname");
if (!$link) echo "error";
if (mysqli_connect_errno()) {
	printf("Не удалось подключиться: %s", mysqli_connect_error());
	exit;
}

include_once("functions.php");

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<link href="style.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php print $title; ?></title>
  <link rel="stylesheet" href="jquery-ui.css" />
  <script type="text/javascript" src="jquery-1.9.1.js"></script>
  <script type="text/javascript" src="jquery-ui.js"></script>
  <script type="text/javascript" src="jquery.maskedinput.js"></script>
  <script type="text/javascript">
$(function() {
    $( "#datepicker" ).datepicker();
	$( "#datepicker" ).datepicker( "option", "firstDay", 1 );
    $( "#datepicker" ).datepicker( "option", "dateFormat", "dd/mm/yy" );
	$( "#datepicker" ).mask("39/19/2099");
	$( "#timepicker" ).mask("999:59");
});
function open_task (numb) {
$.post('index.php', { edit: numb }); 
location.reload();
};
function filter1 (numb1) {
$.post('index.php', { filter_assigned: numb1 }); 
location.reload();
};
function filter2 (numb2) {
$.post('index.php', { filter_day: numb2 }); 
location.reload();
};
</script>
  
  
</head>
<body>