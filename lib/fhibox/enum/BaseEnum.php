<?php

namespace fhibox\enum;

/**
 * Description of BaseEnum
 *
 * @author Francois hill courtesy of http://stackoverflow.com/questions/254514/php-and-enumerations
 * => !!!
 */
abstract class BaseEnum
{
	private static function getConstants()
	{
		$reflect = new \ReflectionClass(get_called_class());
		return $reflect->getConstants();
	}

	/**
	 * Check if the given name corresponds to a valid name of the Enum.
	 *
	 * A name of the Enum is a name of one of the Enum's constants (left in the below example)
	 * A value of the Enum is a value of one of the Enum's constants (right in the below example)
	 * <pre>
	 * class TypeEnv extends BaseEnum
	 * {
	 *  const DEV     = "DEV";
	 * </pre>
	 *
	 *
	 * @param $name
	 * @param bool $strict  if true will require case to be identical. If not,
	 *                      case-insensitive matching is used.
	 * @return bool
	 */
	public static function isValidName($name, $strict = true)
	{
		$constants = self::getConstants();

		if ($strict)
		{	return array_key_exists($name, $constants);
		}

		$keys = array_map('strtolower', array_keys($constants));
		return in_array(strtolower($name), $keys);
	}

	/**
	 * Check if the given value corresponds to a valid value of the Enum.
	 *
	 * @param $value
	 * @param bool $strict  if true will require case to be identical. If not,
	 *                      case-insensitive matching is used.
	 * @return bool
	 */
	public static function isValidValue($value, $strict = true)
	{
	    $values = array_values(self::getConstants());
	    return in_array($value, $values, $strict);
	}




	/**
	 * Check if the given value is the given type value
	 *
	 * @example is($value, TypeEnv::PROD)        will return true if $value = PROD and TypeEnv::PROD = PROD
	 * @example is($value, TypeEnv::PROD, true)  will return true if $value = prod and TypeEnv::PROD = PROD
	 *
	 * @param $value
	 * @param $type_value
	 * @param bool $strict  if true will require case to be identical. If not,
	 *                      case-insensitive matching is used.
	 * @return bool|\InvalidArgumentException
	 */
	public static function is($value, $type_value, $strict = false)
	{
		// First, check that the type we're trying to check against is indeed a valid type
		if (!self::isValidValue($type_value, $strict)) {
			return new \InvalidArgumentException("Asking if [$value] is a [$type_value], while [$type_value] is not of current type [TODO]");
		}

		if ($strict)
		{	return $value == $type_value;
		}
		else
		{	return strtolower($value) == strtolower($type_value);
		}
	}

	/**
	 * Return the real type value, corresponding to the given value.
	 * This takes care of typecase problems.
	 *
	 * If the value corresponds to no real type, raise exception.
	 *
	 * @param $value
	 * @param bool $strict if true will require case to be identical. If not,
	 *                      case-insensitive matching is used.
	 * @return mixed The real type value as defined in the type constant
	 */
	public static function getTypeValue($value, $strict = false)
	{
		$type_values = array_values(self::getConstants());
		foreach ($type_values as $type_value)
		{ $type_value_bckp = $type_value;
			if (! $strict) {$type_value = strtolower($type_value); $value = strtolower($value); }
			if ($type_value == $value)
			{ return $type_value_bckp;
			}
		}
		throw new \RuntimeException("Value given [$value] corresponds to no value of type [". get_called_class() . "]");
	}


	/**
	 * @alias getTypeValue
	 */
	public static function  from($value, $strict = false)
	{
		return self::getTypeValue($value, $strict);
	}


}

