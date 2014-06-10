<?php namespace emat; class Settings {
/**
 * Represents the settings/configuration file on disk, creates and 
 * populates the file if it doesn't exist.
 */

private $_filePath = "settings";
private $_questions

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