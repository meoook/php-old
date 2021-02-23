<?php
# API
#============================================================
if (isset($_POST['API'])) {
	# Апи получает имя и значение параметра
	if (isset($_POST['name']) && isset($_POST['value'])) {
		# Проверяем на валидность имя параметра
		$name = chk_input($_POST['name']);
		if (preg_match('/^[a-z]+[\_?a-z]+$/i',$name)!==1) exit('ERROR:Critical - Post change');
		# Проверяем на валидность значение параметра
		$value = chk_input($_POST['value']);
		switch ($name) {
			case 'login':	# LOGIN
				if (isset($_SESSION['acc_id'])) exit('ERROR: уже залогинен');
				if (!filter_var($value, FILTER_VALIDATE_EMAIL)) exit('ERROR: JS-off - mail dont pass php validation');
				$pass = md5(chk_input($_POST["login_p"]));
				if (acc_login($value,$pass)) $resp_json['err'] = 0;
				else $resp_json = array('err'=>1, 'err_msg'=>'неверный логин\пароль');
			break;
			case 'reg_n':	# Проверка имени
				if (preg_match('/^\w+$/i',$value)!==1) exit('ERROR: JS-off - name dont pass php validation');
				$query="SELECT * FROM users WHERE name='".$value."'";
				if (!sql_select($query)) $resp_json['err'] = 0;
				else $resp_json = array('err'=>1, 'err_msg'=>'такое имя занято');
			break;
			case 'reg_m':	# Проверка почты
				if (!filter_var($value, FILTER_VALIDATE_EMAIL)) exit('ERROR:JS-off - mail dont pass php validation');
				$query="SELECT * FROM users WHERE mail='".$value."'";
				if (!sql_select($query)) $resp_json['err'] = 0;
				else $resp_json = array('err'=>1, 'err_msg'=>'такая почта занята');
			break;
			case 'acc_pnew': # Смена пароля
				$new_pass = md5($value);
				if (!isset($_SESSION['acc_id'])) exit('ERROR: Попытка смены пароля незалогиненым юзером');
				# Если админ то может менять пароль - Role = 0
				$acc_id = (isset($_SESSION['acc_role']) && $_SESSION['acc_role']==0 && $m_params['prm1']!==false) ? $m_params['prm1'] : $_SESSION['acc_id'];
				$query = "UPDATE users SET password='".$new_pass."' WHERE user_id=".$acc_id;
				if (sql_insert($query)) $resp_json['ok'] = 'ok';
				else $resp_json['ok'] = $query; # Заменить на фразу "ERROR: SQL query"
			break;
			case 'comment': # возвращаем коменты и кол-во и если залогинен то флаг владельца
				$rows_select_limit = 10; // Сколько за один запрос строчек - надо сделать конфиг - возможно для юзера
				if (!is_numeric($value)) exit('ERROR: Нет такого контента');
				$row_from = (isset($_POST['nrow'])) ? chk_input($_POST['nrow']) : 0; # По умолчанию показываем первые строки
				if (!is_numeric($row_from)) exit('ERROR: строка начала должно быть цифрой');
				$desc = (isset($_POST['desc'])) ? "" : "DESC "; # По умолчанию более новые сообщения
				$query = "SELECT SQL_CALC_FOUND_ROWS t2.name,t2.img,t2.user_id,t1.cr_date,t1.msg";
				if (isset($_SESSION['acc_id'])) $query .= ",t1.cmm_id,t2.user_id='".$_SESSION['acc_id']."' AS can_del"; # Для залогиненых - запрос на id и флаг удаления
				$query .= " FROM comments AS t1 JOIN users AS t2 ON t1.user_id=t2.user_id";
				$query .= " WHERE t1.c_id='".$value."' ORDER BY cr_date ".$desc."LIMIT ".$row_from.",".$rows_select_limit.";";
				$resp_json['rows_count'] = ($resp_json = sql_select($query)) ? sql_rselect('SELECT FOUND_ROWS();')[0] : 0 ; # переделать на count($resp_json)
				$resp_json['rows_from'] = ($resp_json['rows_count']>0) ? $row_from : 0;
				$resp_json['rows_limit'] = $rows_select_limit;
			break;
			case 'comment_refr': # Количество коментов и есть ли автор среди них
				if (!is_numeric($value)) exit('ERROR: Нет такого контента');
				$user_id = (isset($_SESSION['acc_id'])) ? $_SESSION['acc_id'] : 0 ;
				$query = "SELECT user_id='".$user_id."' AS found FROM comments WHERE c_id='".$value."';";
				if ($arr = sql_select($query)) foreach ($arr as $row) if ($row['found']==1) $resp_json['found'] = 1; # Если найден автор
				$resp_json['rows_count'] = count($arr);
			break;
			case 'add_comment': # Добавляем комент - возвращаем флаг успеха и c_id
				if (!is_numeric($value)) exit('ERROR: Нет такого контента');
				if (!isset($_SESSION['acc_id'])) exit('ERROR: Попытка коментария незалогиненым юзером');
				if (!isset($_POST['comment_txt'])) exit('ERROR: Нечего добавить - пустое сообщение');
				$comment = chk_input($_POST['comment_txt']);
				if (preg_match('/[\w\sа-яА-Я]/i', $comment)!==1) exit('ERROR: Недопустимые символы');
				$query = "INSERT INTO comments(c_id,cr_date,user_id,msg) VALUES('".$value."','".time()."','".$_SESSION['acc_id']."','".$comment."');";
				if (!sql_insert($query)) exit('ERROR: SQL INPUT comment');
				$resp_json = array('ok'=>'ok', 'cid'=>$value);
			break;
			case 'del_comment'; # Удаляем комент - возвращаем флаг успеха и c_id
				if (!isset($_SESSION['acc_id'])) exit('ERROR: Попытка удалить комент незалогиненым юзером');
				if (!is_numeric($value)) exit('ERROR: c_id определяются номером');
				if (!isset($_POST['comment_id'])) exit('ERROR: Что удалять ? comment_id miss');
				$comment_id = chk_input($_POST['comment_id']);
				if (!is_numeric($comment_id)) exit('ERROR: comment_id определяются номером');
				$query = "SELECT user_id FROM comments WHERE cmm_id='".$comment_id."';";
				if (sql_rselect($query)[0]!==$_SESSION['acc_id']) exit('ERROR: Попытка удаления комента не его автором');
				$query = "DELETE FROM comments WHERE cmm_id='".$comment_id."';";
				if(!sql_insert($query)) exit('ERROR: Critical SQL DELETE comment');
				$resp_json = array('ok'=>'ok', 'cid'=>$value);
			break;
			case 'like': # Ставим лайкосики или наоборот
				if (!isset($_SESSION['acc_id'])) exit('ERROR: Попытка лайкосика незалогиненым юзером');
				if (!is_numeric($value)) exit('ERROR: c_id определяются номером');
/*				Такая проверка нужна, чтоб лайк не поставить в несуществующий контент
				$query = "SELECT * FROM content WHERE c_id='".$value."';";
				if (!sql_rselect($query)) exit('ERROR: No content with id: '.$value);
*/				$dis = (isset($_POST['dis'])) ? true : false;
				$resp_json['ok'] = 'clear'; # отжимаем лайк \ дизлайк
				$where = " WHERE c_id='".$value."' and user_id = '".$_SESSION['acc_id']."';";
				$query = "SELECT dislike FROM content_likes".$where;
				if ($arr = sql_rselect($query)) { # Если лайкосик уже ставили
					if ($dis){ # отжимаем дизлайк \ меняем лайк на дизлайк
						if ($arr[0]==1) $query = "DELETE FROM content_likes".$where;
						else {
							$query = "UPDATE content_likes SET dislike='1'".$where;
							$resp_json['ok'] = 'dlike';
						}
					}
					else { # меняем дизлайк на лайк \ отжимаем дизлайк
						if ($arr[0]==1) {
							$query = "UPDATE content_likes SET dislike=NULL".$where; # Null чтоб count() не считал
							$resp_json['ok'] = 'like';
						}
						else $query = "DELETE FROM content_likes".$where;
					}
				}
				else { # ставим новый лайк или дизлайк
					$query = "INSERT INTO content_likes(c_id,user_id,cr_date,dislike) VALUES('".$value."','".$_SESSION['acc_id']."','".time()."',";
					if ($dis) {
						$query .= "'1');";
						$resp_json['ok'] = 'dlike';
					}
					else {
						$query .= "NULL);";
						$resp_json['ok'] = 'like';
					}
				}
				if (!sql_insert($query)) exit('ERROR: Critical - SQL QUERY: API LIKE');
				# Итого возвращаем кол-во лайков и дизов
				$query = "SELECT count(user_id), count(dislike) FROM content_likes WHERE c_id='".$value."';";
				$arr = sql_rselect($query);
				$resp_json['like'] = $arr[0]-$arr[1];
				$resp_json['dlike'] = $arr[1];
			break;
			default:
				exit('API Method not found'); # Если метод name не найден
			break;
		}
		exit(json_encode($resp_json, JSON_UNESCAPED_UNICODE));
	}	
	exit('API wrong format'); # Если POST name,value не найден
}
# FORMS
#============================================================
# REG
if (isset($_POST["reg"])) {
	$name = chk_input($_POST['reg_n']);
	$mail = chk_input($_POST['reg_m']);
	if (preg_match('/^\w+$/i', $name)===1 && filter_var($mail, FILTER_VALIDATE_EMAIL)) {
		$pass = md5(chk_input($_POST["reg_p"]));		
		$date = time();
		$role_def = '8';
		$img_def = 'blank'; #по идее удалить и проверять при выводе
		# Регистрируем нового юзера
		$query = "INSERT INTO users(name,password,role_id,cr_date,mail) VALUES('".$name."','".$pass."','".$role_def."','".$date."','".$mail."')";
		if (sql_insert($query) && acc_login($mail,$pass)) header('Location: /acc'); # сразу логинимся под юзером
		else $m_params['err'] = 'Что то пошло не так - возможно JS проверка';
	}
}
?>