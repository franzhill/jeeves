<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 20/10/201x
 * Time: 11:56
 */

namespace fhibox\logging_interface;

/**
 * Interface ILoggingInterface
 *
 * This is a logging interface, to abstract the actual logging solution
 * used (log4php, monolog...)
 *
 * Use the functions provided by this ILoggingInterface in the code, and then
 * it will be possible to change the actual real logging solution
 * without touching the core of the code.
 *
 * At "bootstrap time", chose the implementing logging solution to use
 * E.g. for a Log4php:
 * <pre>
 *   require_once PROJECT_LIB_PATH . '/vendor/apache/log4php/src/main/php/Logger.php';
 *   $ilogging = new LoggingInterfaceLog4Php();
 *   $ilogging -> configure(PROJECT_CONF_PATH.'/log4php.conf.ini');
 * </pre>
 * and then in the code something like:
 * <pre>
 *    $this->logger = $ilogging->getLogger(__CLASS__);
 *    $this->logger->debug("This is a debug message");
 * </pre>
 *
 * @package fhibox\logging_interface
 * @author fhi
 */
interface ILoggingInterface
{
	const LEVEL_OFF   = 2147483647;
	const LEVEL_FATAL = 50000;
	const LEVEL_ERROR = 40000;
	const LEVEL_WARN  = 30000;
	const LEVEL_INFO  = 20000;
	const LEVEL_DEBUG = 10000;
	const LEVEL_TRACE = 5000;
	const LEVEL_ALL   = -2147483647;


	public function configure($conf_file_path);

	/**
	 * Set this general logging solution's minimum logging level
	 * @param $level int One of the LEVEL_* constants.
	 *                   Warning : all levels might not be supported by
	 *                   the actual implementing class.
	 * @return mixed
	 */
	public function setLevel($level);

	/**
	 * Ask the logging solution to return a specific logger
	 *
	 * The logging solution may indeed harbour several different loggers
	 * (each of them with different settings).
	 *
	 * @param $identifier string Identifier of the logger to return
	 * @return mixed A logger object; actual class of that object may vary
	 *               but it should expose the "standard" logging functions
	 *               debug(), warn(), info() ...
	 *               Due to the dynamic typing nature of PHP there is no need expose
	 *               the standard logging functions
	 *                 debug(), warn() ...
	 *               in an interface abstracting all possible types of loger objects.
	 *               (provided that these logger objects do provide the standard logging
	 *               functions).
	 *               So we don't have to create that interface.
	 *               Downside of this is we won't benefit from IDE (PHPStorm) type hinting/help
	 *               functionalities.
	 *
	 */
	public function getLogger($identifier);
} 