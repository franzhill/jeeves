<?php
/**
 * Created by PhpStorm.
 * User: Francois hill
 * Date: 03/12/201x
 * Time: 15:13
 */

namespace fhibox\nestor\application\objects;


use fhibox\nestor\application\values\TypeEnv;
use fhibox\string\StringLib;


/**
 */
abstract class FileLocatorRepo extends FileLocator
{
	public function __construct($path, $env = null)
	{
		$this->rep  = $this->getTypeRepository();
		parent::__construct($path, $env);
	}


	protected abstract function getTypeRepository();


	/**
	 * Provide a one dimensional array of accepted leading patterns
	 * @return string
	 */
	protected abstract function getAcceptedLeadingPatterns();


	protected function locateCommon($leading_patterns_replacement)
	{	/*        */  isset($this->logger) && $this->logger->debug("");

		$leading_patterns_accepted = $this->getAcceptedLeadingPatterns();  // array
		$matches = array();  // array of arrays of matches
		$res     = array();  // array of results (true, false)
		$realm   = -1;

		foreach ($leading_patterns_accepted as $key=>$lpa)
		{
			// Handle special leading pattern ending in ...|^
			// meaning : searched pattern is not preceded by anything.
			// This syntax won't work directly in the (?<= ... )
			//  so we'll have to handle it ourselves.
			$allow_empty_leading_pattern = false;
			if (StringLib::endsWith($lpa, "|^"))
			{
				$allow_empty_leading_pattern = true;
				$lpa = StringLib::removeTrailing($lpa, "|^");
			}

			$is_matched = preg_match('/(?<='. '^'.addcslashes($lpa, '/')  .')\/(.*)/'    , $this->path, $matches[]);

			if ( (! ($is_matched === 1)) && $allow_empty_leading_pattern)	// Give it another try...
			{	$matches = array();   // Empty the array! (contains stuff from above preg_match)
				$is_matched = preg_match('/(.+)/'    , $this->path, $matches[]);
			}

			$res[]      = $is_matched;
			$realm      = ($is_matched === 1) ? $key : $realm;
		}

		#$res[0] = preg_match('/(?<='. addcslashes($leading_patterns_accepted[0], '/')  .')\/(.*)/'    , $this->path, $matches[0]);
		#$res[1] = preg_match('/(?<='. addcslashes($leading_patterns_accepted[1], '/')  .')\/(.*)/'    , $this->path, $matches[1]);
		#$res[2] = preg_match('/(?<='. addcslashes($leading_patterns_accepted[2], '/')  .')\/(.*)/'    , $this->path, $matches[2]);

		/*        */   isset($this->logger) && $this->logger->debug('$res='.print_r($res, true));

		// Check for preg_match errors:
		if (in_array(FALSE, $res, true))
		#if ($res[0] && $res[1] && $res[2] === FALSE)
		{	throw new \InvalidArgumentException("Error while trying to extract exact file path from given file path [$this->path]");
		}

		// If no pattern found, then this is a breach of contract
		if (array_sum($res) == 0)
		#if ($res[0] == 0 && $res[1] == 0 && $res[2] == 0)
		{	/*        */   isset($this->logger) && $this->logger->debug('no pattern found');
			throw new \InvalidArgumentException("File provided is not as expected: [$this->path]. Please refer to help.");
		}

		#// Determine which of the leading patterns matched;
		#$res_len = count($res);
		#for ($i=0; $i<$res_len; $i++)
		#{	if ($res[$i] != 0 ) break;
		#}


		#$match_idx = ($res[0] != 0) ? 0  : (($res[1] != 0) ? 1 : (($res[2] != 0) ? 2 : -1  ));
		/*        */  #$this->logger->debug("match_idx=$match_idx");
		/*        */  if ($realm == -1) {throw new \Exception("Programming Exception, should not ever happen");}
		/*        */  #$this->logger->debug('leading_patterns_replacement[$env][$match_idx]=' . $leading_patterns_replacement[$env][$match_idx]);
		/*        */  #$this->logger->debug('$matches[$match_idx][1]=' . $matches[$match_idx][1]);

		$basename = $matches[$realm][1];
		$realpath = $leading_patterns_replacement[$this->env][$realm] . $basename;

		// TODO : hosts should depend on the location
		// Ex : if asking for the sync dir in PROD, the host will be FRONT
		//             ...        sync dir in DEV or STAGING, the host will be DEV or STAGING respectively
		//             ...        vcs workdir in DEV, STAGING, PROD, the host will be FRONT
		return new FileLocation(TypeEnv::toHostName($this->env),$realpath, $basename);
	}




}