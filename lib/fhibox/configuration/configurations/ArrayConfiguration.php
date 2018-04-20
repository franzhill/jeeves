<?php


namespace fhibox\configuration\configurations;

use fhibox\configuration\IConfiguration;

class ArrayConfiguration implements IConfiguration
{
	/**
	 * The inner representation of the configuration
	 * @var array of key => value
	 */
	private $arr;

	public function __construct($arr)
	{ $this->arr = $arr;
	}

	public function get($key)
	{
		if  (array_key_exists($key, $this->arr ))
		{	return $this->arr[$key];
		}
		else
		{ return null;
		}
	}

	/**
	 * Mostly for tests...
	 */
	public function getInnerArray()
	{ return $this->arr;
	}
}
