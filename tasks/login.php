<!doctype html>
<?php
include_once("header.php");

if (!isset($_POST["submit"])) unset($message);
if (!isset($message)) $message=" ";
	
# Проверяем есть ли такой пользователь в базе ?
if (isset($_POST["submit"])) {
	$login=htmlspecialchars($_POST["login"], ENT_QUOTES);
	$query="SELECT uid,login,role,in_gr FROM users WHERE login='".$login."' and md5pass='".md5($_POST["password"])."';";
	$result=mysqli_query($link, $query);
	if (!$result) echo 'ERORR SQL ';
	elseif(mysqli_num_rows($result)==1) {
		$row=mysqli_fetch_array($result);
		$_SESSION["uid"]=$row["uid"];
		$_SESSION["login"]=$row["login"];
		$_SESSION["role"]=$row["role"];
		$_SESSION["in_gr"]=$row["in_gr"];
		$sql="SELECT gid,name,adm_uid,deputy_uid FROM groups WHERE adm_uid='".$_SESSION["uid"]."' or deputy_uid='".$_SESSION["uid"]."';";
		if (!$result2=mysqli_query($link, $sql)) echo 'ERORR SQL ';
		else {
			while ($row = mysqli_fetch_row($result2)) {
				$_SESSION["uid_in_gid"][]=$row[0];
			}
		header('Location: /index.php');
		}
	}
else $message="Неверный логин\пароль";
}

# Если юзер не авторизирован тогда выдавать форму логина
if (!isset($_SESSION["role"])) {
echo '
<form method="POST" action="login.php">
<table id="header" width="100%"><tr>
<td class="logo">&nbsp</td>
<td>&nbsp</td><td>
<table class="filter"><tr>
<td>'.$message.'&nbsp</td>
<td><input type="text" name="login" placeholder="Логин" value="" required></td>
<td><input type="password" name="password" value="" placeholder="Пароль"></td>
<td><input name="submit" type="submit" value="Вход"></td>
</tr></table>
</td></tr></table>
</form>';
}
else header('Location: /index.php');

mysqli_close($link);
?>
</body>
</html>