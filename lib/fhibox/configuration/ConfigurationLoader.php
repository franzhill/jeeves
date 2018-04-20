<?php

namespace fhibox\configuration;

use fhibox\arrays\ArrayLib;
use fhibox\configuration\configurations\ArrayConfiguration;
use fhibox\configuration\configurations\EmptyConfiguration;
use fhibox\configuration\exceptions\ConfigurationException;
use fhibox\filesystem\FileSystemLib;
use fhibox\logging_interface\ILoggingInterface;
use fhibox\string\StringLib;

/**
 * Loads a configuration
 *
 * Generally a configuration is a file of settings (e.g. a ini file)
 * for a given concern (e.g. for a file or a class).
 * E.g. :
 * Class = MyObject.php
 * Conf file = /path/to/conf/dir/MyObject.ini
 *
 * The config loader loads the config and makes it available in a simple way.
 *
 * @todo make more versatile. FOr the moment, only able to load ini conf files
 * @package fhibox\jeeves\tools\configuration
 */
class ConfigurationLoader //implements IConfLoader @todo
{
	const  PLACEHOLDER_DELIMITER = "%";

	/**
	 * Full path to configuration directory
	 * @var
	 */
	private $confDirAbsPath;

	private $confFileExtension;

// ------------------------------------------------------------------------------
// <LOGGING>

	/**
	 * @var mixed  Logger object, see setter.
	 */
	private $logger;

	/**
	 * @var ILoggingInterface   See setter.
	 */
	private $logging;

	/**
	 * Use this when you want to assign a logger to this class.
	 * If no logger is assigned to this class, it will not output any logs.
	 *
	 * @param $logger mixed A logger object that should at least provide the standard logging functions
	 *                (debug(), info(), warn() etc.)
	 */
	public function setLogger($logger)
	{ $this->logger = $logger;
	}

	/**
	 * Use this instead of setLogger() when you want this class to setup its own
	 * logger, based on a logging solution interface.
	 * The logger assigned to this class will then be based on this class' name.
	 * @param ILoggingInterface $log_i
	 * @return $this
	 */
	public function setLogging(ILoggingInterface $log_i)
	{
		$this->logging = $log_i;
		$this->logger  = $this->logging->getLogger(str_replace('\\', '.', __CLASS__));
		return $this;
	}

// </LOGGING>
// ------------------------------------------------------------------------------



	/**
	 * @param $conf_dir_abs_path string Absolute path of configuration directory where configuration
	 *                                  files are placed.
	 * @param string $conf_file_extension
	 */
	public function __construct($conf_dir_abs_path, $conf_file_extension = 'ini')
	{
		$this->confDirAbsPath    = $conf_dir_abs_path;
		$this->confFileExtension = $conf_file_extension;
	}


	/**
	 * Load configuration for given class
	 * Name of conf file is of type <name_of_class>.ini
	 *
	 * The conf file for the class should be placed in the configuration directory set
	 * in the conf loader constructor, and in the same directory hierarchy as the class.
	 * E.g. if class is source/Animals/Mammals/Human/Kim.php
	 *  then conf file is expected to be at:
	 *                  conf/Animals/Mammals/Human/Kim.ini
	 *
	 * Inheritance.
	 * The configuration loader supports class inheritance
	 * i.e. if source/Animals/Mammals/Human/Offspring/North.php extends source/Animals/Mammals/Human/Offspring/Kim.php
	 * then conf file
	 * conf/Animals/Mammals/Human/Offspring/North.ini will inherit conf/Animals/Mammals/Human/Kim.ini
	 * i.e. all configurations defined in Kim.ini will show in the result configuration of North.ini,
	 * and it is also possible to redefine (overload) these configurations in North.ini.
	 *
	 *
	 *
	 * What happens if the conf file does not exist?
	 * The decision is always touch and go between raising an exception
	 * and behaving as smoothly as possible (e.g. return an empty configuration
	 * object that returns empty strings)
	 *
	 * This function also processes placeholders. See function processPlaceholdersStr()
	 * for more info. For the placeholders to be processed, pass an object and not the class name.
	 * Placeholder substitution relies on calling getters from the given object.
	 *
	 *
	 * @param mixed $obj Either object or full name of class( with namespace)
	 *                   for which we want to load the configuration file,
	 * e.g.
	 * $conf_loader->loadForClass('fhibox\jeeves\application\options\OptionTest')->get('name')
	 *
	 * @throws ConfigurationException if conf file not found or problem with it
	 * @return IConfiguration
	 */
	public function loadForClass($obj)
	{
		/**
		 * @var $class_name string full qualified name of class ex.  fhibox\jeeves\application\commands\deploy\CommandDeploy
		 */
		$class_name = (is_string($obj)) ? $obj : get_class($obj);
		/*        */   isset($this->logger) && $this->logger->debug("class_name=$class_name");

		// Read conf file for current class:
		$arr_conf = $this->getArrConf($class_name);
		// ... and process placeholders if applicable
		if (!is_string($obj))
		{	$arr_conf = $this->processPlaceholders($arr_conf, $obj);
		}

		/*        */   isset($this->logger) && $this->logger->debug("arr_conf=". print_r($arr_conf, true));

		// If class has parent classes, read their confs and merge them into child conf,
		// with lower precedence (child conf "redefines" parent conf)
		$class_parents = $this->getParents($class_name);
		/*        */	 isset($this->logger) && $this->logger->debug("parents = " . print_r($class_parents, true));


		foreach ($class_parents as $parent_class)
		{ // Read conf file for current class:
			$arr_conf_parent = $this->getArrConf($parent_class);
			// ... and process placeholders if applicable
			if (!is_string($obj))
			{	$arr_conf_parent = $this->processPlaceholders($arr_conf_parent, $obj);
			}

			// Merge in already computed conf, with lower precedence:
			#$arr_conf = array_merge($arr_conf_parent, $arr_conf);
			$arr_conf = ArrayLib::array_merge_recursive($arr_conf_parent, $arr_conf);

			#// Remove new lines from values (Experimental): (Trying to fix the way help looks)
			#array_walk_recursive($arr_conf, function(&$i, $k){ $i=StringLib::removeNewlines($i);});

		}
		/*        */   isset($this->logger) && $this->logger->debug("merged conf array=" . print_r($arr_conf, true));



		// If array is empty, return an EmptyConfiguration
		if (empty($arr_conf))
		{	return new EmptyConfiguration();
		}
		else
		{	// @todo polymorphism Return type should be configurable
			return new ArrayConfiguration($arr_conf);
		}

	}


	/**
	 * @param $class_name
	 * @return array Empty if no conf file found for given class
	 */
	private function getArrConf($class_name)
	{
		$conf_file_path = FileSystemLib::normalisePath
		                     ($this->confDirAbsPath . '/'.
		                      $class_name . ($this->confFileExtension[0] == '.' ? '' : '.' ) .
		                      $this->confFileExtension);

		/*        */  isset($this->logger) && $this->logger->debug("conf_file_path=$conf_file_path");
		/*        */  if (! file_exists($conf_file_path))
		/*        */  { #throw new ConfigurationException("The expected configuration file: <$conf_file_path> was not found.");
		/*        */    isset($this->logger) && $this->logger->debug("conf_file_path NOT found ! => returning empty array");
		/*        */    return array();
		/*        */  }
		/*        */    isset($this->logger) && $this->logger->debug("conf_file_path found ! => loading conf");

		$arr            = parse_ini_file($conf_file_path);

		/*        */  if ( $arr === FALSE)
		/*        */  { #throw new ConfigurationException("There was a problem while loading configuration file: <$conf_file_path> was not found.");
		/*        */    isset($this->logger) && $this->logger->debug("Pb while reading conf => returniong empty array");
		/*        */    return array();
		/*        */  }
		/*        */    isset($this->logger) && $this->logger->debug("returning arr=" . print_r($arr, true));
		return $arr;
	}


	/**
	 * Return the parent inheritance hierarchy for the given class/object
	 *
	 * E.g. if C -> B -> A  (A is the ancestor, C is the grandchild) then
	 * getParents(C) will return:
	 *  Array
	 *  (
	 *    [0] => B
	 *    [1] => A
	 *  )

	 * the following
	 * will be returned:

	 *
	 *
	 * @param mixed $obj Either object or full name of class( with namespace)
	 * @return array Empty if class has no parents
	 */
	private function getParents($obj)
	{
		/**
		 * @var $class_name string full qualified name of class ex.  fhibox\jeeves\application\commands\deploy\CommandDeploy
		 */
		$class_name  = (is_string($obj)) ? $obj : get_class($obj);
		$class       = new \ReflectionClass($class_name);
		$parents     = array();

		while ($parent = $class->getParentClass())
		{
			$parents[] = $parent->getName();
			$class     = $parent;
		}
		return $parents;
	}


	/**
	 * Replace the placeholders in an array resulting of a parse_ini_file
	 * Handles the mixed-type values, and calls processPlaceholdersStr() on just the strings.
	 * @param $arr
	 * @param $obj
	 * @return array Array of [strings/array of strings]
	 */
	private function processPlaceholders($arr, $obj)
	{
		$new_arr = array();
		foreach($arr as $key=>$value)
		{
			$new_arr[$key]=$this->processPlaceholdersStr($value, $obj);
		}
		return $new_arr;
		#if     (is_string($subj)) { return $this->processPlaceholdersStr($subj, $obj); }
		#elseif (is_array ($subj))
		#{
		#	# Attempt to use an anonymous function ...
		#	# thwarted by PHP 5.3 ... http://stackoverflow.com/questions/3605595/creating-and-invoking-an-anonymous-function-in-a-single-statement
		#	#$new_arr = array_map(
		#	#	function ($elem) use ($obj) {
		#	#		return $this->processPlaceholdersStr($elem, $obj);
		#	#	}
		#	#	, $subj);
		#	#return $new_arr;
		#	$new_arr = array();
		#	foreach($subj as $str)
		#	{ $new_arr[] = $this->processPlaceholdersStr($str, $obj);
		#	}
		#	return $new_arr;
		#}
		#else
		#{ throw new \Exception("Programming exception: type of subj not expected"); }
	}


	/**
	 * Will replace the placeholders contained in a string or array of strings
	 * with the corresponding values taken from the passed object <br />
	 * Example: <br />
	 * Given: <br />
	 * <pre>
	 *  $line = "The name is %name% and the surname is %surname% %%";
	 *  $obj = new Obj();
	 *  class Obj
	 *  {  public function getName()
	 *     {return "<obj name>";
	 *     }
	 *     public function getSurname()
	 *     {return "<obj name>";
	 *     }
	 *  }
	 * </pre>
	 * ,
	 * <pre>
	 * processPlaceholdersStr($string, $obj)
	 * </pre>
	 * will return:
	 * <pre>
	 * The name is <obj name> and the surname is <obj surname> %%
	 * </pre>
	 *
	 * @param mixed $string string or array of strings
	 * @param mixed $obj
	 * @return mixed string/array of strings with the placeholders replaced
	 */
	public function processPlaceholdersStr($string, $obj)
	{ $phd = self::PLACEHOLDER_DELIMITER;
		$newstring = preg_replace_callback(
			'/'.$phd.'(.+?)'.$phd.'/',  // ? is for non greedy
			function ($matches) use ($obj)
			{ // Memo $matches[0] is the whole matched pattern
				//      $match[i>0] is the caught subpattern number i
				// Call the getter from the obj:
				// TODO : is there a way to no call this function statically?
				return ConfigurationLoader::getAttribute($obj, $matches[1]);
				#return $obj->{"get".ucFirst($matches[1])}();
			},
			$string
		);
		return $newstring;
	}


	/**
	 * @accessability public and static because used as a callback above (see if can be done differently)
	 * @param $object
	 * @param $attribute
	 * @return mixed
	 */
	public static function getAttribute($object, $attribute)
	{
		// Test if getter exists:
		$getter = "get".ucFirst($attribute);
		if(is_callable(array($object, $getter)))
		{	return $object->{$getter}();
		}
		else
		{	// Else use reflection to get attribute:
			$reflect_class   = new \ReflectionClass($object);
			$reflect_prop    = $reflect_class->getProperty($attribute);
			$reflect_prop    -> setAccessible(true);
			return $reflect_prop    -> getValue($object);
		}
	}

}