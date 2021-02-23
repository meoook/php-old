<?php
# CHECK: INPUT
function chk_input($var) {
	$var=strip_tags($var); # Удаляет теги HTML и PHP из строки
	$var=htmlspecialchars($var,ENT_QUOTES); # Преобразует специальные символы в HTML-сущности
	$var=implode("",explode("\\",$var)); # Удаляем двойные обратные слеши
	$var=stripslashes($var); # Удаляем обратные слеши
	return $var;
}
# CHECK: ROLE
function chk_role($role_id) {
	if (isset($_SESSION['acc_role']) && $_SESSION['acc_role']<=$role_id) return true;
	else return false;
}
# SQL: ROW=1 SELECT
function sql_rselect($query) {
	if (substr($query,0,6)==='SELECT') {
		$result =  mysqli_query($GLOBALS['sql_conn'],$query);
		# Должна быть одна запись с такой комбинацией
		if (mysqli_num_rows($result)==1){
			$row = mysqli_fetch_row($result);
			mysqli_free_result($result);
			return $row;
		}
	}
	return false;
}
# SQL: SELECT
function sql_select($query) {
	if (substr($query,0,6)==='SELECT' && $result=mysqli_query($GLOBALS['sql_conn'],$query)) {
		while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) $rows[] = $row;
		mysqli_free_result($result);
		return $rows;
	}
	else return false;
}
# SQL: INSERT/UPDATE
function sql_insert($query) {
	$chk = substr($query,0,6);
	if ($chk==='INSERT'||$chk==='UPDATE'||$chk==='DELETE') {
		if (mysqli_query($GLOBALS['sql_conn'],$query)) return true;
		mysqli_free_result($result);
	}
	return false;
}
# URL: Получаем параметры для модуля
function url_params() {
	$arr = explode('/', chk_input($_SERVER['REQUEST_URI']));
	# Если нет совпадений то настройки по умолчанию - main page
	$m['name'] = (preg_match('/^[a-z]+$/i', $arr[1])) ? $arr[1] : false;
	$m['prm1'] = (preg_match('/^[a-z0-9]+$/i', $arr[2])) ? $arr[2] : false;
	$m['prm2'] = (preg_match('/^[0-9]+$/i', $arr[3])) ? $arr[3] : false;
	return $m;
}
# SQL: Получаем параметры для модуля
function module_params($name) {
	# Список обработчиков контента - возможно просто в массив запихнуть в конфиге
	$query = "SELECT m_name FROM modules";
	if ($arr = sql_select($query)) foreach ($arr as $module) $m['modules'][] = $module["m_name"];
	if (!in_array($name,$m['modules'])) {
			$GLOBALS['m_params']['err'] = 'ERROR: NO MODULE FOUND '.$name;
			$name = 'news'; # По умолчанию эту страницу
	}
	# Получаем параметры страницы из SQL
	$query = "SELECT function,menu_id,role_id,banner,dsc FROM modules WHERE m_name='".$name."'";
	# Должна быть одна такая страница
	if ($row=sql_rselect($query)) $m += Array('m_name'=>$name,'func'=>$row[0],'menu'=>$row[1],'role'=>$row[2],'banner'=>$row[3],'dsc'=>$row[4]);
	# ERROR Сообщение модуль не найден
	else $GLOBALS['m_params']['err'] = "CRITICAL: SQL PARAMS - ".$name;
	return $m;
}
# ACC: PARAMS
function acc_params($id) {
	$query = "SELECT user_id,name,role_id,cr_date,dsc,mail,img FROM users WHERE user_id=".$id;
	if ($row=sql_rselect($query)) {
		$acc = Array('id'=>$row[0],'name'=>$row[1],'role'=>$row[2],'cr_date'=>$row[3],'dsc'=>$row[4],'mail'=>$row[5]);
		$acc['img'] = (empty($row[6])) ? 'blank.png' : $row[6].'.png';
		return $acc;
	}
	else return false;
}
# ACC: LOGIN
function acc_login($mail,$pass) {
	$query = "SELECT user_id,name,role_id,img FROM users WHERE mail='".$mail."' and password='".$pass."'";
	if ($row=sql_rselect($query)) {
		$_SESSION["acc_id"] = $row[0];
		$_SESSION["acc_name"] = $row[1];
		$_SESSION["acc_role"] = $row[2];
		$_SESSION["acc_img"] = (empty($row[3])) ? 'blank.png' : $row[3].'.png';
		$query = "INSERT INTO users_connect(user_id,conn_date,ip_adr,session_id) VALUES('".$row[0]."','".time()."','".ip2long($_SERVER['REMOTE_ADDR'])."','".session_id()."')";
		sql_insert($query);
		return true;
	}
	else return false;
}
# ACC: LOGOUT
function acc_exit() { # СДЕЛАТЬ ПОИСК ПО СЕССИИ
	$query = "UPDATE users_connect SET dconn_date=".time()." WHERE user_id=".$_SESSION["acc_id"]." AND dconn_date is NULL AND session_id='".session_id()."'ORDER BY conn_date DESC LIMIT 1;";
	sql_insert($query);
	unset($_SESSION["acc_id"]);
	unset($_SESSION["acc_name"]);
	unset($_SESSION["acc_role"]);
	session_unset();
	session_destroy();
	header('Location: /');
}
?>