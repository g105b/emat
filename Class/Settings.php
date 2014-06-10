<?php namespace emat; class Settings {
/**
 * Represents the settings/configuration file on disk, creates and 
 * populates the file if it doesn't exist.
 */

private $_filePath = "settings";
private $_conf = [
	"APIKEY" => [
		"description" => "API key",
	],
	"APISECRET" => [
		"description" => "API secret",
	],
	"EXCHANGE" => [
		"description" => "Exchange",
		"default" => "BTCE",
	],
	"FREQ" => [
		"description" => "Trade frequency (minutes)",
		"type" => "integer",
		"default" => 60,
	],
	"EMASHORT" => [
		"description" => "Short EMA",
		"type" => "integer",
		"default" => 10,
	],
	"EMALONG" => [
		"description" => "Long EMA",
		"type" => "integer",
		"default" => 21
	],
	"WAIT" => [
		"description" => "Period to check threshold before trade (minutes)",
		"type" => "number",
		"default" => 0,
	],
	"BUY" => [
		"description" => "Buy threshold (%)",
		"type" => "number",
		"default" => 0.25,
	],
	"SELL" => [
		"description" => "Sell threshold (%)",
		"type" => "number",
		"default" => 0.25,
	],
	"DRYRUN" => [
		"description" => "Dry run (yes/no)",
		"type" => "bool",
		"default" => true,
	],
];

public function __construct() {
	// Force absolute path
	$this->_filePath = dirname(__DIR__) . "/" . $this->_filePath;

	if(!is_file($this->_filePath)) {
		$this->create();
	}
}

/**
 * Asks user for all required information, serialises to settings file.
 */
private function create() {
	foreach ($this->_conf as $code => $details) {
		$prompt = $details["description"] . " ";
		
		if(isset($details["default"])) {
			$prompt .= "[" . $details["default"] . "] ";
		}

		$prompt .= ": ";

		while(!isset($details["value"])) {
			$input = trim(readline($prompt));

			if(strlen($input) > 0) {
				$details["value"] = $input;
			}
			else if(isset($details["default"])) {
				$details["value"] = $details["default"];
				fwrite(
					STDOUT, 
					"Using default value, " . $details["default"] . PHP_EOL
				);
			}
			else {
				fwrite(STDOUT, "Required parameter!" . PHP_EOL);
			}
		}
	}
}

}#