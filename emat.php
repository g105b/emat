<?php namespace emat;

require "Class/Settings.php";
require "Class/Data.php";
require "Class/Trade.php";

// Only proceed when a settings file can be loaded.
$settings	= new Settings();
$data		= new Data();
$trade		= new Trade($settings, $data);

// Loop until the Trade class returns false.
// When trading on paper, Trade::next() will return false when the historical
// data ends. When trading for real, false will only be returned from a fatal
// error.
while(false !== ($tradeAction = $trade->next()) ) {
	fwrite(STDOUT, $tradeAction);
}

// Once the loop is broken, no more trading will take place until the script
// is run again.
fwrite(STDOUT, PHP_EOL . "emat terminated" . PHP_EOL);