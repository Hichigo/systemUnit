<?php
// header('Content-type: text/html; charset=utf-8');

	$mysqli = new mysqli('localhost', 'root', '', 'system_unit');

	if ($mysqli->connect_error) {
		die('Ошибка подключения (' . $mysqli->connect_errno . ') '. $mysqli->connect_error);
	} else {
		echo "MySQL ok!";
	}

	$dataPath = Array(
			Array('.\\accessories\\', 'cpu'),
			Array('.\\accessories\\', 'video'),
			Array('.\\accessories\\', 'psu'),
			Array('.\\accessories\\', 'hull'),
			Array('.\\accessories\\', 'motherboard'),
			Array('.\\accessories\\', 'hdd'),
			Array('.\\accessories\\', 'culling'),
			Array('.\\accessories\\', 'ram')
		);

// START MAIN LOOP
	foreach ($dataPath as $dataK => $val) {
	
		$path = $val[0].$val[1];
		$files = scandir($path);

		$nFiles = count($files);
		$max = 0;

		$queryes = Array();
		$r = Array();
		for($i = 2; $i < $nFiles; $i++) {
			$f = fopen($path.'\\'.$files[$i], 'rb');
			$jsonAttr = json_decode(fread($f, filesize($path.'\\'.$files[$i])));
			fclose($f);

			// выбираем все возможные имена колонок для каждой таблицы
			foreach ($jsonAttr as $jsk => $jsv) {
				foreach ($jsv as $kjs => $vjs) {
					if(!in_array($kjs, $r)) {
						$r[] = $kjs;
					}
				}
			}
			$queryes[] = insert_table_q($jsonAttr, $val[1], $mysqli); // запросы для добавления в бд из каждого файла
		}

		$q = create_table_q($r, $val[1], $mysqli);
		
		if ($mysqli->query($q) === TRUE) {
			echo "Table MyGuests created successfully<br>";
		} else {
			echo "Error creating table: " . $mysqli->error."<br>";
			echo "<pre>";
			echo $q;
			echo "</pre>";
		}

		foreach ($queryes as $index => $mass) { //добавление в бд
			foreach ($mass as $key => $value) {
				if ($mysqli->query($value) === TRUE) {
					echo "Add to ".$val[1]." succesfully!!!<br>";
				} else {
					echo "Error creating table: " . $mysqli->error."<br>";
					echo "<pre>";
					echo $value;
					echo "</pre>";
				}
			}
		}
	}
// END MAIN LOOP


	function create_table_q($arr, $tblName, $msql) {
		$q = "CREATE TABLE IF NOT EXISTS ".$tblName."(id int unique auto_increment,"; //

		foreach ($arr as $key => $value) {
			$q .= "`".str_replace("\"", "'", $value)."`"." TEXT,"; // кавычки в названиях колонок удалить (из за этого была ошибка)
		}
		$q = substr($q, 0, -1).")";
		return $q;
	}

	function insert_table_q($arr, $tblName, $msql) {
		foreach ($arr as $key => $value) {
			$q = "INSERT INTO `".$tblName."` ";
			$keys = "(";
			$vals = "VALUES(";
			foreach ($value as $k => $v) {
				$keys .= "`".str_replace("\"", "'", $k)."`,"; // кавычки в названиях колонок удалить (из за этого была ошибка)
				$vals .= "'".$msql->real_escape_string($v)."',";
			}
			$keys = substr($keys, 0, -1).")";
			$vals = substr($vals, 0, -1).")";
			$q .= $keys.$vals;
			$r[] = $q;
		}
		return $r;
	}

	// function check_max_attr($arr) { // переписать для сбора всех возможных полей
	// 	$r = Array();
	// 	foreach ($arr as $k => $v) {
	// 		foreach ($v as $key => $val) {
	// 			echo !in_array($key, $r);
	// 			if(!in_array($key, $r)) {
	// 				$r[] = $key;
	// 			}
	// 		}
	// 	}
	// 	return $r;
	// }