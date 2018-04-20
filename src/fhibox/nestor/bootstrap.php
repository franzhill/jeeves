<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 19/10/201x
 * Time: 11:51
 */

use fhibox\logging_interface\ILoggingInterface;
use fhibox\logging_interface\LoggingInterfaceLog4Php;

define ('PROJECT_ROOT_PATH', __DIR__.'/../../..');
define ('PROJECT_CONF_PATH', PROJECT_ROOT_PATH . '/conf');
define ('PROJECT_LIB_PATH' , PROJECT_ROOT_PATH . '/lib');
define ('PROJECT_SRC_PATH' , PROJECT_ROOT_PATH . '/src');

require_once PROJECT_LIB_PATH . '/vendor/autoload.php';
require_once PROJECT_LIB_PATH . '/fhibox/autoload.php';
require_once PROJECT_SRC_PATH . '/fhibox/nestor/autoload.php';

// We'll be using Log4php as logging solution => import it
require_once PROJECT_LIB_PATH . '/vendor/apache/log4php/src/main/php/Logger.php';


#class Project
#{
#	/**
#	 * @var ILoggingInterface $loggingSolution
#	 */
#	public static $loggingSolution;
#
#
#	public static function setup()
#	{
#		// Load and configure logging solution
#		// Here we will chose to use Log4Php (this could be changed for another logging solution, e.g. Monolog)
#		// TODO we could even set up a object container here (use the one from Symfony) instead of statically accessing the objects like that.
#
#		self::$loggingSolution = new LoggingInterfaceLog4Php();
#		self::$loggingSolution->configure(PROJECT_CONF_PATH . '/log4php.conf.ini');
#	}
#
#}
#
#Project::setup();