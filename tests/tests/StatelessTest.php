<?php
/*
 * Copyright (c) 2012, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

abstract class sly_StatelessTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		sly_Core::cache()->flush('sly', true);
	}

	/**
	 * @return array
	 */
	protected function getRequiredPackages() {
		return array();
	}

	protected function loadPackage($package) {
		$service = sly_Service_Factory::getPackageManagerService();
		$service->load($package, true);
	}
}
