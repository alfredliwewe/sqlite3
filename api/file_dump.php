<?php
function read_all($dir){
	$data = [];
	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {
	    	$folders = [];
	        $files = [];

	        $not_allowed = [".", ".."];

	        while (($file = readdir($dh)) !== false) {
	        	$file = trim($file);
	        	if (!in_array($file, $not_allowed)) {
		        	if (filetype($dir.$file) == "dir") {
		        		array_push($folders, $file);
		        	}
		        	else{
		        		array_push($files,$file);
		        	}
	        	}
	        	else{
	        		//echo "Yeah $file";
	        	}
	        }
	        closedir($dh);

	        sort($folders);
	        sort($files);

	        foreach($folders as $file){
		        array_push($data, [
	        		'name' => $file,
	        		'type' => "dir",
	        		'children' => read_all($dir.$file."/")
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
	return $data;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(read_all("../"));
?>