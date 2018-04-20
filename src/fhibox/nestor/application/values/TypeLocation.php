<?php

namespace fhibox\nestor\application\values;


use fhibox\enum\BaseEnum;

/**
 *
 *
 * @author Francois hill
 */
class TypeLocation extends BaseEnum
{
	const SYNC_DIR      = "mirror";
	const VCS_WORKDIR = "vcs_workdir";
	const SERVER      = "server";
}



