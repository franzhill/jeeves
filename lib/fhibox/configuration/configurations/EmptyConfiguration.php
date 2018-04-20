<?php


namespace fhibox\configuration\configurations;

use fhibox\configuration\IConfiguration;

class EmptyConfiguration implements IConfiguration
{

	/**
	 * This config being null, always return null
	 */
	public function get($key)
	{
		return null;
	}

}
