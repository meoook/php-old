<html>
<head>
  <title>Результат загрузки файла</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php


function file_upload($input_upload_name) {
	global $link;
	$errs = "";
	$f_ =  $_FILES[$input_upload_name];
	$f_dir = "upload/";
	$pos = ;
	
	# проверяем имя файла на различные "плохие" совпадения
	$blacklist = array("htm",".php","cgi",".exe",".pl",".asp",".fpl",".jsp",".wml");
	foreach ($blacklist as $f_ext) {
		if(strpos($f_[name],$f_ext)) {
			return false;
		}
	}
	if ($f_['error']>0) {
		print_r($_FILES);
		echo "<br>Ошибка при загрузке файла";
		return false;
	}
	if ($f_['size'] > 3*1024*1024) {
		print_r($_FILES);
		echo "<br>Размер файла превышает три мегабайта";
		return false;
	}
	# Если файл загружен методом POST
	if (is_uploaded_file($f_['tmp_name'])) {
		if (end(explode(".",strtolower($f_['name']))) == "txt") {
			echo "формат txt";
			$f_path=$f_dir.basename($f_['name']);
			echo $f_path;
			if (file_exists($f_path)) echo '<br />Файл существует<br />';
			else echo "<br />Файл не существует<br />";
			if (move_uploaded_file($f_['tmp_name'], $f_path)) {
				echo '<br />file moved to /'.$f_path.'<br />';
				$fp = fopen($f_path, 'rt');
				unset($f_method);
				# $query="";
				if ($fp) {
				# количество проверяемых строк на кол-во символов
					while (ftell($fp)<200 and !feof($fp) and !isset($f_method)) {
						$stroka=fgets($fp);
						$stroka = iconv('WINDOWS-1251', 'UTF-8', $stroka);
						# Переменнкю метод возможно потребуется в сессию
						if (strpos($stroka,"соединений клиента - 57109")) $f_method="mts";
						if (strpos($stroka,"cucucucu")) $f_method="cucm";
					}
					echo ftell($fp);
					if (isset($f_method)) {
						echo 'МЕТОД '.$f_method.'<br /><hr />';
						while (!feof($fp)) {
							$stroka=fgets($fp);
#							echo $stroka.'<br />';
							if ($f_method=="mts") {
								if (strpos($stroka, "571092")==4) {
									$chars = preg_split('/ /', $stroka, -1, PREG_SPLIT_NO_EMPTY);
									# закидываем эти строки в массив, чтоб сделать общую проверку для 2-х файлов
									$count=count($chars);
									$chars[6] = rtrim(substr($stroka, 90, 41));
									$chars[7] = rtrim(substr(substr($stroka, 131), 0, -35));
									#  Формируем значения для занесение в базу
									$ins_sql_mts = "INSERT INTO buff_mts (date_time,dst,city_country,zone,duration,price,call_cost) VALUES ";
									$ins_sql_value = "('".$chars[1]." ".$chars[2]."','".$chars[5]."','".$chars[6]."','".$chars[7]."','".$chars[$count-5]."','".$chars[$count-4]."','".$chars[$count-3]."')";
									$ins_sql = iconv('WINDOWS-1251', 'UTF-8', $ins_sql_mts.$ins_sql_value.";<br />");
									#  Сюда проверки строки или отдельных столбцов на ошибки
									if ($count<12) $errs .= '<br />мало столбцов '.iconv('WINDOWS-1251', 'UTF-8', $stroka);
									elseif (strpos($chars[6], "   ") or strpos($chars[7], "   ")) $errs .= '<br />ошибка зон '.iconv('WINDOWS-1251', 'UTF-8', $stroka);
									elseif (strlen($chars[1]) <> 10) $errs .= '<br />ошибка даты '.iconv('WINDOWS-1251', 'UTF-8', $stroka);
									elseif (strlen($chars[2]) <> 8) $errs .= '<br />ошибка времени '.iconv('WINDOWS-1251', 'UTF-8', $stroka);
									
									# теперь записываем
									else echo $ins_sql;
									#  В вместо этого надо выполнять мускуль запрос (незабыть про кодировку)
									# $result=mysqli_query($link, $ins_sql);
									# if (!$result) echo 'ERORR SQL ';
									# if(mysqli_num_rows($result)==1) {
									# $row=mysqli_fetch_array($result);
									# }
								}
							}
							elseif ($f_method=="cucm" and strpos($stroka,"roviders")) {
								$chars = preg_split('/ /', $stroka, -1, PREG_SPLIT_NO_EMPTY);
								$query .= "INSERT INTO test (bbb,ccc,ddd) values (".$chars[1].",".$chars[2].",".$chars[3].");<br />";
							}
						}
						# тут надо перекинуть файлик в бекап проверив на всякие ошибки(или не тут)
						echo $errs;
						if (isset($f_method)) return true;
					}
					else echo 'метод загрузки данных не определен';
				# Закрываем и удаляем исходный файл
				# Добавить проверку - если есть метод, тогда кидаем файл в папку под созданным именем (имена в базу)
				fclose($fp);
				unlink($f_path);
				}
			}
		}
		elseif (end(explode(".",strtolower($f_['name']))) == "xls") {
			echo "формат xls";
			if (move_uploaded_file($f_['tmp_name'], $f_dir.basename($f_['name']))) echo '<br>file moved to /upload/'.$f_['name'].'<br>';
			return true;
		}
		else echo "Неверный формат файла";
	}
}

# эту строчку добавлять перед формой
$f_upload='upload_file';

# Проверяем нажатие кнопки
if (isset($_POST['upload'])) file_upload($f_upload);
# сама форма
echo '
	<h2><p><b> Форма для загрузки файлов </b></p></h2>
	<form action="test2.php" method="post" enctype="multipart/form-data">
	<input type="file" name="'.$f_upload.'" id="xaxa" style="visibility:hidden;"><br>
	<input type="submit" value="Загрузить" name="upload"><br>
	<label for="xaxa">xaxa</label>
	</form>';
?>

</body>
</html>