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
		"default" => 60,
	],
	"EMASHORT" => [
		"description" => "Short EMA",
		"default" => 10,
	],
	"EMALONG" => [
		"description" => "Long EMA",
		"default" => 21
	],
	"WAIT" => [
		"description" => "Period to check threshold before trade (minutes)",
		"default" => 0,
	],
	"BUY" => [
		"description" => "Buy threshold (%)",
		"default" => 0.25,
	],
	"SELL" => [
		"description" => "Sell threshold (%)",
		"default" => 0.25,
	],
	"DRYRUN" => [
		"description" => "Dry run (yes/no)",
		"default" => "yes",
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

}

}#