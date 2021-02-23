<!doctype html>
<?php

include_once("header.php");
# на всякий случай проверяем роль у юзера
if (!isset($_SESSION["role"])) header('Location: /login.php');
# если нажали логаут
if (isset($_POST["logout"])) {
unset($_SESSION["uid"]);
unset($_SESSION["login"]);
unset($_SESSION["role"]);
unset($_SESSION["gid"]);
$_SESSION=array();
session_destroy();
header('Location: /login.php');
}
# Получаем список пользователей и групп 
getarrs();

# MENU
echo '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">
<table id="header"><tr>
<td class="logo">&nbsp</td>
<td><button type="submit" name="menu" value="1">Задачи</button></td>
<td><button type="submit" name="menu" value="2">Закрытые</button></td>
<td><button type="submit" name="menu" value="5" style="border-right: 1px solid white;">Добавить</button></td>
<td width="50%">&nbsp</td><td>';
# рисуем форму приветсвия и логаут
echo '<table class="filter"><tr><td align="right">Вы&nbspзашли&nbspкак:</td><td align="center">'.$_SESSION["login"].'</td><td><input name="logout" type="submit" value="Выход"></td></tr>';
echo '</table></td>';
echo '</tr></table></form>';
# PADDING
echo '<div class="content_padding">&nbsp</div>';
# CONTENT пункта меню
if (!isset($_SESSION["menu"])) $_SESSION["menu"]=1;
switch ($_SESSION["menu"]) {
	case 1: # По группам
		filter();
		# Глобальные незакрытые
		echo '<div class="tasks_name">Глобальные незакрытые</div>';
		$query  = "parent_id IS NULL AND status <> 3";
		show_tasks($query);
		# Задачи (не глобальные)
		echo '<div class="tasks_name">Список Задач</div>';
		$query  = "parent_id IS NOT NULL AND status <> 3";
		show_tasks($query);
	break;
	case 2: # Закрытые (вкл.Глобальные)
		filter();
		echo '<div class="tasks_name">Закрытые (вкл.Глобальные)</div>';
		$query  = "status = 3";
		show_tasks($query);
	break;
	case 5: # Добавить 
		echo '<div class="tasks_name">Добавить</div>';
		echo '<form method="POST" action="'.$_SERVER["PHP_SELF"].'"><table>';
		echo '<thead><tr class="col_names">
		<th><div style="width:'.$col_width[0].'px;">id</div></th>
		<th><div style="width:'.$col_width[1].'px;">Gid</div></th>
		<th><div style="width:'.$col_width[2].'px;">creator</div></th>
		<th><div style="width:'.$col_width[3].'px;">name</div></th>
		<th><div style="width:'.$col_width[4].'px;">Важность</div></th>
		<th><div style="width:'.$col_width[5].'px;">Статус</div></th>
		<th><div style="width:'.$col_width[6].'px;">Триггер завершения</div></th>
		<th><div style="width:'.$col_width[7].'px;">Создана</div></th>
		<th><div style="width:'.$col_width[8].'px;">Начало</div></th>
		<th><div style="width:'.$col_width[9].'px;">Закрыта</div></th>
		<th><div style="width:'.$col_width[10].'px;">Время</div></th>
		<th><div style="width:'.$col_width[11].'px;">Группа</div></th>
		<th><div style="width:'.$col_width[12].'px;">Исполнитель</div></th>
		<th><div style="width:'.$col_width[13].'px;"></div></th>
		<th><div style="width:'.$col_width[14].'px;">Зачем</div></th>
		<th><div style="width:'.$col_width[15].'px;">Коммент</div></th>
		</tr></thead>';
		# Кнопка save
		echo '<tr class="row_edit"><td><button name="save" type="submit" value="S" autofocus>Save</button></td>';
		# 2 колонка
		if (isset($_SESSION["global"])) echo '<td>'.$_SESSION["global"].'</td>';
		else echo '<td><input type="checkbox" name="ifglobal"></td>';
		# Creator
		echo '<td>'.$_SESSION["login"].'</td>';
		# Name
		echo '<td width="100%"><textarea name="edit_name" placeholder="Название задачи" required></textarea></td>';
		# Важность
		echo '<td><select name="edit_importance">';
		for($d=0;$d<count($importance_names);$d++) {
			if ($d==1) echo '<option value="'.$d.'" selected>'.$importance_names[$d].'</option>';
			else echo '<option value="'.$d.'">'.$importance_names[$d].'</option>';
		}
		echo '</select></td>';
		# Status
		echo '<td><select name="edit_status">';
		for($d=0;$d<count($status_names);$d++) {
			if ($d==1) echo '<option value="'.$d.'" selected>'.$status_names[$d].'</option>';
			else echo '<option value="'.$d.'">'.$status_names[$d].'</option>';
		}
		echo '</select></td>';
		# End trigger
		echo '<td><textarea name="edit_end_trigger" placeholder="Триггер завершения"></textarea></td>';
		# ДАТА СОЗДАНИЯ
		echo '<td>'.date("d").'/'.date("m").'/'.date("Y").'</td>';
		# ДАТА НАЧАЛА
		echo '<td><input name="edit_start" type="text" id="datepicker" placeholder="01/01/2014" required></td>';
		# ДАТА КОНЦА
		echo '<td>'.showdate("0").'</td>';
		# ЗАТРАЧЕНОЕ ВРЕМЯ
		echo '<td><input type="text" name="edit_time" id="timepicker" placeholder="'.showtime("0").'" value="'.showtime('0').'"></td>';
		# ГРУППА
		echo '<td><select name="edit_gid">';
		echo '<option value="0">ALL</option>';
		for($d=1;$d<=count($_SESSION["groups"]);$d++) echo '<option value="'.$_SESSION["groups"][$d]["gid"].'">'.$_SESSION["groups"][$d]["name"].'</option>';
		echo '</select></td>';
		# ИСПОЛНЯЮЩИЙ
		echo '<td><select name="edit_assigned">';
#		echo '<option value="0">&nbsp</option>';
		for($d=1;$d<=count($_SESSION["users"]);$d++) {
			echo '<option value="'.$_SESSION["users"][$d]["uid"].'"';
			if ($_SESSION["uid"]==$_SESSION["users"][$d]["uid"]) echo ' selected';
			echo '>'.$_SESSION["users"][$d]["login"].'</option>';
		}
		echo '</select></td>';
		# КЛИЕНТ
		echo '<td><select name="edit_cid">';
		echo '<option value="0">&nbsp</option>';
		for($d=1;$d<=count($_SESSION["customer"]);$d++) {
			echo '<option value="'.$_SESSION["customer"][$d]["cid"].'">'.$_SESSION["customer"][$d]["name"].'</option>';
		}
		echo '</select></td>';
		# Для чего
		echo '<td><textarea name="edit_whatfor" placeholder="Для чего"></textarea></td>';
		# Коментарий
		echo '<td><textarea name="edit_comment" placeholder="Комент"></textarea></td>
		</tr>';
		echo '</table>';
	break;
	case 20: # По группам $_SESSION["global"]
		# Выбранная глобальная
		echo '<div class="tasks_name">Глобальная задача</div>';
		$query  = "id='".$_SESSION["global"]."'";
		show_tasks($query);
		# Открытые
		echo '<div class="tasks_name">Открытые подзадачи</div>';
		$query  = "parent_id='".$_SESSION["global"]."' AND status <> 3";
		show_tasks($query);
		# Закрытые
		echo '<div class="tasks_name">Закрытые подзадачи</div>';
		$query  = "parent_id='".$_SESSION["global"]."' AND status = 3";
		show_tasks($query);
	break;
}
#var_dump($_POST);
#print_r($_SESSION);
echo '</body></html>';
mysqli_close($link);
?>