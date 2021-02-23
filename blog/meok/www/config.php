<?php
session_start();
# Служит для отладки, показывает все ошибки, предупреждения и т.д.
error_reporting(E_ALL);
$title="МЕГАСАЙТ";
$phonen="+7(495)111-0000";
$m_params['seo_words'] = array('test1'/*,'test2','test3','test4','test5','test6','test7'*/);
$m_params['err'] = '&nbsp;';
# BD Config
$dbhost="localhost";
$dbuser="meok";
$dbpass="lolita";
$dbname="meok";
# BD Connect
$sql_conn = mysqli_connect("$dbhost", "$dbuser", "$dbpass", "$dbname");
if (mysqli_connect_errno()) {
	printf("Не удалось подключиться: %s", mysqli_connect_error());
	exit;
}
# Функции
include_once("www/functions.php"); #SQL & ARR func
# Параметры страницы - тут для API
$m_params += url_params();
include_once("www/dodo.php"); # API return exit()
$m_params += module_params($m_params['name']); # SQL - тут: данные параметры не нужны для API
include_once("www/construct.php"); #HTML blocks builder
#phpinfo();
?>