<?php

spl_autoload_register
(
	/**
	 * Must transform a class name such as:
	 *   fhibox\<lib>\<folder>\Class
	 * into the following file path:
	 *   <path_to_parent_dir>/fhibox/<lib>/<folder>/Class.php
	 *
	 * @author fhi
	 * @since 2014
	 */
	function ($classname)
	{
		// Path to directory containing the first level/directory of the namespacing
		// In our case, path to dir containing 'fhibox', since namespacing is done as
		// such : namespace fhibox\jeeves\...
		$PATH_TO_LIB_ROOT = __DIR__  . '/..';

		/*        */  #$auto_load_id="autoload jeeves";
		/*        */  #echo "$auto_load_id 1: classname=$classname\n";

		$isSuccessful = false;
		$filename     = str_replace('\\', DIRECTORY_SEPARATOR, $classname);

		/*        */  #echo "$auto_load_id 2 : filename=$filename\n";
		/*        */  #echo "$auto_load_id 3 : explode(DIRECTORY_SEPARATOR, filename)=".print_r(explode(DIRECTORY_SEPARATOR, $filename), true)."\n";
		/*        */  #echo "$auto_load_id 4 : array_slice(explode(DIRECTORY_SEPARATOR, filename), 1)=".print_r(array_slice(explode(DIRECTORY_SEPARATOR, $filename), 1), true)."\n";

		$filename     = $PATH_TO_LIB_ROOT . '/' . $filename . '.php';
		/*        */  #echo "$auto_load_id 5 : filename=$filename\n";

		// Si classe existe, on l'inclut
		if (is_file($filename))
		{
			require_once $filename;
			$isSuccessful = true;
		}
		return $isSuccessful;
	}
);
