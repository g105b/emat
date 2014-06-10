<?php namespace emat; class Trade {

private $_settings;
private $_data;

public function __construct($settings, $data) {
	$this->_settings = $settings;
	$this->_data = $data;
}

public function next() {
	return false;
}

}#