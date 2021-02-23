<?php
# POST 

# Выбираем пункт меню
if (isset($_POST["menu"])) {
	edit_unset();
	unset($_SESSION["filter"]);
	$_SESSION["menu"]=$_POST["menu"];
	if (isset($_SESSION["global"])) unset($_SESSION["global"]);
}
# Добавить строку в базу Мускуль
if (isset($_POST["save"])) {
	if (post_sess()) {
		if ($_SESSION["menu"]==5) {
			$query = "INSERT INTO tasks (parent_id,creator_uid,name,importance,status,end_trigger,date_create,date_start,date_end,time_spent,gid,assigned_uid,cid,whatfor,coment) VALUES ";
			if ($_SESSION["col"][1]==="null") $query .="(NULL";
			else $query .="('".$_SESSION["col"][1]."'";
			$query .= ",'".$_SESSION["uid"]."'";
			$query .= ",'".$_SESSION["col"][3]."'";
			$query .= ",'".$_SESSION["col"][4]."'";
			$query .= ($_SESSION["col"][5]==3) ? ",'1'" : ",'".$_SESSION["col"][5]."'"; # Нельзя создать закрытую задачу
			$query .= ",'".$_SESSION["col"][6]."'";
			$query .= ",'".time()."'";
			$query .= ",'".$_SESSION["col"][8]."'";
			$query .= ",'0'";
			$query .= ",'".$_SESSION["col"][10]."'";
			$query .= ",'".$_SESSION["col"][11]."'";
			$query .= ",'".$_SESSION["col"][12]."'";
			$query .= ",'".$_SESSION["col"][13]."'";
			$query .= ",'".$_SESSION["col"][14]."'";
			$query .= ",'".$_SESSION["col"][15]."');";
		}
		elseif (check_role($_SESSION["col"][11],$_SESSION["col"][12])) {
			$query = "UPDATE tasks SET";
			$query .= " name ='".$_SESSION["col"][3]."'";
			$query .= ($_SESSION["col"][1]==='null') ? ",parent_id = NULL" : "";
			$query .= ",importance ='".$_SESSION["col"][4]."'";
			$query .= ",status ='".$_SESSION["col"][5]."'";
			$query .= ",end_trigger ='".$_SESSION["col"][6]."'";
			$query .= ",date_start ='".$_SESSION["col"][8]."'";
			$query .= ($_SESSION["col"][5]==3) ? ",date_end ='".time()."'" : "";
			$query .= ",time_spent ='".$_SESSION["col"][10]."'";
			$query .= ",gid ='".$_SESSION["col"][11]."'";
			$query .= ",assigned_uid ='".$_SESSION["col"][12]."'";
			$query .= ",cid ='".$_SESSION["col"][13]."'";
			$query .= ",whatfor ='".$_SESSION["col"][14]."'";
			$query .= ",coment ='".$_SESSION["col"][15]."'";
			$query .= " WHERE id='".$_SESSION["edit_row"]."';";
		}
		else echo 'Нет прав на редактирование';
		if (!mysqli_query($link, $query)) {
			echo 'WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING<br>';
		}
#	echo '<code>'.$query.'</code>';
	}
edit_unset();
}
# ФИЛЬТРЫ
if (!isset($_SESSION["filter"]["assigned"])) $_SESSION["filter"]["assigned"]=0;
if (!isset($_SESSION["filter"]["day"])) $_SESSION["filter"]["day"]=0;
if (!isset($_SESSION["filter"]["weight"])) $_SESSION["filter"]["weight"]=0;
if (isset($_POST["filter_assigned"]) and is_numeric($_POST["filter_assigned"]) and $_POST["filter_assigned"]>=0) $_SESSION["filter"]["assigned"]=$_POST["filter_assigned"];
if (isset($_POST["filter_day"]) and is_numeric($_POST["filter_day"]) and $_POST["filter_day"]>=0) $_SESSION["filter"]["day"]=$_POST["filter_day"];
if (isset($_POST["filter_weight"])) $_SESSION["filter"]["weight"] = ($_POST["filter_weight"]=="Order:On") ? 1 : 0;
# кнопка редактирования задач
if (isset($_POST["edit"])) {
	edit_unset();
	if (is_numeric($_POST["edit"]) and $_POST["edit"]>0) {
		$_SESSION["edit_row"]=$_POST["edit"];
	}
	elseif ($_POST["edit"]==0) {
		echo 'Отмена редактирования</br>';
	}
	else {
		echo 'Warning: critical error POST["edit"]</br>';
	}
}
# переход к глобальной задаче(подзадачи)
if (isset($_POST["show_global"])) {
	edit_unset();
	if (is_numeric($_POST["show_global"]) and $_POST["show_global"]>0) {
		$_SESSION["global"]=$_POST["show_global"];
		if ($_SESSION["menu"]==20) $_SESSION["menu"]=5;
		else $_SESSION["menu"]=20;
	}
	else {
		echo 'Display critical error POST["show_global"]';
		return false;
	}
}
# Отмена редактирования
function edit_unset() {
	if (isset($_SESSION["edit_row"])) unset($_SESSION["edit_row"]);
	if (isset($_SESSION["col"][0])) unset($_SESSION["col"]);
}

# Проверка $_POST строки и перенос в массив $_SESSION["col"][0-15]
# ! ! ! W A R N I N G ! ! ! PROTECTION ! ! !
# $_POST вводимой строки
function post_sess() {
# show_global edit_name edit_importance edit_status edit_end_trigger edit_time_h edit_time_m edit_gid edit_assigned edit_cid edit_whatfor edit_comment
	global $disabled_symbols;
	$_SESSION["col"][1]=0;
	if (isset($_POST["ifglobal"])) $_SESSION["col"][1]='null';
	elseif (isset($_SESSION["global"])) $_SESSION["col"][1]=$_SESSION["global"];
	# Имя
	if (isset($_POST["edit_name"])) {
		$_SESSION["col"][3]=htmlspecialchars($_POST["edit_name"], ENT_QUOTES);
#		if (!preg_match($disabled_symbols, $_POST["edit_name"])) $_SESSION["col"][3]=$_POST["edit_name"];
#		else {
#			echo 'Display critical error POST["edit_name"]</br>';
#			return false;
#		}
	}	
	# Важность
	if (isset($_POST["edit_importance"])) {
		if (is_numeric($_POST["edit_importance"]) and $_POST["edit_importance"]>=0 and $_POST["edit_importance"]<15) {
			$_SESSION["col"][4]=$_POST["edit_importance"];
		}
		else {
			echo 'Display critical error POST["edit_importance"]</br>';
			return false;
		}
	}
	# Статус
	if (isset($_POST["edit_status"])) {
		if (is_numeric($_POST["edit_status"]) and $_POST["edit_status"]>=0 and $_POST["edit_status"]<15) {
			$_SESSION["col"][5]=$_POST["edit_status"];
		}
		else {
			echo 'Display critical error POST["edit_status"]</br>';
			return false;
		}
	}
	# Триггер завершения - проверка по регулярному выражению
	if (isset($_POST["edit_end_trigger"])) $_SESSION["col"][6]=htmlspecialchars($_POST["edit_end_trigger"], ENT_QUOTES);
	# Дата старта
	if (isset($_POST["edit_start"])) {
		if (!empty($_POST["edit_start"])) {
			$tmp_d=substr($_POST["edit_start"],0,2);
			$tmp_m=substr($_POST["edit_start"],3,2);
			$tmp_y=substr($_POST["edit_start"],-4);
			if (is_numeric($tmp_d) and is_numeric($tmp_m) and is_numeric($tmp_y) and $tmp_d>0 and $tmp_d<=31 and $tmp_m>0 and $tmp_m<=12 and $tmp_y>0 and $tmp_y<=5000) $_SESSION["col"][8]=mktime(0,0,0,$tmp_m,$tmp_d,$tmp_y);
		}
		else { 
			echo 'Дата старта - обязательное поле</br>';
			return false;
		}
	}
	# Время
	if (isset($_POST["edit_time"])) {
		$tmp_m=substr($_POST["edit_time"],-2);
		$tmp_h=substr($_POST["edit_time"],0,3);
		if (is_numeric($tmp_m) and $tmp_m>=0 and $tmp_m<60 and is_numeric($tmp_h) and $tmp_h>=0 and $tmp_h<=999) {
			$_SESSION["col"][10]=($tmp_h*60+$tmp_m);
		}
		else {
			$_SESSION["col"][10]=0;
			echo 'Display critical error POST["edit_time"]</br>';
			return false;
		}
	}
	# Группа
	if (isset($_POST["edit_gid"])) {
		if (is_numeric($_POST["edit_gid"]) and $_POST["edit_gid"]>=0 and $_POST["edit_gid"]<99) {
			$_SESSION["col"][11]=$_POST["edit_gid"];
		}
		else {
			echo 'Display critical error POST["edit_gid"]</br>';
			return false;
		}
	}
	# На кого назначено
	if (isset($_POST["edit_assigned"])) {
		if (is_numeric($_POST["edit_assigned"]) and $_POST["edit_assigned"]>0 and $_POST["edit_assigned"]<99) {
			$_SESSION["col"][12]=$_POST["edit_assigned"];
		}
		else {
			echo 'Display critical error POST["edit_assigned"] Задача не может быть не назначеной</br>';
			return false;
		}
	}
	# Заказчик
	if (isset($_POST["edit_cid"])) {
		if (is_numeric($_POST["edit_cid"]) and $_POST["edit_cid"]>=0 and $_POST["edit_cid"]<99) {
			$_SESSION["col"][13]=$_POST["edit_cid"];
		}
		else {
			echo 'Display critical error POST["edit_cid"]</br>';
			return false;
		}
	}
	# Для чего
	if (isset($_POST["edit_whatfor"])) $_SESSION["col"][14]=htmlspecialchars($_POST["edit_whatfor"], ENT_QUOTES);
	# Комент
	if (isset($_POST["edit_comment"])) $_SESSION["col"][15]=htmlspecialchars($_POST["edit_comment"], ENT_QUOTES);
	return true;
}
# Показать дату
function showdate($date) {
if ($date==0) return '--/--/----';
else return date("d/m/Y",$date);
#else return date("d",$date).'/'.date("m",$date).'/'.date("Y",$date);
}
# Показать время
function showtime($time) {
	if ($time ==0) return '000:00';
	if (intval($time/60) > 999) return 'err E+';
	else {
		$count=3-strlen(intval($time/60));
		$zeros='';
		for ($d=0;$d<$count;$d++) $zeros .= '0'; 
		return $zeros.intval($time/60).':'.date("s",$time);
	}
}
# Права на изменения/сохранения
function check_role($row11,$row12) {
	if (isset($_SESSION["role"]) and $_SESSION["role"]==1) return true;
	elseif (isset($row11) and in_array($row11,$_SESSION["uid_in_gid"]) and $_SESSION["menu"]!=2) return true;
	elseif (isset($row12) and $_SESSION["uid"]==$row12 and $_SESSION["menu"]!=2) return true;
	else return false;
}
# Кнопка глобальных задач
function show_global_task($row0,$row1) {
	if ($_SESSION["menu"]==2) {
		if (is_null($row1)) return 'Glob';
		else return ($row1==0) ? '-' : $row1;
	}
	if ($_SESSION["menu"]==20) return ($row1===null) ? '<button name="show_global" type="submit" value="'.$row0.'">+</button>' : $row1;
	if ($row1==0 and !is_null($row1)) $var = '&nbsp';
	else {
		$var = '<button name="show_global" type="submit" value="';
		$var .= ($row1===null) ? $row0.'">Glob' : $row1.'">'.$row1;
		$var .= '</button>';
	}
	return $var;
}
# Получить массивы юзеров,групп и клиентов
function getarrs() {
	global $link;
	$query="SELECT uid,login,role,in_gr FROM users;";
	$result=mysqli_query($link, $query);
	if (!$result) echo 'error result 1';
	$i=1;
	while ($row=mysqli_fetch_array($result)) {
		$_SESSION["users"][$i]["uid"]=$row["uid"];
		$_SESSION["users"][$i]["login"]=$row["login"];
		$_SESSION["users"][$i]["role"]=$row["role"];
		$_SESSION["users"][$i]["in_gr"]=$row["in_gr"];
		$i++;
	}
	$query="SELECT gid,name,adm_uid,deputy_uid FROM groups;";
	$result=mysqli_query($link, $query);
	if (!$result) echo 'error result 2';
	$i=1;
	while ($row=mysqli_fetch_array($result)) {
		$_SESSION["groups"][$i]["gid"]=$row["gid"];
		$_SESSION["groups"][$i]["name"]=$row["name"];
		$_SESSION["groups"][$i]["adm_uid"]=$row["adm_uid"];
		$_SESSION["groups"][$i]["deputy_uid"]=$row["deputy_uid"];
		$i++;
	}
	$query="SELECT cid,name FROM customer;";
	$result=mysqli_query($link, $query);
	if (!$result) echo 'error result 3';
	$i=1;
	while ($row=mysqli_fetch_array($result)) {
		$_SESSION["customer"][$i]["cid"]=$row["cid"];
		$_SESSION["customer"][$i]["name"]=$row["name"];
		$i++;
	}
}
# FILTER
function filter() {
	echo '<form method="POST" action="'.$_SERVER["PHP_SELF"].'"><table id="filter" class="filter" style="border-bottom-left-radius: 30px"><tr>';
	echo '<td><input type="submit" name="filter_weight" style="width: 105px" value="';
	echo ($_SESSION["filter"]["weight"]==1) ? 'Order:Off"></td>' : 'Order:On"></td>';
	echo '<td><select name="filter_assigned" onchange="filter1(this.value)">';
	echo '<option value="0">ALL</option>';
		for($d=1;$d<=count($_SESSION["users"]);$d++) {
			echo '<option value="'.$_SESSION["users"][$d]["uid"].'"';
			if ($_SESSION["filter"]["assigned"]==$_SESSION["users"][$d]["uid"]) echo ' selected';
			echo '>'.$_SESSION["users"][$d]["login"].'</option>';
		}
	echo '</select></td>';
	echo '<td><select name="filter_day" onchange="filter2(this.value)">';
		echo '<option value="0" ';
		echo ($_SESSION["filter"]["day"]==0) ? 'selected>Default</option>' : '>Default</option>';
		echo '<option value="1" ';
		echo ($_SESSION["filter"]["day"]==1) ? 'selected>Till today</option>' : '>Till today</option>';
		echo '<option value="2" ';
		echo ($_SESSION["filter"]["day"]==2) ? 'selected>This Week</option>' : '>This Week</option>';
	echo '</select></td>';
	echo '</tr></table></form>';
}
# Показать задачи ; $sql - условия фильтра 
function show_tasks($where) {
	global $link,$importance_names,$status_names,$col_width;
	$query = "SELECT id,parent_id,creator_uid,name,importance,status,end_trigger,date_create,date_start,date_end,time_spent,gid,assigned_uid,cid,whatfor,coment FROM tasks ";
	if ($_SESSION["filter"]["weight"]==1) $query .= "LEFT JOIN users ON tasks.assigned_uid=users.uid ";
	if (isset($where)) $query .= "WHERE ".$where." ";
	if ($_SESSION["filter"]["assigned"]>0) $query .= "AND assigned_uid='".$_SESSION["filter"]["assigned"]."' ";
	switch ($_SESSION["filter"]["day"]) {
	case 1:
		$query .= "AND date_start<".time()." ";
	break;
	case 2:
		$day=time();
		$time = (date("w",$day)==0) ? $day : (7-date("w",$day))*60*60*24+$day;
		$query .= "AND date_start<".$time." ";
	break;
	}
	if ($_SESSION["filter"]["weight"]==1) $query .= "ORDER BY weight";
	$query .= ";";
	if (!$result=mysqli_query($link, $query)) echo '<code>Error SQL: '.$query.'</code>';
#	else echo '<code>SQL OK: '.$query.'</code>';
	if (!mysqli_num_rows($result)>0) {
		echo 'Нет результатов';
		return false;
	}
	echo '<table>';
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
	while ($row = mysqli_fetch_row($result)) {
		if (isset($_SESSION["edit_row"]) and $_SESSION["edit_row"]==$row[0] and check_role($row[11],$row[12])) {	# Рисуем редактируемую строку
			for($d=0;$d<count($row);$d++) { # массив редактируемых колонок если не выставлены
				if (!isset($_SESSION["col"][$d])) $_SESSION["col"][$d]=$row[$d];
			}
			edit_insert();
		}
		# рисуем не редактируемую строку
		else {
			echo '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
			echo (check_role($row[11],$row[12])) ? '<tr onclick="open_task('.$row[0].')" class="row can_edit">' : '<tr onclick="open_task(0)" class="row">'; # при post edit=0 происходит отмена редактирования
			# Редактирование строки
			echo (check_role($row[11],$row[12])) ? '<td><input name="edit" type="submit" value="'.$row[0].'"></td>' : '<td>'.$row[0].'</td>';
			echo '<td>'.show_global_task($row[0],$row[1]).'</td>';
			echo '<td>'.$_SESSION["users"][$row[2]]["login"].'</td>';
			echo '<td width="100%" style="text-align:left;">'.$row[3].'</td>';
			# IMPORTANCE
			switch ($row[4]) {
				case 0:	echo '<td class="bg_red">';	break;
				case 2:	echo '<td class="bg_blue">'; break;
				default: echo '<td>'; break;
			}
			echo $importance_names[$row[4]].'</td>';
			# STATUS
			switch ($row[5]) {
				case 0:	echo '<td class="bg_red">'; break;
				case 2:	echo '<td class="bg_blue">'; break;
				case 3:	echo '<td class="bg_green">'; break;
				case 4:	echo '<td class="bg_white">'; break;
				default: echo '<td>'; break;
			}
			echo $status_names[$row[5]].'</td>';
			# END TRIGGER
			echo '<td style="text-align:left;">'.$row[6].'</td>';
			# DATE CREATE
			echo '<td>'.showdate($row[7]).'</td>';
			# DATE START
			echo ($row[8]<time() and $row[5]!=3) ? '<td class="bg_red" ' : '<td ';
			echo 'align="center">'.showdate($row[8]).'</td>';
			# DATE END
			echo '<td>'.showdate($row[9]).'</td>';
			# TIME
			echo '<td style="text-align:right;padding-right:7px;">'.showtime($row[10]).'</td>';
			echo (isset($_SESSION["groups"][$row[11]]["name"])) ? '<td>'.$_SESSION["groups"][$row[11]]["name"].'</td>' : '<td>&nbsp</td>';
			echo (isset($_SESSION["users"][$row[12]]["login"])) ? '<td>'.$_SESSION["users"][$row[12]]["login"].'</td>' : '<td>&nbsp</td>';
			echo (isset($_SESSION["customer"][$row[13]]["name"])) ? '<td>'.$_SESSION["customer"][$row[13]]["name"].'</td>' : '<td>&nbsp</td>';
			echo '<td style="text-align:left;">'.$row[14].'</td>';
			echo '<td style="text-align:left;">'.$row[15].'</td></tr></form>';
		}
	}
	echo '</table>';
}

function edit_insert() {
global $importance_names,$status_names;
	# Кнопка save
	echo '<form method="POST" action="'.$_SERVER["PHP_SELF"].'"><tr class="row_edit"><td><button name="save" type="submit" value="S" autofocus>Save</button></td>';
	# 2 колонка
	if (isset($_SESSION["global"])) echo '<td>'.$_SESSION["global"].'</td>';
	else echo ($_SESSION["col"][1]===null) ? '<td>Glob</td>' : '<td><input type="checkbox" name="ifglobal"></td>';
	# Creator
	echo '<td>'.$_SESSION["users"][$_SESSION["col"][2]]["login"].'</td>';
	# Name
	echo '<td width="100%"><textarea name="edit_name" placeholder="Название задачи" required>'.$_SESSION["col"][3].'</textarea></td>';
	# Важность
	echo '<td><select name="edit_importance">';
	for($d=0;$d<count($importance_names);$d++) {
		if ($_SESSION["col"][4]==$d) echo '<option value="'.$d.'" selected>'.$importance_names[$d].'</option>';
		else echo '<option value="'.$d.'">'.$importance_names[$d].'</option>';
	}
	echo '</select></td>';
	# Status
	echo '<td><select name="edit_status">';
	for($d=0;$d<count($status_names);$d++) {
		if ($_SESSION["col"][5]==$d) echo '<option value="'.$d.'" selected>'.$status_names[$d].'</option>';
		else echo '<option value="'.$d.'">'.$status_names[$d].'</option>';
	}
	echo '</select></td>';
	# End trigger
	echo '<td><textarea name="edit_end_trigger" placeholder="Триггер завершения">'.$_SESSION["col"][6].'</textarea></td>';
	# ДАТА СОЗДАНИЯ
	echo '<td>'.showdate($_SESSION["col"][7]).'</td>';
	# ДАТА НАЧАЛА
	echo '<td><input name="edit_start" type="text" id="datepicker" placeholder="01/01/2014" required ';
	echo ($_SESSION["col"][8]>0) ? 'value="'.date("m",$_SESSION["col"][8]).'/'.date("d",$_SESSION["col"][8]).'/'.date("Y",$_SESSION["col"][8]).'"></td>' : '></td>';
	# ДАТА КОНЦА
	echo '<td>'.showdate($_SESSION["col"][9]).'</td>';
	# ЗАТРАЧЕНОЕ ВРЕМЯ
	echo '<td><input type="text" name="edit_time" id="timepicker" placeholder="'.showtime($_SESSION["col"][10]).'" value="'.showtime($_SESSION["col"][10]).'"></td>';
	# ГРУППА
	echo '<td><select name="edit_gid">';
	echo '<option value="0">ALL</option>';
	for($d=1;$d<=count($_SESSION["groups"]);$d++) {
		if ($_SESSION["col"][11]==$_SESSION["groups"][$d]["gid"]) echo '<option value="'.$_SESSION["groups"][$d]["gid"].'" selected>'.$_SESSION["groups"][$d]["name"].'</option>';
		else echo '<option value="'.$_SESSION["groups"][$d]["gid"].'">'.$_SESSION["groups"][$d]["name"].'</option>';
	}
	echo '</select></td>';
	# исполняющий
	echo '<td><select name="edit_assigned">';
#	echo '<option value="0">&nbsp</option>';
	for($d=1;$d<=count($_SESSION["users"]);$d++) {
		echo '<option value="'.$_SESSION["users"][$d]["uid"].'"';
		if ($_SESSION["col"][12]==$_SESSION["users"][$d]["uid"]) echo ' selected';
		echo '>'.$_SESSION["users"][$d]["login"].'</option>';
	}
	echo '</select></td>';
	# Client
	echo '<td><select name="edit_cid">';
	echo '<option value="0">&nbsp</option>';
	for($d=1;$d<=count($_SESSION["customer"]);$d++) {
		if ($_SESSION["col"][13]==$_SESSION["customer"][$d]["cid"]) echo '<option value="'.$_SESSION["customer"][$d]["cid"].'" selected>'.$_SESSION["customer"][$d]["name"].'</option>';
		else echo '<option value="'.$_SESSION["customer"][$d]["cid"].'">'.$_SESSION["customer"][$d]["name"].'</option>';
	}
	echo '</select></td>';
	# Для чего
	echo '<td><textarea name="edit_whatfor" placeholder="Для чего">'.$_SESSION["col"][14].'</textarea></td>';
	# Коментарий
	echo '<td><textarea name="edit_comment" placeholder="Комент">'.$_SESSION["col"][15].'</textarea></td>
	</tr></form>';
}
?>