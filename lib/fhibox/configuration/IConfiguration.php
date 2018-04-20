<?php

namespace fhibox\configuration;


interface IConfiguration
{
	/**
	 * Return value for given key
	 * @param $key
	 * @return mixed Value stored at given key, or null if key is not found
	 */
	public function get($key);
} 