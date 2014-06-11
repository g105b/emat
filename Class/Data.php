<?php namespace emat; class Data {
/**
 * Used by Trade class to store and iterate over historic data.
 * Can load backlog of past data from a file, or simply log all new data
 * as it arrives. Typically, the more historic data, the better decisions can
 * be made by the trader.
 *
 * Data filenames are required to be in this format:
 * exchange-name.currency1-currency2
 * example: btc-e.btc-usd
 *
 * Data is required to be a JSON array. Each element must be an array with the
 * following elements:
 * 1: Date time string in the format YYYY-MM-DD [with optional HH:II:SS]
 * 2: Low price.
 * 3: Open price.
 * 4: Close price.
 * 5: High price.
 * 6: Volume traded.
 */

private $_settings;
private $_currencyPairs = [
	"btc-usd",
	"ltc-btc",
	"ltc-usd",
	"btc-rur",
	"btc-eur",
	"ltc-rur",
	"ltc-eur",
	"usd-rur",
];
private $_urlParts = [
	"domain" => "cryptocoincharts.info", // /v2/fast/period.php?pair="
	"apiVersion" => "v2/fast",
	"script" => "period.php",
	"query" => [
		"pair" => null,
		"market" => null,
		"time" => "12m",
		"resolution" => "1h",
	],
];
private $_dataFields = [
	0 => "dateTime",
	1 => "low",
	2 => "open",
	3 => "close",
	4 => "high",
	5 => "median",
	6 => "volume",
];

public function __construct($settings) {
	$this->_settings = $settings;
	$this->_urlParts["query"]["market"] = $this->_settings["EXCHANGE"]["value"];
	$this->loadHistoryData();
}

/**
 * Loads history data for current exchange.
 */
private function loadHistoryData() {
	foreach ($this->_currencyPairs as $pair) {
		if($this->_settings["DATAREFRESH"]["value"] === true) {
			$this->refresh($pair);
		}
	}
}

/**
 * Attempts to use the refresh mechanism to update JSON files with up to one
 * year's worth of fresh data.
 */
private function refresh($pair) {
	$dt = new DateTime();
	$dtValid = new DateTime("-1 year");
	$dataFilePath = $this->_settings["DATA"] 
		. "/"
		. $this->_settings["EXCHANGE"]
		. ".$pair";

	fwrite(STDOUT, "Refreshing $pair..." . PHP_EOL);

	if(file_exists($dataFilePath)) {
		$jsonFile = json_decode(file_get_contents($dataFilePath));
		$last = end($jsonFile);
		$dtValid = $this->buildDateTime($last[0]);
		fwrite(STDOUT, "Data file valid up to" 
			. $dtValid->format("Y-m-d") . PHP_EOL);		
	}

	$daysInvalid = $dt->diff($dtValid)->days;
	$this->_urlParts["query"]["time"] = $daysInvalid . "d";
	$this->_urlParts["query"]["pair"] = $pair;

	$url = "http://"
		. $this->_urlParts["domain"]
		. "/"
		. $this->_urlParts["apiVersion"]
		. "/"
		. $this->_urlParts["script"]
		. "?"
		. http_build_query($this->_urlParts["query"]);

	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
	]);
	$jsonFresh = json_decode(curl_exec($ch));
	curl_exec($ch);

	$jsonFileDirty = false;
	foreach ($jsonFresh as $row) {
		$dtRow = $this->buildDateTime($row[0]);
		if($dtRow > $dtValid) {
			$jsonFileDirty = true;
			$jsonFile[] = $row;
		}
	}

	if($jsonFileDirty) {
		file_put_contents($dataFilePath, json_encode($jsonFile));
	}
}

private function buildDateTime($string) {
	$dateTimeString = substr($string, 0, 10);

	if(strlen($last[0]) >= 13) {
		$dateTimeString .= " ";
		$dateTimeString .= substr($last[0], 11, 2);
		$dateTimeString .= ":00:00";
	}
	else {
		$dateTimeString .= " 00:00:00";
	}
	
	return new DateTime($dateTimeString);
}

}#