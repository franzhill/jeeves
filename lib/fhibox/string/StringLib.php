<?php

namespace fhibox\string;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of stringLibrary
 *
 * @author Francois hill
 */
class StringLib
{
	static public function startsWith($haystack, $needle)
	{
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	static public function endsWith($haystack, $needle)
	{
			return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}

	/**
	 * @param $string
	 * @return string
	 */
	static public function removeNewlines($string)
	{
		return trim(preg_replace('/\s+/', ' ', $string));
	}


	static public function removeTrailing($str, $pattern)
	{
		if (substr($str,-strlen($pattern))===$pattern)
			$str = substr($str, 0, strlen($str)-strlen($pattern));
		return $str;
	}

}

