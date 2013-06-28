<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

// determine application based on the request URI

$router = function($server) {
	$base   = dirname($server['SCRIPT_NAME']);
	$reqUri = substr($server['REQUEST_URI'], strlen($base));
	$reqUri = preg_replace('/[?&].*$/', '', $reqUri);
	$reqUri = trim($reqUri, '/');

	// route other app URIs (be careful not to confuse the assets)
	if (preg_match('#^backend(?!/assets/)(/|$)#', $reqUri)) return array('backend', 'backend');
	if (preg_match('#^setup(?!/assets/)(/|$)#', $reqUri))   return array('setup',   'setup');
	if (preg_match('#^assets/#', $reqUri))                  return array('assets',  '/');

	// everything else goes through the frontend
	return array('frontend', '/');
};

return $router($_SERVER);
