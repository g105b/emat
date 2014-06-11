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
		"default" => "btc-e",
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
	"DATA" => [
		"description" => "Historic data directory",
		"default" => "Data",
	],
	"DATAREFRESH" => [
		"description" => "Attempt automatic data refresh? (yes/no)",
		"default" => "no"
	],
	"DRYRUN" => [
		"description" => "Dry run (yes/no)",
		"type" => "bool",
		"default" => "yes",
	],
];

public function __construct() {
	// Force absolute path
	$this->_filePath = dirname(__DIR__) . "/" . $this->_filePath;

	$correct = false;
	if(is_file($this->_filePath)) {
		// Attempt to load settings file.
		$this->load();
		// Ask user if they want to use loaded data.
		$correct = $this->check();
	}
	
	// Take new settings configuration until user confirms they are correct.
	while(!$correct) {
		if(!$correct) {
			$this->create();			
			$correct = $this->check();
		}
	}
}

/**
 * Displays all configuration to user, asks for confirmation.
 */
private function check() {
	foreach ($this->_conf as $code => $details) {
		fwrite(STDOUT, 
			str_pad($code, 12, " ", STR_PAD_LEFT)
			. ": "
			. (isset($details["value"]) ? $details["value"] : "")
			. PHP_EOL
		);
	}
	$input = readline("Use these settings? [yes/no]");
	return (strtolower($input[0]) === "y");
}

/**
 * Asks user for all required information, serialises to settings file.
 */
private function create() {
	// Loop over every conf variable.
	foreach ($this->_conf as $code => $details) {
		unset($this->_conf[$code]["value"]);
		$prompt = $details["description"] . " ";
		
		// If there is a default value, display to user.
		if(isset($details["default"])) {
			$prompt .= "[" . $details["default"] . "] ";
		}

		$prompt .= ": ";

		// Only progress when value is set (from user input or default).
		while(!isset($this->_conf[$code]["value"])) {
			$input = trim(readline($prompt));

			// If user input is given and datatype is allowed, store value.
			if(strlen($input) > 0) {
				if(isset($details["type"])) {
					// Perform a type check on provided input.
					switch($details["type"]) {
					case "number":
						if(is_numeric($input)) {
							$this->_conf[$code]["value"] = (double)$input;
						}
						break;
					case "int":
					case "integer":
						if(ctype_digit($input)) {
							$this->_conf[$code]["value"] = (int)$input;
						}
						break;
					case "bool":
					case "boolean":
						if(strtolower($input[0]) == "y") {
							$this->_conf[$code]["value"] = true;
						}
						else if(strtolower($input[0]) == "n") {
							$this->_conf[$code]["value"] = false;
						}
						break;
					}

					if(!isset($details["value"])) {
						// Type check failed.
						fwrite(
							STDOUT, 
							"Parameter must be type " 
							. $details["type"]
							. "." 
							. PHP_EOL
						);
					}
				}
				else {
					// No type check required (string input allowed).
					$this->_conf[$code]["value"] = $input;					
				}
			}
			else if(isset($details["default"])) {
				// Convert default "yes"/"no" to true/false.
				if(isset($details["type"])
				&& ($details["type"] == "bool" 
				|| $details["type"] == "boolean")
				&& is_string($details["default"])) {
					$this->_conf[$code]["value"] = 
						($details["default"] == "yes");
				}
				else {
					$this->_conf[$code]["value"] = $details["default"];
				}

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

	// All conf variables set - output to file.
	$this->save();
}

private function save() {
	$fh = fopen($this->_filePath, "w");
	foreach ($this->_conf as $code => $details) {
		fwrite($fh, implode(" ", [$code, $details["value"]]) . PHP_EOL);
	}
	fclose($fh);
}

private function load() {
	$fh = fopen($this->_filePath, "r");
	while(false !== ($line = fgets($fh)) ) {
		$data = explode(" ", $line, 2);
		var_dump($data);
		// if(count($data) === 1) {
		// 	$data[1] = "";
		// }

		if(isset($this->_conf[$data[0]])) {
			$this->_conf[$data[0]]["value"] = trim($data[1]);
		}
	}
	fclose($fh);
}

}#