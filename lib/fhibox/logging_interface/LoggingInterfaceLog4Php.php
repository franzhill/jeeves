<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 20/10/201x
 * Time: 12:01
 */

namespace fhibox\logging_interface;


class LoggingInterfaceLog4Php implements ILoggingInterface
{
	public function configure($conf_file_path)
	{
		// Assume Log4Php is loaded
		// This is done so :
		//   require_once PROJECT_LIB_PATH . '/vendor/apache/log4php/src/main/php/Logger.php';
		// Log4php is not loaded here because we don't really know here
		// what the exact path to it is.
		// So it's best that it be included from the project level.

		\Logger::configure($conf_file_path);
	}


	public function setLevel($level)
	{
		switch($level)
		{ case ILoggingInterface::LEVEL_OFF   : $level = \LoggerLevel::getLevelOff  (); break;
			case ILoggingInterface::LEVEL_FATAL : $level = \LoggerLevel::getLevelFatal(); break;
			case ILoggingInterface::LEVEL_ERROR : $level = \LoggerLevel::getLevelError(); break;
			case ILoggingInterface::LEVEL_WARN  : $level = \LoggerLevel::getLevelWarn (); break;
			case ILoggingInterface::LEVEL_INFO  : $level = \LoggerLevel::getLevelInfo (); break;
			case ILoggingInterface::LEVEL_DEBUG : $level = \LoggerLevel::getLevelDebug(); break;
			case ILoggingInterface::LEVEL_TRACE : $level = \LoggerLevel::getLevelTrace(); break;
			case ILoggingInterface::LEVEL_ALL   : $level = \LoggerLevel::getLevelAll  (); break;
			default                            : $level = \LoggerLevel::getLevelInfo ();
		}
		\Logger::getRootLogger()->setLevel($level);
	}


	public function getLogger($identifier)
	{
		return \Logger::getLogger($identifier);
	}

} 