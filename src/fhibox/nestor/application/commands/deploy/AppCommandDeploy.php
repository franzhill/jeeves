<?php

namespace fhibox\nestor\application\commands\deploy;

use fhibox\nestor\application\commands\CommandParent;
use fhibox\nestor\application\commands\deploy\delegates\CommandDeployDelegateDev;
use fhibox\nestor\application\commands\deploy\delegates\CommandDeployDelegateProd;
use fhibox\nestor\application\commands\deploy\delegates\CommandDeployDelegateProdAdmin;
use fhibox\nestor\application\commands\deploy\delegates\CommandDeployDelegateStaging;
use fhibox\nestor\application\values\TypeEnv;
use fhibox\nestor\application\values\TypeRepository;


/**
 * Command to deploy a source file
 *
 * @author fhi
 */
class AppCommandDeploy extends CommandParent
{
	/**
	 * @override
	 */
	protected function execute__()
	{  /*        */	$this->logger->debug("");

		$env = $this->getOptionValue('env_deploy');
		$rep = $this->getOptionValue('rep');

		switch (TypeEnv::from($env))  //@formatter:off
		{
			case TypeEnv::DEV     : $delegate = new CommandDeployDelegateDev     ($this); break;
			case TypeEnv::STAGING : $delegate = new CommandDeployDelegateStaging ($this); break;
			case TypeEnv::PROD    :
			                          switch (TypeRepository::from($rep))
			                          {
			                          	case TypeRepository::ADMIN   : $delegate = new CommandDeployDelegateProdAdmin($this); break;
			                          	default                      : $delegate = new CommandDeployDelegateProd     ($this); break;
			                          } 
			                          break;
			default : throw new \InvalidArgumentException("Environnement [$env] is not supported for command deploy");
		}

		$delegate->execute__();
	}
}
 