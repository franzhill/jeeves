<?php

namespace fhibox\arrays;


/**
 *
 * @author Francois hill
 */
class ArrayLib
{

	/**
	 * Courtesy StackOverflow
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function array_merge_recursive(array & $array1, array & $array2)
	{
		$merged = $array1;

		foreach ($array2 as $key => & $value)
		{
			if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
			{
				$merged[$key] = self::array_merge_recursive($merged[$key], $value);
			} else if (is_numeric($key))
			{
				if (!in_array($value, $merged))
					$merged[] = $value;
			} else
				$merged[$key] = $value;
		}

		return $merged;
	}




}