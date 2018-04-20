<?php

namespace fhibox\filesystem;

/**
 * Description of FileSystemLib
 *
 * @author Francois hill
 */
class FileSystemLib
{
	static function isFolder($path)
	{

	}

	/**
	 * Converts \ to /
	 * @param $path
	 */
	static public function normalisePath($path)
	{
		return str_replace('\\', '/', $path);
	}

}
