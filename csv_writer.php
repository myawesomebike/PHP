<?

function writeCSV($filename,$data) {

	header("Content-Type: text/plain");
	header("Content-Disposition: attachment; filename=" . $filename . ".csv");
	header("Content-Type: application/csv");
	$columns = count($data);
	$rows = 0;
	for($c = 0; $c <= $columns; $c++) {
		if(array_key_exists($c,$data)) {
			$rowCount = count($data[$c]);
			if($rowCount > $rows) {
				$rows = $rowCount;
			}
			for($r = 0; $r <= $rows; $r++) {
				if(array_key_exists($r,$data[$c])) {
					echo '"' . $data[$c][$r] . '"';
				}
				echo ',';
			}
		}
		echo "\n";
	}
}

?>
