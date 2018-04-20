<?php

use fhibox\nestor\application\arguments\ArgumentSourceFile;
use fhibox\nestor\application\values\TypeEnv;
use fhibox\nestor\application\values\TypeRepository;

require_once '../../../bootstrap.php';

/**
 * To launch this test :
 *
 * Francois hill@Fhibox-PC-2 /cygdrive/c/Users/Francois hill/Documents/CODE/FRONT/my/work/scripts_2
 * $ php phpunit.phar --bootstrap src/autoload.php tests/fhibox/nestor/application/arguments/ArgumentSourceFileTest
 *
 * @author fhi
 * @since 12/12/201*
*/
class ArgumentSourceFileTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var \Logger
	 */
	protected static $logger;

	public static function setUpBeforeClass()
	{
		/*        */	self::$logger = \Logger::getLogger(str_replace('\\', '.', __CLASS__));
		/*        */	self::$logger->debug("");
	}

	function testGetTargetFilePath()
	{ $test_sets=array();
		// $test_sets[] = array(repos,  file, env, expected result)
		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/file"                  , TypeEnv::DEV    , "/my/www/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/file"                  , TypeEnv::STAGING, "/my/www/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/file"                  , TypeEnv::PROD   , "/my/work/var/prod3/sources/www/file");

		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/trunkfile"             , TypeEnv::DEV    , "/my/www/trunkfile");
		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/trunkfile"             , TypeEnv::STAGING, "/my/www/trunkfile");
		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/trunkfile"             , TypeEnv::PROD   , "/my/work/var/prod3/sources/www/trunkfile");

		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/path/to/.htaccess"     , TypeEnv::DEV    , "/my/www/path/to/.htaccess");
		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/path/to/.htaccess"     , TypeEnv::STAGING, "/my/www/path/to/.htaccess");
		$test_sets[] = array(TypeRepository::FHIBOX1, "trunk/path/to/.htaccess"     , TypeEnv::PROD   , "/my/work/var/prod3/sources/www/path/to/.htaccess");

		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/file"              , TypeEnv::DEV    , "/my/www/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/file"              , TypeEnv::STAGING, "/my/www/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/file"              , TypeEnv::PROD   , "/my/work/var/prod3/sources/www/file");

		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/trunkfile"         , TypeEnv::DEV    , "/my/www/trunkfile");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/trunkfile"         , TypeEnv::STAGING, "/my/www/trunkfile");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/trunkfile"         , TypeEnv::PROD   , "/my/work/var/prod3/sources/www/trunkfile");

		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/path/to/.htaccess" , TypeEnv::DEV    , "/my/www/path/to/.htaccess");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/path/to/.htaccess" , TypeEnv::STAGING, "/my/www/path/to/.htaccess");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/www/path/to/.htaccess" , TypeEnv::PROD   , "/my/work/var/prod3/sources/www/path/to/.htaccess");

		$test_sets[] = array(TypeRepository::FHIBOX1, "conf/file"                   , TypeEnv::DEV    , "/my/conf/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "conf/file"                   , TypeEnv::STAGING, "/my/conf/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "conf/file"                   , TypeEnv::PROD   , "/my/work/var/prod3/sources/conf/file");

		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/conf/file"             , TypeEnv::DEV    , "/my/conf/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/conf/file"             , TypeEnv::STAGING, "/my/conf/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/conf/file"             , TypeEnv::PROD   , "/my/work/var/prod3/sources/conf/file");

		$test_sets[] = array(TypeRepository::FHIBOX1, "logs/file"                   , TypeEnv::DEV    , "/my/logs/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "logs/file"                   , TypeEnv::STAGING, "/my/logs/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "logs/file"                   , TypeEnv::PROD   , "/my/work/var/prod3/sources/logs/file");

		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/logs/file"             , TypeEnv::DEV    , "/my/logs/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/logs/file"             , TypeEnv::STAGING, "/my/logs/file");
		$test_sets[] = array(TypeRepository::FHIBOX1, "/my/logs/file"             , TypeEnv::PROD   , "/my/work/var/prod3/sources/logs/file");


		foreach($test_sets as $test_set)
		{ /*        */	self::$logger->debug("Testing $test_set[0], $test_set[1], $test_set[2], expected $test_set[3]");
			$this->assertTrue(ArgumentSourceFile::translate($test_set[0], $test_set[1], $test_set[2]) == $test_set[3], "Failed on $test_set[0], $test_set[1], $test_set[2], expected $test_set[3]");
		}

		$fl                   = FileLocatorBuilder::build(TypeRepository::from($rep), $target_files[0], TypeEnv::from($env) );
	}
}

