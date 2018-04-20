<?php

use fhibox\configuration\ConfigurationLoader;
use fhibox\configuration\configurations\ArrayConfiguration;
use fhibox\configuration\configurations\EmptyConfiguration;


define ('PATH_TO_LIB',  '../../..');

// Use the 'fhibox meta library' autloader:
require_once PATH_TO_LIB . '/' . 'fhibox/autoload.php';

// Import Log4Php
require_once PATH_TO_LIB .  '/' . 'vendor/apache/log4php/src/main/php/Logger.php';

require_once 'MockObjectC.php';

/**
 * To launch this test :
 *
 * Francois hill@Fhibox-PC-2 /cygdrive/c/Users/Francois hill/Documents/03-WORK/50-CODE/FRONT/my/work/scripts_2
 * $ php phpunit.phar --bootstrap src/autoload.php tests/fhibox/nestor/tools/configuration/ConfigurationLoaderTest
 *
 * @author fhi
 * @since 12/12/2014
 */
class ConfigurationLoaderTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var \Logger
	 */
	protected static $logger;
	protected static $conf_dir ;


	public static function setUpBeforeClass()
	{
		/*        */	\Logger::configure(__DIR__ . '/log4php.conf.ini');
		/*        */	self::$logger = \Logger::getLogger(str_replace('\\', '.', __CLASS__));
		/*        */	self::$logger->debug("setUpBeforeClass");

		self::$conf_dir       = __DIR__. '/' .'conf';
	}

	/**
	 * Tests just the basic place holder replacement function
	 */
	function testConvertPlaceholdersStr()
	{ /*        */	self::$logger->debug("testConvertPlaceholdersStr");
		$string          = "The name is %name% and the surname is %surname% %%";
		$string_expected = "The name is <obj name> and the surname is <obj surname> %%";

		$loader     = new ConfigurationLoader(self::$conf_dir);
		$loader     ->setLogger(self::$logger);
		$obj        = new MockObjectC();
		$new_string = $loader -> processPlaceholdersStr($string, $obj);
		/*        */  self::$logger->debug("new_string = $new_string");
		$this->assertTrue($new_string == $string_expected, "String after placeholders have been replaced is not as expected");
	}

	/**
	 * For the moment this test function's goal is simply to assist
	 * in the making of functions in ConfigurationLoader
	 */
	function testConvertPlaceholders()
	{ /*        */	self::$logger->debug("testConvertPlaceholders");
		$arr_expected = Array(
		                       "name"                 => "I AM C",
		                       "path_workdir_pull_to" => "/my/repos/DEV/fhibox1",
		                       "param1"               => "DEV",
		                       "param2"               => "targetEEnv",
		                       "param3"               => "TargetEnv",
		                       "param4"               => "DEV<obj name><obj surname>",
		                       "param5"               => "jeremy",
		                     );

		#// Testing the lower-lever functions...
		#$obj            = new MockObject();
		#$class_name     = get_class($obj);
		#$conf_file_path = self::$conf_dir.DIRECTORY_SEPARATOR.$class_name . ".ini";
		#/*        */  self::$logger->debug("conf_file_path = $conf_file_path");
		#$arr_virgin     = parse_ini_file($conf_file_path);
		#/*        */  self::$logger->debug("Configuration array, BEFORE going through place holder processing = " . print_r($arr_virgin, true));
		#$loader         = new ConfigurationLoader($conf_dir);
		#$arr_processed  = $loader -> processPlaceholdersStr($arr_virgin, $obj);
		#/*        */  self::$logger->debug("Configuration array, AFTER going through place holder processing = " . print_r($arr_processed, true));
		#$this->assertTrue($arr_processed == $arr_expected, "Array after placeholders have been replaced is not as expected");

		// Now we try by using the top function directly:
		$obj          = new MockObjectC();
		$loader       = new ConfigurationLoader(self::$conf_dir);
		$loader       ->setLogger(self::$logger);
		$conf         = $loader->loadForClass($obj);

		$this->assertTrue(! ($conf instanceof EmptyConfiguration), "conf is NOT expected to be of type EmptyConfiguration");
		$this->assertTrue(   $conf instanceof ArrayConfiguration, "conf is expected to be of type ArrayConfiguration");

		/*        */  self::$logger->debug("Configuration array, directly loaded : " . print_r($conf->getInnerArray(), true));

		$this->assertTrue($conf->getInnerArray() == $arr_expected, "Array after placeholders have been replaced is not as expected");

	}
}