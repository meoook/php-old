<?php
#=============================================================
# MODULES
#=============================================================
# MODULES: CONTENT LIST (NEWS) - Походу это АПИ будет
function content_list() {
	$query = "SELECT t1.c_id,t1.cr_date,t1.user_id,t2.name,t2.img,count(t3.user_id) AS all_count, count(t3.dislike) as dis_count
	FROM content AS t1 LEFT JOIN users AS t2 ON t1.user_id=t2.user_id 
					LEFT JOIN content_likes AS t3 ON t1.c_id=t3.c_id
	WHERE t1.m_name='news' GROUP BY t1.c_id ORDER BY t1.cr_date DESC LIMIT 10";
	$arr = sql_select($query);
	$html = '';
	foreach ($arr as $row) {
		$img = (empty($row['img'])) ? 'blank.png' : $row['img'].'.png';
		$html .= '<div id="'.$row['c_id'].'" class="content"><div class="infoline">
			<span>ID: '.$row['c_id'].'</span>
			<span>Date create: '.date('F Y H:i',$row['cr_date']).'</span>
			<span><a href="/acc/'.$row['user_id'].'">'.$row['name'].'<img src="'.$img.'" class="avas"></a></span>
		</div><div>';
		$query = "SELECT msg FROM content_vars WHERE c_id='".$row['c_id']."' ORDER BY var_id LIMIT 30";
		$c_vars = sql_select($query);
		foreach ($c_vars as $var) $html .= $var['msg']; // Это как то странно - может для поиска или многоконтентного контента
		$query = "SELECT count(*) FROM comments WHERE c_id='".$row['c_id']."'";
		# Переделать тут можно за 1 запрос
		$comments_count = sql_rselect($query)[0]; #кол-во коментов
		$commentc = $likec = $dlikec = 'ico'; # Нет подсветки 
		if (isset($_SESSION['acc_id'])) { # Подсвечиваем юзеру его лайкосики или коменты
			$query = "SELECT * FROM content_likes WHERE c_id='".$row['c_id']."' AND user_id = '".$_SESSION['acc_id']."'";
			if ($like=sql_rselect($query)) {
				if ($like[3]==1) $dlikec .= ' on'; #DIS LIKE подсветка
				else $likec .= ' on'; #LIKE подсветка
			}
			$query = "SELECT * FROM comments WHERE c_id='".$row['c_id']."' AND user_id = '".$_SESSION['acc_id']."'";
			if (sql_select($query)) $commentc .= ' on';
		}
		$html .= '</div><div class="likeline"><span name="countc"><span>'.$comments_count.'</span>';
		$html .= '<img src="/i_cmsg.png" class="'.$commentc.'" onclick="comment('.$row['c_id'].')"></span>';
		$html .= '<span><img src="/i_like.png" class="'.$likec.'" onclick="like('.$row['c_id'].')"><span>'.($row['all_count'] - $row['dis_count']).'</span>';
		$html .= '<img src="/i_liker.png" class="'.$dlikec.'" onclick="dlike('.$row['c_id'].')"><span>'.$row['dis_count'].'</span>';
		$html .= '</span></div><div class="blockC">';
		if (isset($_SESSION['acc_id'])) { # Оставить коментарий залогиненому юзеру
			$html .= '<div class="inpc"><img src="'.$_SESSION["acc_img"].'"><textarea onkeyup="chkComment(this)"></textarea>';
			$html .= '<input type="button" value="комент" onclick="putC('.$row['c_id'].')"></div>';
		}
		$html .= '<div class="comments"></div></div></div>'; # div ; div content
	}
	return $html;
}
# MODULE: ACCOUNT
function acc_fn() { # Функция выбора функции по параметру
	global $m_params;
	if ($m_params['prm1']) {
		if (!is_numeric($m_params['prm1'])) {
			switch ($m_params['prm1']) {
				case 'exit':
					return acc_exit();
				break;
				default:
					return 'ERROR: NO ACCOUNT ACTION AS '.$m_params['prm1']; # $GLOBALS['m_params']['err'] =
				break;
			}
		}
		else return acc_id($m_params['prm1']);
	}
	elseif (isset($_SESSION["acc_id"])) return acc_id($_SESSION["acc_id"]);
	else return 'USER NOT SET';
}
# MODULE: ADMIN PAGE
function adm_page() {
	$dir = session_save_path(); # Путь до файлов сесси
	$files = array_diff(scandir($dir), array('..', '.')); # Названия файлов сессий
	$files = array_map(function ($v){return substr($v,5);},$files); # Массив sessions из названий файлов
	#---------------------------------------------------------
	echo '<div>UTC DIFFERENSE: '.date('Z').'</div>';
	echo '<div>SESSION EXPIRE: '.(session_cache_expire()*60).'</div>';
	echo '<div>SESSION LIFE TIME: '.ini_get("session.cookie_lifetime").'</div>';
	echo '<div>CURRENT TIME (unix): '.time().'</div>';
	$query = "SELECT name, ip_adr, conn_date, session_id 
	FROM users_connect left join users on users_connect.user_id=users.user_id 
	WHERE dconn_date is NULL ORDER BY conn_date LIMIT 30";
	$arr = sql_select($query);
	$html = '<div class="flex"><table class="content"><tr><td>Name</td><td>Connected</td><td>Disconnected</td><td>IP ADDRESS</td><td>SESSION ID</td></tr>' ;
	foreach ($arr as $row) {
		$html .= '<tr>';
		$dcount = $row['conn_date'] + ini_get("session.cookie_lifetime") - time(); # Время входа + жизнь куки - текущее время #  date('Z') - разница времени
		$dconn = '<td>'.(($dcount - $dcount%3600)/3600).date(':i:s',$dcount).'</td>';
		$html .= '<td>'.$row['name'].'</td><td>'.date('H:i d.m.y',$row['conn_date']).'</td>'.$dconn.'<td>'.long2ip($row['ip_adr']).'</td>';
		$html .= ($n = array_search($row['session_id'],$files)) ? '<td class="input_ok"><b>'.($n-1).'</b> '.$row['session_id'].'</td>' : '<td>'.$row['session_id'].'</td>';
		$oss[] = $row['session_id']; # Массив no logout sessions
		$html .= '</tr>';
	}
	$html .= '</table>';
	echo $html;
	#---------------------------------------------------------
	echo '<ul class="content"><b>'.count($files).'</b> in <b>'.$dir.'</b>';
	foreach ($files as $fname) {
		$clss = (in_array($fname,$oss)) ? ' class="input_ok"' : '';
		echo '<div'.$clss.'><b>'.(++$i).'</b> '.$fname.'<div></div>'.date("d.m.y H:i:s", filectime($dir.'/sess_'.$fname)).' <b>'.filesize($dir.'/sess_'.$fname).'</b> байт</div>';
	}
	echo '</ul></div>';
}
#=============================================================
# BLOCKS
#=============================================================
# BLOCKS: MENU BUILDER - Стиль и другие параметры могут браться из БД
function build_menu($id,$style) {
	$triger = 0;
	$query = "SELECT pos,subm_pos,name,href,src_ico FROM menu_items WHERE menu_id=".$id." ORDER BY pos,subm_pos;";
	if ($arr = sql_select($query)) {
		$html = (isset($style)) ? '<ul class="'.$style.'">' : '<ul>';
		foreach ($arr as $row) {
			if ($row['subm_pos']>0 && $triger===0) { # Открываем сабменю
				$html .= '<ul>';
				$triger = 1;
			}
			elseif ($row['subm_pos']==0 && $triger===1) { # Закрываем сабменю
				$html .= '</ul></li>';
				$triger = 0;
			}
			else $html .= '</li>';
			$ico = (isset($row['src_ico'])) ? '<img src="/'.$row['src_ico'].'.png" class="ico">' : '';
			$html .= '<li><a href="/'.$row['href'].'">'.$ico.strtoupper($row['name']).'</a>';
		}
		if (count($arr>0)) $html .= '</li>'; # Закрываем последний li
		$html .= '</ul>';
	}
	else $html = '<ul>Нет такой менюхи в базе</ul>'.$id;
	if ($triger===1) $html .='</ul>';
	return $html;
}
# BLOCKS: ARRAY to TABLE
function arr_to_table($arr,$style) {
	$html = (isset($style)) ? '<table class="'.$style.'">' : '<table>' ;
	foreach ($arr as $row) {
		$html .= '<tr>';
		# Проверка, не печатаем ли мы таблицу айпи - тогда перекодим
		if ($style==='ip_tbl') {
			$dconn = ($row['dconn_date']) ? '<td>'.date('H:i d.m.y',$row['dconn_date']).'</td>' : '<td class="input_ok">На сайте</td>';
			$html .= '<td>'.date('H:i d.m.y',$row['conn_date']).'</td>'.$dconn.'<td>'.long2ip($row['ip_adr']).'</td>';
			if (chk_role(2)) $html .= '<td>'.$row['session_id'].'</td>';
		}
		else foreach ($row as $elem) $html .= '<td>'.$elem.'</td>';
		$html .= '</tr>';
	}
	$html .= '</table>';
	return $html;
}

# BLOCKS: ACC PAGE
function  acc_id($id) {
	if (!isset($id)) return false;
	# Может изменять
	$change = ($_SESSION['acc_id']==$id) ? true : false;
	$html = '<div class="flex">';
	$html .= acc_form_up($id);
	$html .= '<div class="content nowrap">'; #nowrap
	$what = ($change) ? "conn_date,dconn_date,ip_adr" : "MAX(conn_date) as conn_date,ip_adr";
	$query = "SELECT ".$what." FROM users_connect WHERE user_id=".$id." ORDER BY conn_date DESC LIMIT 10";
	$rr = sql_select($query);
	if ($change) $html .=  arr_to_table($rr,'ip_tbl');
	else $html .=  'Last login:&nbsp;'.date('d F Y',$rr[0]['conn_date']);
	$html .=  '</div></div>';
	return $html;
}
# FORMS
#=============================================================
# BLOCKS: ACC MENU
function acc_menu() {
	if (isset($_SESSION["acc_name"])) $html = '<a onclick="menuLogin()">'.strtoupper($_SESSION["acc_name"]).'<img src="'.$_SESSION["acc_img"].'"></a>'.build_menu(2,'loginMenu');
	else $html = '<li><a onclick="menuLogin()">ВХОД</a></li><li><a href="/reg">REG</a></li>
	<ul class="loginMenu">
		<div><input name="login_m" type="email" value="" onkeyup="checkMail(this)" placeholder="Почтовый Адрес" autocomplete="on" autofocus></div>
		<div><input name="login_p" type="password" value="" placeholder="Пароль" autocomplete="off"></div>
		<div><input name="login" type="submit" value="ВОЙТИ" onclick="entr(this)"></div>
		<div class="small"><a href="/acc/recover">Восстановление пароля</a></div>
	</ul>';
	return $html;
}
# BLOCKS: LOGIN FORM - DELETE ?
function acc_form_login() {
	global $m_params;
	$html = '<div id="acc" class="frm content">
	<div><input name="login_m" type="email" value="'.$GLOBALS['mail'].'" placeholder="Почтовый Адрес" autocomplete="on" autofocus></div>
	<div><input name="login_p" type="password" value="" placeholder="Пароль" autocomplete="off"></div>
	<div><input name="login" type="submit" value="ВХОД"></div>
	</div>';
	return $html;
}
# BLOCKS: REG FORM No login No mail_off
function acc_form_reg() {
	global $m_params;
	$html = 'ERR: Вы уже вошли';
	if (!isset($_SESSION["acc_login"])) { 
		$html = '<div id="acc" class="frm content">
		<h2>REGISTRATION</h2>
		<div><input name="reg_n" type="text" placeholder="Имя на сайте" onkeyup="chkn(this)" autocomplete="on" autofocus></div>
		<div><input name="reg_m" type="text" placeholder="Почтовый Адрес" onkeyup="chkm(this)" autocomplete="on"></div>
		<div><input name="reg_p" type="password" placeholder="Пароль" onkeyup="chkp(this)" autocomplete="off"></div>
		<div><input name="reg" type="submit" value="РЕГИСТРАЦИЯ" disabled></div>
		</div>';
	}
	return $html;
}
# BLOCKS: FORM UPDATE ACCOUNT - NEED REMAKE
function acc_form_up($id) {
	$arr = acc_params($id);
	if ($_SESSION['acc_id']==$id) $change = true;
	else $change = (chk_role(1)) ? true : false ; # Если админ то видит мыло и меняет пароль
	$html='<div id="acc" class="frm content">
			<div class="center"><img src="'.$arr['img'].'" height="100" width="100"></div>
			<div class="btn"><span>Name:</span><span>'.$arr['name'].'</span></div>
			<div class="btn"><span>Role:</span><span>'.$arr['role'].'</span></div>';
	if ($change) $html .= '<div class="btn"><span>Mail:</span><span>'.$arr['mail'].'</span></div>
			<div id="pchange"><input value="Изменить пароль" type="submit" onclick="openPass(1)"></div>';
	$html .= '<div class="center">Created in '.date('F Y',$arr['cr_date']).'</div>
		</div>
		<div class="content mid">'.$arr['dsc'].'</div>';
	return $html;
}
?>
