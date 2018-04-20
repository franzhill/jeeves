<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 03/12/201x
 * Time: 15:13
 */

namespace fhibox\nestor\application\objects;


use fhibox\nestor\application\values\TypeRepository;
use fhibox\nestor\application\values\TypeEnv;


class FileLocatorAdmin extends FileLocatorRepo
{

	protected function getTypeRepository()
	{	return TypeRepository::ADMIN;
	}

/*


'realm' | Path given (as argument) |  Environment      | Path in server        | Path in vcs workdir (on server FRONT)   | Path in sync dir
========|==========================+===================+=======================|=========================================|========================
        |  <file>                  |                   |                       |                                         |
        |  /my/bin/<file>          |   dev staging     | /my/bin/<file>        | (/my/repos/admin)/<file>                |  Same as path in server (on server)
  0     |  Ex. :                   |   staging         |                       |                                         |
        |  slurps/cer.../certif    |   slurps1, slurps2|                       |                                         |
        |  slurps/panadom/param    |-------------------|                       |                                         |-------------------------
        |  /my/bin/slurps/...      |   prod            |                       |                                         | /my/work/var/prod3/sources/bin/<file>  (on FRONT)
--------|--------------------------+-------------------+-----------------------|------------------------------------------------------------------


  Basename is always <file>
 */

	// TODO think about moving elsewhere possibly
	public function getVcsWorkDir_Dev    ()   {  return '/my/repos/admin';}
	public function getVcsWorkDir_Staging()   {  return '/my/repos/admin';}
	public function getVcsWorkDir_Prod   ()   {  return '/my/repos/admin';}


	protected function getAcceptedLeadingPatterns()
	{
		$lpa[0] = '/my/bin|^';    // file starts with /my/bin or with no leading pattern

		return $lpa;
	}


	public function locateInServer()
	{
		$leading_patterns_replacement[TypeEnv::DEV    ][0] = '/my/bin/' ;
		$leading_patterns_replacement[TypeEnv::STAGING][0] = '/my/bin/' ;
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = '/my/bin/' ;

		return $this->locateCommon($leading_patterns_replacement);
	}

	public function locateInVcsWorkdir()
	{
		$leading_patterns_replacement[TypeEnv::DEV    ][0] = $this->getVcsWorkDir_Dev()     ."/";
		$leading_patterns_replacement[TypeEnv::STAGING][0] = $this->getVcsWorkDir_Staging() ."/";
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = $this->getVcsWorkDir_Prod()    ."/";

		return $this->locateCommon($leading_patterns_replacement);
	}


	public function locateInSyncDir()
	{
		# Now we use the concept of SyncDir instead of Mirror

		$leading_patterns_replacement[TypeEnv::DEV    ][0] = '/my/bin/';
		$leading_patterns_replacement[TypeEnv::STAGING][0] = '/my/bin/';
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = self::DIR_PROD_SYNC.'/bin/';

		return $this->locateCommon($leading_patterns_replacement);
	}


}