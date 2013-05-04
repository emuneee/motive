<?php

namespace api\utils;

use Slim\Log;

/**
 * Creates a log file at the designated location
 * that location is document root/../logs
 */
class LogWriter 
{
	/**
     * @var array
     */
    protected static $levels = array(
        Log::FATAL => 'FATAL',
        Log::ERROR => 'ERROR',
        Log::WARN =>  'WARN',
        Log::INFO =>  'INFO',
        Log::DEBUG => 'DEBUG'
    );

	function __construct() {

	}

	/**
	 * Writes a message to the log file
	 */
	public function write($message, $level = null) {
		$log_date = date("Y-m-d");
		$log_time = date("Y-m-d-H:i:s");
		$file = fopen($_SERVER["DOCUMENT_ROOT"]."/../logs/".
			"log-".$log_date.".php", "a");
		fwrite($file, "[".self::$levels[$level]."] ".$log_time." - ".$message."\n");
		fclose($file);
	}

	public function debug($data) { }

	public function info($data) { }

	public function warn($data) { }

	public function error($data) { }
}