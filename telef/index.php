<!doctype html PUBLIC>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">

<?php

include_once("header.php");

# на всякий случай проверяем роль у юзера
if (!isset($_SESSION["role"])) header('Location: /login.php');

# если нажали логаут
if (isset($_POST["logout"])) {
unset($_SESSION["login"]);
unset($_SESSION["role"]);
$_SESSION=array();
session_destroy();
header('Location: /login.php');
}
# рисуем форму приветсвия и логаут
echo '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">
<table id="menu"><tr>
<td>'.$title.'&nbsp</td>
<td class="text" style="width: 706px;">Вы зашли как: '.$_SESSION["login"].'</td>
<td><input name="logout" type="submit" value="Выйти"></td>
</tr></table>';

# БЛОКИ
echo '<table border="0" cellspacing="20" cellpadding="0"><tr><td>';
# НАСТРОЙКА

if ($_SESSION["role"]<=3) {
	include_once("/calendar.php");

	echo '<div class="blockname">Настройки</div>';
	echo '<table border="0" cellspacing="0" cellpadding="0" class="blocks">';
	echo '<tr id="content"><td colspan="3" align="center">Выберите начальную и конечную дату</td></tr>';

	if (isset($_SESSION["start_selected_d"])) echo '<tr id="content"><td align="center">Начало '.$_SESSION["start_selected_d"].'.'.$_SESSION["start_selected_m"].'.'.$_SESSION["start_selected_y"].'</td>';
	else echo '<tr id="content"><td align="center">Начало <font color="red">Не выбрано</font></td>';

	if (isset($_SESSION["start_date"]) and isset($_SESSION["end_date"])) {
		if ($_SESSION["start_date"]<$_SESSION["end_date"]+1) {
			$start_end='<input name="show_stat" type="submit" value="Показать интервал c '.$_SESSION["start_selected_d"].'.'.$_SESSION["start_selected_m"].'.'.$_SESSION["start_selected_y"].' по '.$_SESSION["end_selected_d"].'.'.$_SESSION["end_selected_m"].'.'.$_SESSION["end_selected_y"].'">';
		}
		else {
			$start_end='<font color="red">Конечная дата должна быть больше начальной</font>';
		}
	}
	else  $start_end='<font color="red">Интервал не установлен. Выберите дату</font>';

	echo '<td>&nbsp</td>';
	if (isset($_SESSION["end_selected_d"])) echo '<td align="center">Окончание '.$_SESSION["end_selected_d"].'.'.$_SESSION["end_selected_m"].'.'.$_SESSION["end_selected_y"].'</td></tr>';
	else echo '<td align="center">Окончание <font color="red">Не выбрано</font></td></tr>';
	echo '<tr><td>';
	build_cal('start');
	echo '</td><td id="content">&nbsp</td><td>';
	build_cal('end');
	echo '</td></tr></table></td>';
}

# При нажатии на кнопку, заполняем массив данными из запрооса(MYSQL)
if (isset($_POST["show_stat"])) {
	$_SESSION["end_date"] += 3600*24-1;
	$var_names = array("Втц","Втц(Кукм)","Мтс","Мтс(Кукм)","всего","всего(Кукм)");
	#WTC
	$query  = "SELECT count(*), sum(CEILING(e_duration/60)), sum(e_call_cost) from external_cdr where id_prov=2 and e_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	$query .= "SELECT count(*), sum(CEILING(i_duration/60)), sum(getPrice(i_dst,internal_cdr.id_prov)*CEILING(i_duration/60)) from internal_cdr left join providers on internal_cdr.id_prov=providers.id_prov where internal_cdr.id_prov=2 and i_duration>=free_secs and i_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	#MTS
	$query .= "SELECT count(*), sum(CEILING(e_duration/60)), sum(e_call_cost) from external_cdr where id_prov=1 and e_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	$query .= "SELECT count(*), sum(CEILING(i_duration/60)), sum(getPrice(i_dst,internal_cdr.id_prov)*CEILING(i_duration/60)) from internal_cdr left join providers on internal_cdr.id_prov=providers.id_prov where internal_cdr.id_prov=1 and i_duration>=free_secs and i_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	#All
	$query .= "SELECT count(*), sum(CEILING(e_duration/60)), sum(e_call_cost) from external_cdr where e_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	$query .= "SELECT count(*), sum(CEILING(i_duration/60)), sum(getPrice(i_dst,internal_cdr.id_prov)*CEILING(i_duration/60)) from internal_cdr left join providers on internal_cdr.id_prov=providers.id_prov where i_duration>=free_secs and i_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	/* запускаем мультизапрос */
	if (mysqli_multi_query($link, $query)) {
		unset($counter);
		do {
			/* получаем первый результирующий набор */
			if ($result = mysqli_store_result($link)) {
				$row = mysqli_fetch_row($result);
				$counter++;
				$_SESSION["stat"][$counter]='<tr><td class="var_name">'.$var_names[$counter-1].'</td><td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td></tr>';
				mysqli_free_result($result);
			}
			/* печатаем разделитель */
			if (mysqli_more_results($link)) {
				printf("");
			}
		} while (mysqli_next_result($link));
	}
	# переделать под функцию, запускапть по кнопке, закидывать переменные в ссесию !!!!!!!!!!!!
	$hd_numbers = array("ВТЦ 2099","Технопарк 2499","Полет 2299","Морозов Д. 2291","всего");
	$query  = "SELECT count(*) FROM internal_cdr where i_calling=2099 and i_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	$query .= "SELECT count(*) FROM internal_cdr where i_calling=2499 and i_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	$query .= "SELECT count(*) FROM internal_cdr where i_calling=2299 and i_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	$query .= "SELECT count(*) FROM internal_cdr where i_calling=2291 and i_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";
	$query .= "SELECT count(*) FROM internal_cdr where i_calling in (2099, 2499, 2299,2291, 3333) and i_date between from_unixtime(".$_SESSION["start_date"].") and from_unixtime(".$_SESSION["end_date"].");";

	/* запускаем мультизапрос */
	if (mysqli_multi_query($link, $query) and $_SESSION["role"]==1) {
		unset($counte);
		do {
			/* получаем первый результирующий набор */
			if ($result = mysqli_store_result($link)) {
				$row = mysqli_fetch_row($result);
				$counte++;
				$_SESSION["stathd"][$counte]='<tr><td class="var_name">'.$hd_numbers[$counte-1].'</td><td>'.$row[0].'</td></tr>';
				mysqli_free_result($result);
			}
			/* печатаем разделитель */
			if (mysqli_more_results($link)) {
				printf("");
			}
		} while (mysqli_next_result($link));
	}
}


# Рисуем таблицу статистики
echo '<td><div class="blockname">Статистика&nbspзвонков</div>';
echo '<table id="cont_table" class="blocks">
<tr><td colspan="4" class="var_name">'.$start_end.'</td></tr>';
echo '<tr><td class="var_name">&nbsp</td><td>Количество</td><td>время в м.</td><td>стоимость</td></tr>';

# ИСПРАВИТЬ - ПИШЕТ В ЛОГ
$count = count($_SESSION["stat"]);
for ($i = 0; $i < $count+1; $i++) {
	echo $_SESSION["stat"][$i];
}
echo '</table></td>';
if ($_SESSION["role"]==1) {
	echo '<td><div class="blockname">Help&nbspDesk</div>';
	echo '<table id="cont_table" class="blocks">
	<tr><td colspan="2" class="var_name">Количество звонков на номера:</td></tr>';
	echo '<tr><td class="var_name">&nbsp</td><td>Количество</td></tr>';
	# ИСПРАВИТЬ - ПИШЕТ В ЛОГ
	$count = count($_SESSION["stathd"]);
	for ($i = 0; $i < $count+1; $i++) {
		echo $_SESSION["stathd"][$i];
	}
	echo '</table></td>';
}
echo '</tr>';
if ($_SESSION["role"]==1) {
	# ФОРМА ЗАПРОСА --------
	echo '<tr><td>';
	echo '<div class="blockname">Детальная статистика</div>';
	echo '<table id="cont_table" class="blocks">
	<tr><td class="var_name">';
	
echo '<input name="select_name" type="hidden" value="off">';
echo '<input name="select_name" type="checkbox" value="on">';

if (isset($_POST["select_name"]) and $_POST["select_name"]=="on") $_SESSION["showshow"]=1;
echo $_SESSION["showshow"].' SESSION_showshow<br>';
echo $_POST["select_name"].' select_name';
if (isset($_SESSION["showshow"])) {
echo '
<select name="testv">
<option value="Designer">звонившего</option>
<option value="Programmer">набранный</option>
<option value="Writer">ответившего</option>
<option value="all">все</option>
</select>
';
}
	
	echo '</td></tr>';
	echo '</table></td>';
	# РЕЗУЛЬТАТ
	echo '<td colspan="2"><div class="blockname">Результат</div>';
	echo '<table id="cont_table" class="blocks" width="100%">
	<tr><td>gkfotfo saiu idfuisadfsaodifos dfoisd sdu os dfa sda </td></tr>';

	echo '</table></td></tr>';
}
echo '</table></form></body></html>';
mysqli_close($link);
?>