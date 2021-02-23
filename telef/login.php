<!doctype html PUBLIC>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<?php
include_once("header.php");

if (!isset($_POST["submit"])) unset($message);
if (!isset($message)) $message=" ";
	
# Проверяем есть ли такой пользователь в базе ?
if (isset($_POST["submit"])) {
$query="SELECT login,role FROM users WHERE login='".$_POST["login"]."' and password='".$_POST["password"]."'";
$result=mysqli_query($link, $query);
if (!$result) echo 'ERORR SQL ';
	if(mysqli_num_rows($result)==1) {
	$row=mysqli_fetch_array($result);
	$_SESSION["login"]=$row["login"];
	$_SESSION["role"]=$row["role"];
	header('Location: /index.php');
	}
	else $message="Неверный логин\пароль";
}

# Если юзер не авторизирован тогда выдавать форму логина
if (!isset($_SESSION["role"])) {
echo '
<form method="POST" action="login.php">
<table id="menu"><tr>
<td>'.$title.'&nbsp</td>
<td class="text" style="width: 486px;">'.$message.'</td>
<td><input type="text" name="login" placeholder="Логин" value="" required></td>
<td><input type="password" name="password" value="" placeholder="Пароль"></td>
<td><input name="submit" type="submit" value="Вход"></td>
</tr></table>
</form>';
}
else header('Location: /index.php');

mysqli_close($link);
?>
</body>
</html>