<?php
include_once("www/config.php");
# SEO слова, переделать под SQL для каждой страницы
echo '<!doctype html>
<html lang="ru">
<head>
<title>'.$title.'</title>
<link href="style.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="favicon.png">
<script src="fu.js"></script>
<script src="post.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="meok web site">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="meok">
<meta name="keywords" content="'.join(', ', $m_params['seo_words']).'">';
echo '</head>
<body><form method="POST">';
#==============================================================
# LOGO
echo '<div class="logo wrap"><span>'.$title.'</span><span>'.$phonen.'</span></div>';
#==============================================================
# TOPBAR
echo '<div class="topbar"><div class="wrap">'.build_menu($m_params['menu'],'menu');
# ADMIN MENU
if (chk_role(2)) echo build_menu(3,'menu');
# Сообщение для отладки
echo '<ul class="login"><li id="crit">'.$m_params['err'].'</li>';
# Менюшка входа или юзера
echo acc_menu().'</ul>';
echo '</div></div>';
#==============================================================
# CONTENT
if (chk_role($m_params['role']) || $m_params['role']>=8) {
	echo '<div class="wrap"><h2>'.$m_params['func'].'</h2>'; # Параметр модуля (проверка)
	echo $m_params['func']().'</div>'; # тут вызывается функция модуля
}
else echo '<div class="wrap">NO PERMISSIONS</div>';
echo '<div class="wrap">END PAGE</div>';
# END PAGE
mysqli_close($link);
echo '</form></body></html>';
?>
