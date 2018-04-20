<?php

namespace fhibox\nestor\application\values;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
use fhibox\enum\BaseEnum;

/**
 * Description of Environment
 *
 * @author Francois hill
 */
class TypeEnv extends BaseEnum
{
	const LOCAL   = "LOCAL";
	const DEV     = "DEV";
	const STAGING = "STAGING";
	const FRONT   = "FRONT";
	const PROD    = "PROD";
	const PROD1   = "PROD1";
	const PROD2   = "PROD2";
	const PROD3   = "PROD3";


	/**
	 * Returns the corresponding hostname
	 *
	 * @var string $type_env One of the possible values of TypeModule
	 * @example toUrl(Environment::DEV)
	 * @return string
	 */
	public static function toHostName($type_env)
	{	switch ($type_env)
		{	case self::DEV      : return "dev";
			case self::STAGING  : return "staging";
			case self::PROD     : return "prod";
			case self::FRONT    : return "front";
			#case self::LOCAL    : return "local";
			default             : return "UNKNOWN";
		}
	}



}



