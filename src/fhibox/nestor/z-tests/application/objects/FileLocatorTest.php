<?php


#namespace fhibox\nestor\z-tests\application\objects;

use fhibox\nestor\application\objects\FileLocatorBuilder;
use fhibox\nestor\application\values\TypeEnv;
use fhibox\nestor\application\values\TypeRepository;
use fhibox\nestor\application\values\TypeLocation;

# Using the  following:
#require_once __DIR__.'/../../../bootstrap.php';
# generates the following error:
# Fatal error: Call to undefined method PHPUnit_Framework_TestResult::beStrictAboutTestsThatDoNotTestAnything() in phar://C:/Users/Francois/Documents/CODE/FRONT/my/work/scripts_2/phpunit.phar/phpunit/TextUI/TestRunner.php on line 411
#=> use the --bootstrap option witb phpunit.phar

/**
 * To launch this test :
 *
 * Francois hill@Fhibox-PC-2 /cygdrive/c/Users/Francois hill/Documents/CODE/FRONT/my/work/scripts_2
 * $ php lib/vendor/phpunit/phpunit/phpunit.php --bootstrap src/fhibox/nestor/bootstrap.php src/fhibox/nestor/z-tests/application/objects/FileLocatorTest.php
 * $ php lib/phpunit.phar --bootstrap src/fhibox/nestor/bootstrap.php src/fhibox/nestor/z-tests/application/objects/FileLocatorTest.php
 *
 * @author fhi
 * @since 12/12/2014
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


	function testFileLocators()
	{
		$reps = array(TypeRepository::FHIBOX1, TypeRepository::ADMIN, TypeRepository::CONSTANTS);
		foreach ($reps as $rep)
		{  /*        */
			self::$logger->debug("Testing rep [$rep]");
			$test_sets = $this->getOkTestSetRep($rep);


			foreach ($test_sets as $test_set)  //@formatter:off
			{ /*        */	self::$logger->debug("Testing $test_set[0], $test_set[1], $test_set[2], $test_set[3]");

				$fl = FileLocatorBuilder::build($rep, $test_set[0], $test_set[1]);

				$path_in_server      = $fl->locate(TypeLocation::SERVER)     ->getPath();
				$path_in_vcs_workdir = $fl->locate(TypeLocation::VCS_WORKDIR)->getPath();   #$sf->getRealPathInVcsWorkDir();
				#$path_to_sync_to      = $fl->locate(TypeLocation::SYNC_DIR)     ->getPath();   #$sf->getRealPath();
				$path_basename       = $fl->locate(TypeLocation::SERVER)     ->getBasename();

				$this->assertTrue($path_in_server      == $test_set[2], "Failed for target file path=[$test_set[0]], for [path_in_server]       : got : [$path_in_server]     , expected : [$test_set[2]]");
				$this->assertTrue($path_in_vcs_workdir == $test_set[3], "Failed for target file path=[$test_set[0]], for [path_in_vcs_workdir]  : got : [$path_in_vcs_workdir], expected : [$test_set[3]]");
				$this->assertTrue($path_basename       == $test_set[5], "Failed for target file path=[$test_set[0]], for [basename]             : got : [$path_basename]      , expected : [$test_set[5]]");
			} //@formatter:on
		}
	}


	function getOkTestSetRep($rep)
	{
		switch ($rep)  //@formatter:off
		{	case TypeRepository::FHIBOX1  : return $this->getOkTestSetRepFhibox1();
		 	case TypeRepository::ADMIN    : return $this->getOkTestSetRepAdmin  ();
			case TypeRepository::CONSTANTS: return $this->getOkTestSetRepConstants();
			default : throw new Exception("Unknown rep [$rep].");
		}   //@formatter:on
	}


	/**
	 * These test cases should fall into the 'working' cases and should not generate exceptions or errors
	 */
	function getOkTestSetRepFhibox1()
	{	$test_sets=array();

		// $test_sets[] = array(target file path given (as argument), env, expected path in server, expected path in vcs workdir, expected path in mirror dir, expected expected result, expected basename)

		$test_sets[] = array("trunk/file"                  , TypeEnv::DEV    , "/my/www/file", "/my/repos/fhibox1/trunk/file", null, "file");
		$test_sets[] = array("trunk/file"                  , TypeEnv::STAGING, "/my/www/file", "/my/repos/fhibox1/trunk/file", null, "file");
		$test_sets[] = array("trunk/file"                  , TypeEnv::PROD   , "/my/www/file", "/my/repos/prod3/fhibox1/trunk/file", "/my/work/var/prod3/sources/www/file", "file");

		$test_sets[] = array("trunk/trunkfile"             , TypeEnv::DEV    , "/my/www/trunkfile"  , "/my/repos/fhibox1/trunk/trunkfile", null, "trunkfile");
		$test_sets[] = array("trunk/trunkfile"             , TypeEnv::STAGING, "/my/www/trunkfile"  , "/my/repos/fhibox1/trunk/trunkfile", null, "trunkfile");
		$test_sets[] = array("trunk/trunkfile"             , TypeEnv::PROD   , "/my/www/trunkfile"  , "/my/repos/prod3/fhibox1/trunk/trunkfile", "/my/work/var/prod3/sources/www/trunkfile", "trunkfile");

		$test_sets[] = array("trunk/path/to/.htaccess"     , TypeEnv::DEV    , "/my/www/path/to/.htaccess" , "/my/repos/fhibox1/trunk/path/to/.htaccess", null, "path/to/.htaccess");
		$test_sets[] = array("trunk/path/to/.htaccess"     , TypeEnv::STAGING, "/my/www/path/to/.htaccess" , "/my/repos/fhibox1/trunk/path/to/.htaccess", null, "path/to/.htaccess");
		$test_sets[] = array("trunk/path/to/.htaccess"     , TypeEnv::PROD   , "/my/www/path/to/.htaccess" , "/my/repos/prod3/fhibox1/trunk/path/to/.htaccess", "/my/work/var/prod3/sources/www/path/to/.htaccess", "path/to/.htaccess");

		$test_sets[] = array("conf/file"                   , TypeEnv::DEV    , "/my/conf/file"      , "/my/repos/fhibox1/conf/file"      , null                                          , "file");
		$test_sets[] = array("conf/file"                   , TypeEnv::STAGING, "/my/conf/file"      , "/my/repos/fhibox1/conf/file"      , null                                          , "file");
		$test_sets[] = array("conf/file"                   , TypeEnv::PROD   , "/my/conf/file"      , "/my/repos/prod3/fhibox1/conf/file", "/my/work/var/prod3/sources/conf/file"    , "file");

		$test_sets[] = array("/my/conf/file"             , TypeEnv::DEV    , "/my/conf/file"      , "/my/repos/fhibox1/conf/file"      , null                                          , "file");
		$test_sets[] = array("/my/conf/file"             , TypeEnv::STAGING, "/my/conf/file"      , "/my/repos/fhibox1/conf/file"      , null                                          , "file");
		$test_sets[] = array("/my/conf/file"             , TypeEnv::PROD   , "/my/conf/file"      , "/my/repos/prod3/fhibox1/conf/file", "/my/work/var/prod3/sources/conf/file"    , "file");

		$test_sets[] = array("logs/file"                   , TypeEnv::DEV    , "/my/logs/file"      , "/my/repos/fhibox1/logs/file"      , null                                          , "file");
		$test_sets[] = array("logs/file"                   , TypeEnv::STAGING, "/my/logs/file"      , "/my/repos/fhibox1/logs/file"      , null                                          , "file");
		$test_sets[] = array("logs/file"                   , TypeEnv::PROD   , "/my/logs/file"      , "/my/repos/prod3/fhibox1/logs/file", "/my/work/var/prod3/sources/logs/file"    , "file");

		$test_sets[] = array("/my/logs/file"             , TypeEnv::DEV    , "/my/logs/file"      , "/my/repos/fhibox1/logs/file"      , null                                          , "file");
		$test_sets[] = array("/my/logs/file"             , TypeEnv::STAGING, "/my/logs/file"      , "/my/repos/fhibox1/logs/file"      , null                                          , "file");
		$test_sets[] = array("/my/logs/file"             , TypeEnv::PROD   , "/my/logs/file"      , "/my/repos/prod3/fhibox1/logs/file", "/my/work/var/prod3/sources/logs/file"    , "file");

		return $test_sets;
	}



	function getOkTestSetRepAdmin()
	{
		$test_sets = array();

		// $test_sets[] = array(target file path given (as argument), env, expected path in server, expected path in vcs workdir, expected path in mirror dir, expected expected result, expected basename)

		$test_sets[] = array("file"          , TypeEnv::DEV    , "/my/bin/file", "/my/repos/admin/file", null, "file");
		$test_sets[] = array("file"          , TypeEnv::STAGING, "/my/bin/file", "/my/repos/admin/file", null, "file");
		$test_sets[] = array("file"          , TypeEnv::PROD   , "/my/bin/file", "/my/repos/admin/file", "/my/work/var/prod3/sources/bin/file", "file");

		$test_sets[] = array("/my/bin/file", TypeEnv::DEV    , "/my/bin/file", "/my/repos/admin/file", null, "file");
		$test_sets[] = array("/my/bin/file", TypeEnv::STAGING, "/my/bin/file", "/my/repos/admin/file", null, "file");
		$test_sets[] = array("/my/bin/file", TypeEnv::PROD   , "/my/bin/file", "/my/repos/admin/file", "/my/work/var/prod3/sources/bin/file", "file");

		$test_sets[] = array("slurps/certifications/certif12345", TypeEnv::DEV    , "/my/bin/slurps/certifications/certif12345", "/my/repos/admin/slurps/certifications/certif12345", null, "slurps/certifications/certif12345");
		$test_sets[] = array("slurps/certifications/certif12345", TypeEnv::STAGING, "/my/bin/slurps/certifications/certif12345", "/my/repos/admin/slurps/certifications/certif12345", null, "slurps/certifications/certif12345");
		$test_sets[] = array("slurps/certifications/certif12345", TypeEnv::PROD   , "/my/bin/slurps/certifications/certif12345", "/my/repos/admin/slurps/certifications/certif12345", "/my/work/var/prod3/sources/bin/slurps/certifications/certif12345", "slurps/certifications/certif12345");

		$test_sets[] = array("/my/bin/slurps/certifications/certif12345", TypeEnv::DEV    , "/my/bin/slurps/certifications/certif12345", "/my/repos/admin/slurps/certifications/certif12345", null, "slurps/certifications/certif12345");
		$test_sets[] = array("/my/bin/slurps/certifications/certif12345", TypeEnv::STAGING, "/my/bin/slurps/certifications/certif12345", "/my/repos/admin/slurps/certifications/certif12345", null, "slurps/certifications/certif12345");
		$test_sets[] = array("/my/bin/slurps/certifications/certif12345", TypeEnv::PROD   , "/my/bin/slurps/certifications/certif12345", "/my/repos/admin/slurps/certifications/certif12345", "/my/work/var/prod3/sources/bin/slurps/certifications/certif12345", "slurps/certifications/certif12345");

		return $test_sets;
	}


	function getOkTestSetRepConstants()
	{
		$test_sets = array();

		// $test_sets[] = array(target file path given (as argument), env, expected path in server, expected path in vcs workdir, expected path in mirror dir, expected expected result, expected basename)

		$test_sets[] = array("file", TypeEnv::DEV    , "/my/config/const/file", "/my/repos/const/dev/file"    , null, "file");
		$test_sets[] = array("file", TypeEnv::STAGING, "/my/config/const/file", "/my/repos/const/staging/file", null, "file");
		$test_sets[] = array("file", TypeEnv::PROD   , "/my/config/const/file", "/my/repos/const/prod/file"   , "/my/work/var/prod3/sources/config/php/constants/file", "file");

		return $test_sets;
	}
}

