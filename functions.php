<?php

function time_ago($time){
    $time = (int)trim($time);
	$labels = [
		['s', 60],
		['min', 3600],
		['h', 3600 * 24],
		['d', 3600 * 24 * 7],
		['w', 3600 * 24 * 7 * 4],
		['mon', 3600 * 24 * 7 * 30],
		['y', 3600 * 24 * 7 * 30 * 12]
	];

	$dif = time() - $time;

	$can = true;
	$label = null;
	$div = 1;

	if ($dif == 0) {
		return "now";
	}

	for ($i=0; $i < count($labels); $i++) { 
		if ($dif < $labels[$i][1]) {
			if($can){
				$can = false;
				$label = $labels[$i][0];

				if($i != 0){
					$div = $labels[$i-1][1];
				}
			}
		}
	}

	if ($label == null) {
		return "Unknown";
	}
	else{
		return floor($dif/$div).$label;
	}
}

function guidv4($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

class SQLiteResult {
	function __construct($result,$isResult=true)
	{
		if ($isResult) {
			$count = 0;
			$store = [];

			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				$count += 1;
				array_push($store, $row);
			}
			$this->store = $store;
			$this->num_rows = $count;
			$this->index = 0;
		}
		else{
			$this->store = $result;
			$this->num_rows = count($result);
			$this->index = 0;
		}
	}

	public function fetch_assoc()
	{
		if ($this->index < $this->num_rows) {
			$this->index += 1;
			return $this->store[$this->index - 1];
		}
		else{
			return false;
		}
	}

	public function fetchArray()
	{
		if ($this->index < $this->num_rows) {
			$this->index += 1;
			return $this->store[$this->index - 1];
		}
		else{
			return false;
		}
	}

	public function getColumnNames()
	{
		$names = [];
		if ($this->num_rows > 0) {
			foreach ($this->store[0] as $key => $value) {
				array_push($names, $key);
			}
		}

		return $names;
	}
}

class mysql_like extends sqlite3
{
	public $error = "";
	public $insert_id = 0;

	function __construct($file)
	{
		parent::__construct($file);
	}
	
	public function query($sql)
	{
		if (Strings::contains($sql,"REPEATED(")) {
			$sqlObject = new SqlParser($sql);
			$tdata = $sqlObject->getRepeatedInfo();

			$firstWord = strtolower(explode(" ", trim($sql))[0]);
			if ($firstWord == "select") {
				$data = [];

				$read = parent::query("SELECT {$tdata[1]} FROM {$tdata[0]} ");
				while($row = $read->fetchArray(SQLITE3_ASSOC)){
					if (isset($data[$row[$tdata[1]]])) {
						$data[$row[$tdata[1]]] += 1;
					}
					else{
						$data[$row[$tdata[1]]] = 1;
					}
				}

				//get the clean data
				$clean = [];
				foreach ($data as $key => $value) {
					if ($value > 1) {
						array_push($clean, [
							$tdata[0] => $key,
							'count' => $value
						]);
					}
				}

				return new SQLiteResult($clean, false);
			}
			elseif ($firstWord == "delete") {
				$data = [];
				$ids = [];

				$read = parent::query("SELECT {$tdata[1]},id FROM {$tdata[0]} ");
				while($row = $read->fetchArray(SQLITE3_ASSOC)){
					if (isset($data[$row[$tdata[1]]])) {
						$data[$row[$tdata[1]]] += 1;
						array_push($ids, $row['id']);
					}
					else{
						$data[$row[$tdata[1]]] = 1;
					}
				}

				if (count($ids) > 0) {
					$res = parent::query("DELETE FROM {$tdata[0]} WHERE id IN (".implode(",", $ids).")");
				}
				return true;
			}
			else{

			}
		}
		else{
			$result = parent::query($sql);
			$this->error = $this->lastErrorMsg();
			$chars = explode(" ", trim($sql));

			if (strtolower($chars[0]) == "select") {
				return new SQLiteResult($result);
			}
			elseif (strtolower($chars[0]) == "insert") {
				return $result;
			}
			else{
				return $result;
			}
		}
	}

	public function real_escape_string($str)
	{
		return $this->escapeString($str);
	}
}

function fileExtension($filename){
	$chars = explode(".", $filename);
	return strtolower($chars[count($chars)-1]);
}

function db_update($table, $cv, $where)
{
	global $db;

	$contentValues = [];
	foreach ($cv as $key => $value) {
		array_push($contentValues, "`$key` = '$value'");
	}

	$whereClause = [];
	foreach ($where as $key => $value) {
		array_push($whereClause, "`$key` = '$value'");
	}

	return $db->query("UPDATE `$table` SET ".implode(", ", $contentValues)." WHERE ".implode(" AND ", $whereClause));
}

function db_delete($table, $where)
{
	global $db;

	$whereClause = [];
	foreach ($where as $key => $value) {
		array_push($whereClause, "`$key` = '$value'");
	}

	return $db->query("DELETE FROM `$table` WHERE ".implode(" AND ", $whereClause));
}

function db_insert($table, $array)
{
	global $db;

	$columns = [];
	$values = [];
	$read = $db->query("SHOW COLUMNS FROM `$table`");
	while ($row = $read->fetch_assoc()) {
		array_push($columns, "`{$row['Field']}`");
		if ($row['Extra'] == "auto_increment") {
			array_push($values, "NULL");
		}
		else{
			$value = isset($array[$row['Field']]) ? $db->real_escape_string($array[$row['Field']]) : "0";
			array_push($values, "'$value'");
		}
	}

	$sql = "INSERT INTO `$table` (".implode(",",$columns).") VALUES (".implode(",",$values).")";
	$db->query($sql);
	return $db->insert_id;
}

function getColumnNames($db, $table)
{
	$columns = [];
	//$rows = [];

	$read = $db->query("PRAGMA table_info(`$table`)");
	while ($row = $read->fetchArray(SQLITE3_ASSOC)) {
		//$columns = array_keys($row);
		array_push($columns, $row['name']);
	}

	return $columns;
}

$image_extensions = ["jpg","png","jpeg","gif","webp"];
?>