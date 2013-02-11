<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

// load boot cache
$bootcache   = SLY_DYNFOLDER.'/internal/sally/bootcache.php';
$cacheExists = file_exists($bootcache);

if ($cacheExists) {
	require_once $bootcache;
}

// init the Composer autoloader
$loader = require SLY_VENDORFOLDER.'/autoload_52.php';
$loader->setAllowUnderscore(true);

// still load the old one, to give addOns time to update their code base
// We should remove this once we can properly handle file includes and reach
// maybe version 0.10 or 0.11.
sly_Loader::enablePathCache();
sly_Loader::register();

// cleanup
unset($bootcache, $cacheExists);

return $loader;
