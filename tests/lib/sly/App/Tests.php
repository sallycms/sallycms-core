<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class sly_App_Tests implements sly_App_Interface {

	public function initialize() {
		$container = $this->getContainer();
		$container->getTemplateService()->refresh();
		$container->getModuleService()->refresh();
	}

	public function run() {

	}

	public function getCurrentController() {

	}

	public function getCurrentAction() {
		return 'test';
	}

	public function getContainer() {
		return sly_Core::getContainer();
	}

	public function isBackend() {
		return true;
	}
}
