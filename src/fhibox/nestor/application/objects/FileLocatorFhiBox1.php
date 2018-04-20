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


/**
 * Named after the SVN repository 'fhibox1' which contains general source files.
 */
class FileLocatorFhiBox1 extends FileLocatorRepo
{

	protected function getTypeRepository()
	{	return TypeRepository::FHIBOX1;
	}


/*
'realm' | Path given (as argument) |  Environment      | Path in server        | Path in vcs workdir (on server FRONT)   | Path in sync dir (previously "mirror")
========|==========================+===================+=======================|=========================================|========================
        |  trunk/<file>            |                   |                       |                                         |
        |  www/<file>              |   dev or staging  | /my/www/<file>        | (/my/repos/fhibox1)/trunk/<file>        |  Same as path in server  (on server)
0       |  /my/www/<file>          |                   |                       |                                         |
        |                          +-------------------+                       |------------------------------------------------------------------
        |                          |   prod            |                       | (/my/repos/prod3/fhibox1)/trunk/<file>  | /my/work/var/prod3/sources/www/<file> (on FRONT)
--------|--------------------------+-------------------+-----------------------|------------------------------------------------------------------
        |                          |                   |                       |                                         |
        |  conf/<file>             |   dev or staging  | /my/conf/<file>       |  (/my/repos/fhibox1)/conf/<file>        |   Same as path in server  (on server)
1       |  /my/conf/<file>         |                   |                       |                                         |
        |                          +-------------------+                       |------------------------------------------------------------------
        |                          |   prod            |                       |  (/my/repos/prod3/fhibox1)/conf/<file>  | /my/work/var/prod3/sources/conf/<file>  (on FRONT)
--------|--------------------------+-------------------+-----------------------|------------------------------------------------------------------
        |  logs/<file>             |                   |                       |                                         |
        |                          |   dev or staging  | /my/logs/<file>       |  (/my/repos/fhibox1)/logs/<file>        |   Same as path in server  (on server)
2       |  /my/logs/<file>         |                   |                       |                                         |
        |                          +-------------------+                       |------------------------------------------------------------------
        |                          |   prod            |                       |  (/my/repos/prod3/fhibox1)/logs/<file>  |  /my/work/var/prod3/sources/conf/<file>  (on FRONT)
--------|--------------------------+-------------------+-----------------------|------------------------------------------------------------------
        |  other : assume trunk    |                   |                       |                                         |


  <file> is a file or a rep and can have several
  Basename is always <file>
*/

	// TODO think about moving elsewhere possibly
	public function getVcsWorkDir_Dev    ()   {  return '/my/repos/fhibox1';}
	public function getVcsWorkDir_Staging()   {  return '/my/repos/fhibox1';}
	public function getVcsWorkDir_Prod   ()   {  return '/my/repos/prod3/fhibox1';}


	protected function getAcceptedLeadingPatterns()
	{
		$lpa[0] = 'trunk|www|/my/www';
		$lpa[1] = 'conf|/my/conf'    ;
		$lpa[2] = 'logs|/my/logs'    ;

		return $lpa;
	}


	public function locateInServer()
	{	/*        */  isset($this->logger) && $this->logger->debug("");

		$leading_patterns_replacement[TypeEnv::DEV    ][0] = '/my/www/' ;
		$leading_patterns_replacement[TypeEnv::STAGING][0] = '/my/www/' ;
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = '/my/www/' ;

		$leading_patterns_replacement[TypeEnv::DEV    ][1] = '/my/conf/';
		$leading_patterns_replacement[TypeEnv::STAGING][1] = '/my/conf/';
		$leading_patterns_replacement[TypeEnv::PROD   ][1] = '/my/conf/';

		$leading_patterns_replacement[TypeEnv::DEV    ][2] = '/my/logs/';
		$leading_patterns_replacement[TypeEnv::STAGING][2] = '/my/logs/';
		$leading_patterns_replacement[TypeEnv::PROD   ][2] = '/my/logs/';

		return $this->locateCommon($leading_patterns_replacement);
	}


	public function locateInVcsWorkdir()
	{	/*        */  isset($this->logger) && $this->logger->debug("");

		$leading_patterns_replacement[TypeEnv::DEV    ][0] = $this->getVcsWorkDir_Dev()    .'/trunk/' ;
		$leading_patterns_replacement[TypeEnv::STAGING][0] = $this->getVcsWorkDir_Staging().'/trunk/' ;
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = $this->getVcsWorkDir_Prod()   .'/trunk/' ;

		$leading_patterns_replacement[TypeEnv::DEV    ][1] = $this->getVcsWorkDir_Dev()    .'/conf/' ;
		$leading_patterns_replacement[TypeEnv::STAGING][1] = $this->getVcsWorkDir_Staging().'/conf/' ;
		$leading_patterns_replacement[TypeEnv::PROD   ][1] = $this->getVcsWorkDir_Prod()   .'/conf/' ;

		$leading_patterns_replacement[TypeEnv::DEV    ][2] = $this->getVcsWorkDir_Dev()    .'/logs/' ;
		$leading_patterns_replacement[TypeEnv::STAGING][2] = $this->getVcsWorkDir_Staging().'/logs/' ;
		$leading_patterns_replacement[TypeEnv::PROD   ][2] = $this->getVcsWorkDir_Prod()   .'/logs/' ;

		return $this->locateCommon($leading_patterns_replacement);
	}


	public function locateInSyncDir()
	{	/*        */  isset($this->logger) && $this->logger->debug("");

		$leading_patterns_replacement[TypeEnv::DEV    ][0] = '/my/www/' ;
		$leading_patterns_replacement[TypeEnv::STAGING][0] = '/my/www/' ;
		$leading_patterns_replacement[TypeEnv::PROD   ][0] = self::DIR_PROD_SYNC.'/www/';

		$leading_patterns_replacement[TypeEnv::DEV    ][1] = '/my/conf/';
		$leading_patterns_replacement[TypeEnv::STAGING][1] = '/my/conf/';
		$leading_patterns_replacement[TypeEnv::PROD   ][1] = self::DIR_PROD_SYNC.'/conf/';

		$leading_patterns_replacement[TypeEnv::DEV    ][2] = '/my/logs/';
		$leading_patterns_replacement[TypeEnv::STAGING][2] = '/my/logs/';
		$leading_patterns_replacement[TypeEnv::PROD   ][2] = self::DIR_PROD_SYNC.'/logs/';

		return $this->locateCommon($leading_patterns_replacement);
	}



}