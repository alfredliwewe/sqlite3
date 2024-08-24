<?php
/**
 * Sql
 */
class SqlParser
{
	public $sql = "";

	function __construct($sql)
	{
		$this->sql = $sql;
		//$this->db = $db;
	}

	public function getColumnNames()
	{
		$firstWord = strtolower(explode(" ", trim($this->sql))[0]);
		
		if ($firstWord == "create") {
			$middle = Strings::cutBetween($this->sql, "(", ")");
		}
	}

	public function getRepeatedInfo(){
		//SELECT REPEATED(link) FROM links
		$column = Strings::cutBetween($this->sql, "(", ")");

		//find table name
		$cs = explode("from", strtolower($this->sql));
		$table = explode(" ", trim($cs[1]))[0];
		return [$table,$column];
	}
}
?>