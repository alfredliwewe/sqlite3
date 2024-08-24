<?php
/**
 * Arrays methods and extension of the native array functions available in php
 * 
 * @author Alfred Liwewe https://github.com/alfredliwewe
 */
class Arrays
{
	/**
	 * Kinda like map function in js
	 * 
	 * @param Array[Array[]] array
	 * @param string key
	 * 
	 * @return array
	 */
	public static function getArrayByKey($array, $key)
	{
		$data = [];

		for ($i=0; $i < count($array); $i++) { 
			array_push($data, $array[$i][$key]);
		}

		return $data;
	}


	public static function findMissingValues($bigArray, $smallArray) {
	    $missingValues = [];

	    foreach ($smallArray as $value) {
	        if (!in_array($value, $bigArray)) {
	            $missingValues[] = $value;
	        }
	    }

	    return $missingValues;
	}

	/**
	 * Getting values in array between two offsets
	 * Assuming the array is sorted already
	 * 
	 * @param int[]: input
	 * @param int: lower
	 * @param int: upper
	 * 
	 * @return int[]
	 */
	public static function valuesBetween($array, int $lower, int $upper){
		$result = [];
		for ($i=0; $i < count($array); $i++) { 
			if ($array[$i] >= $lower AND $array[$i] <= $upper){
				array_push($result, $array[$i]);
			}
		}

		return $result;
	}

	/**
	 * Merging associative array
	 * Tried $result = array_merge($array1,...)
	 * Didnt give better results
	 *
	 * @param arrays: .. input arrays
	 * 
	 * @return array: 
	 */
	public static function merge(){
		$result = [];

		foreach (func_get_args() as $array) {
			foreach ($array as $key => $value) {
				$result[$key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Return count of availabily of rows with key and its value
	 * 
	 * @param array[array[]]: rows
	 * @param array[] kv, key => value, you want to count their existence
	 * 
	 * @return int: count
	 */
	public static function countByKey($rows, $kv)
	{
		$count = 0;

		foreach ($rows as $row) {
			$found = true;

			foreach ($kv as $key => $value) {
				if ($row[$key] != $value) {
					$found = false;
				}
			}

			if ($found) {
				$count += 1;
			}
		}

		return $count;
	}

	/**
	 * 
	 */
	public static function shuffle($array, $length)
	{
		$chuncks = array_chunk($array,$length);
		$rows = [];
		foreach ($chuncks as $chunck) {
			shuffle($chunck);
			$rows = array_merge($rows, $chunck);
		}

		return $rows;
	}
}

//var_dump(Arrays::valuesBetween([1,2,3,4], 2,4));
?>