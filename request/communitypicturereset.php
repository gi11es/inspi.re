<?php/*        Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)               Reset the community's picture to the default one*/require_once(dirname(__FILE__).'/../entities/community.php');require_once(dirname(__FILE__).'/../constants.php');if (isset($_REQUEST['xid'])) {	$community = Community::get($_REQUEST['xid']);	$community->setPid(null);	echo $GRAPHICS_PATH.'default-community-picture-big.png';}?>