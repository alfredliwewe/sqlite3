<?php
require '../functions.php';
require '../includes/String.php';
require 'Sql.php';

$db = new mysql_like("../database.db");
$time = time();

$allowable = ['db', 'db3'];

if (isset($_GET['getData'])) {
	$data = [];
	$dir = $_GET['getData'];

	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	    	$folders = [];
	        $files = [];

	        while (($file = readdir($dh)) !== false) {
	        	if (filetype($dir.$file) == "dir") {
	        		array_push($folders, $file);
	        	}
	        	else{
	        		$chars = explode(".", strtolower($file));
	        		$ext = $chars[count($chars)-1];
	        		if (in_array($ext, $allowable)) {
	        			array_push($files,$file);
	        		}
	        	}
	        	
	        }
	        closedir($dh);

	        sort($folders);
	        sort($files);

	        foreach($folders as $file){
		        array_push($data, [
	        		'name' => $file,
	        		'type' => "dir"
	        	]);
			}
			foreach($files as $file){
		        array_push($data, [
	        		'name' => $file,
	        		'type' => "file"
	        	]);
			}
	    }
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
}
elseif (isset($_GET['getDatabases'])) {
	$data = [];

	$read = $db->query("SELECT * FROM databases");
	while($row = $read->fetch_assoc()){
		array_push($data, $row);
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
}
elseif (isset($_POST['deleteDatabase'])) {
	db_delete("databases", ['id' => $_POST['deleteDatabase']]);

	echo "database record deleted";
}
elseif (isset($_POST['saveData'], $_POST['file'])) {
	$dir = $db->real_escape_string($_POST['saveData']);
	$file = $db->real_escape_string($_POST['file']);

	$ins = $db->query("INSERT INTO databases (id, name, dir) VALUES (NULL, '$file', '$dir')");
	echo "Successfully indexed database";
}
elseif (isset($_GET['getTables'], $_GET['name'])) {
	$db3 = new mysql_like($_GET['getTables'].$_GET['name']);

	$data = [];

	$tablesquery = $db3->query("SELECT name, sql FROM sqlite_master WHERE type='table';");
	while ($row = $tablesquery->fetch_assoc()) {
		array_push($data, $row);
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
}
elseif (isset($_GET['getTableData'], $_GET['dir'], $_GET['database'])) {
	$table = $_GET['getTableData'];

	$db3 = new mysql_like($_GET['dir'].$_GET['database']);
	$dbx = new sqlite3($_GET['dir'].$_GET['database']);

	$data = [];

	$read = $db3->query("SELECT * FROM $table");
	$data['cols'] = getColumnNames($dbx, $table);
	$data['rows'] = $read->store;

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
}
elseif (isset($_GET['getTableStructure'], $_GET['dir'], $_GET['database'])) {
	$table = $_GET['getTableStructure'];

	$db3 = new sqlite3($_GET['dir'].$_GET['database']);

	$columns = [];
	$rows = [];

	$read = $db3->query("PRAGMA table_info(`$table`)");
	while ($row = $read->fetchArray(SQLITE3_ASSOC)) {
		$columns = array_keys($row);
		array_push($rows, $row);
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode([
		'cols' => $columns,
		'rows' => $rows
	]);
}

elseif (isset($_GET['runQuery'], $_GET['table'], $_GET['dir'], $_GET['database'])) {
	$table = $_GET['table'];
	$sql = $_GET['runQuery'];

	$dir = $db->real_escape_string($_GET['dir']);
	$database = $db->real_escape_string($_GET['database']);
	$save_sql = $db->real_escape_string($sql);

	//save the query
	$db->query("INSERT INTO history (id,dir,database,query,`time`) VALUES (NULL, '$dir', '$database', '$save_sql', '$time')");

	$db3 = new mysql_like($_GET['dir'].$_GET['database']);

	$data = [];

	$read = $db3->query($sql);
	$firstWord = strtolower(explode(" ", trim($sql))[0]);
	if ($firstWord == "select") {
		$data['cols'] = $read->getColumnNames();
		$data['rows'] = $read->store;
	}
	else{
		$data['cols'] = [];
		$data['rows'] = [];
	}

	//header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
}
elseif(isset($_POST['deleteColumn'], $_POST['table'], $_POST['dir'], $_POST['database'])){
	$db = new sqlite3($_POST['dir'].$_POST['database']);

	$db->query("ALTER TABLE {$_POST['table']} DROP COLUMN {$_POST['deleteColumn']}");

	echo json_encode(['status' => true, 'message' => 'Success']);
}
elseif (isset($_POST['dir'], $_POST['db'], $_POST['new_table'])) {
	$dir = $_POST['dir'];
	$db = $_POST['db'];
	$new_table = $_POST['new_table'];

	$db3 = new mysql_like($dir.$db);

	$cols = [];

	for ($i=0; $i < (int)$_POST['count']; $i++) { 
		$name = $_POST['col_'.$i];
		$type = $_POST['type_'.$i];

		array_push($cols, $name." ".$type.(isset($_POST['ai_'.$i]) ? " PRIMARY KEY AUTOINCREMENT":""));
	}
	//proceed
	$db3->query("CREATE TABLE IF NOT EXISTS $new_table (".implode(",", $cols).")");
	echo json_encode(['status' => true, 'message' => "Success"]);
}
elseif (isset($_GET['getPrevious'], $_GET['database'])) {
	$dir = $_GET['getPrevious'];
	$database = $_GET['database'];

	$data = [];
	$read = $db->query("SELECT * FROM history WHERE dir = '$dir' AND database = '$database' ");
	while ($row = $read->fetch_assoc()) {
		array_push($data, $row);
	}

	//header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
}
else{
	echo "no data - ". json_encode($_GET);
}
?>