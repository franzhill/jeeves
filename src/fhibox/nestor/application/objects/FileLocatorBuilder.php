<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 04/12/201x
 * Time: 09:53
 */

namespace fhibox\nestor\application\objects;


use fhibox\nestor\application\values\TypeRepository;

class FileLocatorBuilder
{

	/**
	 * @param $rep    string  The vcs repository (after option/argument processing) in which the file is versioned.
	 *                        One of the values of TypeRepository.
	 * @param $path   string  The user-provided path for the file (after option/argument processing)
	 * @param $env    string  The user-provided target environment for the file (after option/argument processing).
	 *                        One of the values of TypeEnv.
	 * @return FileLocator
	 */
	static public function build($rep, $path, $env = null)
	{
		$rep  = TypeRepository::from($rep);   // will throw exception if no good
		switch ($rep)
		{
			case  TypeRepository::FHIBOX1   : $ret = new FileLocatorFhiBox1   ($path, $env); break;
			case  TypeRepository::ADMIN     : $ret = new FileLocatorAdmin     ($path, $env); break;
			case  TypeRepository::CONSTANTS : $ret = new FileLocatorConstants ($path, $env); break;
			default                         : throw new \RuntimeException("Repository VCS unknown : [$rep]");
		}
		return $ret;
	}
} 