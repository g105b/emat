<?php class Settings {
private static $_fileName;

public function __construct() {
	$this->_fileName = dirname(__DIR__) . "/settings";
}

}#