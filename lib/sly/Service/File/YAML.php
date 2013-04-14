<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use Symfony\Component\Yaml\Yaml;

/**
 * @ingroup util
 */
class sly_Service_File_YAML extends sly_Service_File_Base {
	/**
	 * @throws sly_Exception
	 * @return string
	 */
	protected function getCacheDir() {
		$dir = SLY_TEMPFOLDER.'/sally/yaml-cache';
		return sly_Util_Directory::create($dir, null, true);
	}

	/**
	 * @param  string $filename
	 * @return mixed
	 */
	protected function readFile($filename) {
		$this->checkForSfYaml();
		return Yaml::parse($filename);
	}

	/**
	 * @param  string $filename
	 * @param  mixed  $data
	 * @return int               number of written bytes
	 */
	protected function writeFile($filename, $data) {
		$this->checkForSfYaml();
		return file_put_contents($filename, Yaml::dump($data, 5), LOCK_EX);
	}

	protected function checkForSfYaml() {
		if (!class_exists('Symfony\Component\Yaml\Yaml')) {
			throw new sly_Exception('Symfony\Yaml was not found. Did you forget `composer install`?');
		}
	}
}
