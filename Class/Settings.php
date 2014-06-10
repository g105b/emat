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
	// Loop over every conf variable.
	foreach ($this->_conf as $code => $details) {
		$prompt = $details["description"] . " ";
		
		// If there is a default value, display to user.
		if(isset($details["default"])) {
			$prompt .= "[" . $details["default"] . "] ";
		}

		$prompt .= ": ";

		// Only progress when value is set (from user input or default).
		while(!isset($details["value"])) {
			$input = trim(readline($prompt));

			// If user input is given and datatype is allowed, store value.
			if(strlen($input) > 0) {
				if(isset($details["type"])) {
					// Perform a type check on provided input.
					switch($details["type"]) {
					case "number":
						if(is_numeric($input)) {
							$details["value"] = (double)$input;
						}
						break;
					case "int":
					case "integer":
						if(ctype_digit($input)) {
							$details["value"] = (int)$input;
						}
						break;
					case "bool":
					case "boolean":
						if(strtolower($input[0]) == "y") {
							$details["value"] = true;
						}
						else if(strtolower($input[0]) == "n") {
							$details["value"] = false;
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
					$details["value"] = $input;					
				}
			}
			else if(isset($details["default"])) {
				// Convert default "yes"/"no" to true/false.
				if(isset($details["type"])
				&& ($details["type"] == "bool" 
				|| $details["type"] == "boolean")
				&& is_string($details["default"])) {
					$details["value"] = ($details["default"] == "yes");
				}
				else {
					$details["value"] = $details["default"];
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
	// TODO.
}

}#