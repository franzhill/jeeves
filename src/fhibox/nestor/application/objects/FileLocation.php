<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 02/12/201x
 * Time: 17:25
 */

namespace fhibox\nestor\application\objects;

/**
 * A container class
 * Meant to contain the result of FileLocator
 * @author fhi
 */
class FileLocation
{
	/**
	 * See constructor
	 * @var
	 */
	private $hosts;

	/**
	 * See constructor
	 * @var
	 */
	private $path;

	/**
	 * See constructor
	 * @var
	 */
	private $basename;



	/**
	 * @param $hosts    mixed   string or string[] Hostname(s) of server(s) where file physically resides.
	 *                          There should be only one host in mono-host environements (dev, staging, front)
	 *                          and several in multi-host environments (prod)
	 * @param $path     string  Full path of file on host
	 * @param $basename string  The basename of the file, all leading common paths stripped off
	 *                          May be empty. See FileLocator*
	 */
	public function __construct($hosts, $path, $basename)
	{
		$this->hosts    = (! is_array($hosts)) ? array($hosts) : $hosts;
		$this->path     = $path;
		$this->basename = $basename;
	}


	public function getHosts()
	{	return $this->hosts;
	}

	public function getPath()
	{	return $this->path;
	}


	public function getBasename()
	{	return $this->basename;
	}



}