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
	$this->_urlParts["query"]["market"] = $this->_settings->get("exchange");
	$this->loadHistoryData();
}

/**
 * Loads history data for current exchange.
 */
private function loadHistoryData() {
	foreach ($this->_currencyPairs as $pair) {
		if($this->_settings->get("dataRefresh") == true) {
			$this->refresh($pair);
		}
	}
}

/**
 * Attempts to use the refresh mechanism to update JSON files with up to one
 * year's worth of fresh data.
 */
private function refresh($pair) {
	$dt = new \DateTime();
	$dtValid = new \DateTime("-1 year");
	$dataFilePath = $this->_settings->get("data") 
		. "/"
		. $this->_settings->get("exchange")
		. ".$pair.json";

	if(!is_dir(dirname($dataFilePath))) {
		mkdir(dirname($dataFilePath), 0775, true);
	}

	if(file_exists($dataFilePath)) {
		$jsonFile = json_decode(file_get_contents($dataFilePath));
		$last = end($jsonFile);
		$dtValid = $this->buildDateTime($last[0]);
		fwrite(STDOUT, "Data file valid up to " 
			. $dtValid->format("Y-m-d H:i:s") . PHP_EOL);		
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

	$jsonFresh = $this->getJsonFromUrl($url, $dt, $pair);

	$jsonFileDirty = false;
	foreach ($jsonFresh as $row) {
		$dtRow = $this->buildDateTime($row[0]);
		if($dtRow > $dtValid) {
			$jsonFileDirty = true;
			$jsonFile[] = $row;
		}
	}

	if($jsonFileDirty) {
		fwrite(STDOUT, "Data file refreshed with new data." . PHP_EOL);
		file_put_contents($dataFilePath, json_encode($jsonFile));
	}
	else {
		fwrite(STDOUT, "Data file already up to date." . PHP_EOL);
	}
}

private function buildDateTime($string) {
	$dateTimeString = substr($string, 0, 10);

	if(strlen($string) >= 13) {
		$dateTimeString .= " ";
		$dateTimeString .= substr($string, 11, 2);
		$dateTimeString .= ":00:00";
	}
	else {
		$dateTimeString .= " 00:00:00";
	}
	
	return new \DateTime($dateTimeString);
}

private function getJsonFromUrl($url, $dt, $pair) {
	$invalidSeconds = $this->_settings->get("cachelength");
	$invalidMTime = new \DateTime("-$invalidSeconds seconds");
	$safeUrl = preg_replace("/(\/)/", "|", $url);
	$filePath = $this->_settings->get("cache") . "/$safeUrl";

	if(file_exists($filePath)
	&& new \DateTime("@" . filemtime($filePath)) > $invalidMTime) {
		fwrite(STDOUT, "$pair cache used." . PHP_EOL);
		return json_decode(file_get_contents($filePath));
	}

	fwrite(STDOUT, "Refreshing $pair..." . PHP_EOL);
	$ch = curl_init();
	curl_setopt_array($ch, [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
	]);
	$data = curl_exec($ch);
	curl_close($ch);

	$jsonFresh = json_decode($data);

	if(!empty($jsonFresh)) {
		if(!is_dir(dirname($filePath)) ) {
			mkdir(dirname($filePath), 0775, true);
		}
		file_put_contents($filePath, $data);

		return $jsonFresh;
	}

	fwrite(STDOUT, "No refresh for $pair." . PHP_EOL);
	return [];
}

}#