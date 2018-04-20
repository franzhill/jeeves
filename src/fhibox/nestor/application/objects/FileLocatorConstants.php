<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 03/12/201x
 * Time: 15:13
 */

namespace fhibox\nestor\application\objects;


use fhibox\nestor\application\values\TypeEnv;
use fhibox\nestor\application\values\TypeRepository;

class FileLocatorConstants extends FileLocatorRepo
{
	protected function getTypeRepository()
	{	return TypeRepository::CONSTANTS;
	}


	public function __construct($path, $env = null)
	{
		$this->rep  = TypeRepository::CONSTANTS;
		parent::__construct($path, $env);
	}


	// TODO think about moving elsewhere possibly
	public function getVcsWorkDir_Dev    ()   {  return '/my/repos/const';}
	public function getVcsWorkDir_Staging()   {  return '/my/repos/const';}
	public function getVcsWorkDir_Prod   ()   {  return '/my/repos/const';}


	protected function getAcceptedLeadingPatterns()
	{
		$lpa[0] = '/my/config/const|^';    // file starts with pattern or with no leading pattern

		return $lpa;
	}

	public function locateInServer()
	{
		$leading_patterns_replacement[TypeEnv::DEV    ][0] = '/my/config/const/' ;
		$leading_patterns_replacement[TypeEnv::STAGING][0] = '/my/config/const/' ;
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = '/my/config/const/' ;

		return $this->locateCommon($leading_patterns_replacement);
	}

	public function locateInVcsWorkdir()
	{
		$leading_patterns_replacement[TypeEnv::DEV    ][0] = $this->getVcsWorkDir_Dev()     ."/dev/";
		$leading_patterns_replacement[TypeEnv::STAGING][0] = $this->getVcsWorkDir_Staging() ."/staging/";
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = $this->getVcsWorkDir_Prod()    ."/prod/";

		return $this->locateCommon($leading_patterns_replacement);
	}


	public function locateInSyncDir()
	{
		# Now we use the concept of SyncDir instead of Mirror

		$leading_patterns_replacement[TypeEnv::DEV    ][0] = '/my/config/const/';
		$leading_patterns_replacement[TypeEnv::STAGING][0] = '/my/config/const/';
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = self::DIR_PROD_SYNC.'config/php/constants/';

		return $this->locateCommon($leading_patterns_replacement);
	}

}