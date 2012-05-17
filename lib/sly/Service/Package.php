<?php
/*
 * Copyright (c) 2012, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

/**
 * @author  christoph@webvariants.de
 * @ingroup service
 */
class sly_Service_Package {
	protected $sourceDir; ///< string

	/**
	 * @param string $sourceDir
	 */
	public function __construct($sourceDir) {
		$this->sourceDir = sly_Util_Directory::normalize($sourceDir).DIRECTORY_SEPARATOR;
	}

	/**
	 * @param  string $package   package name
	 * @return string
	 */
	public function baseDirectory($package = null) {
		$dir = $this->sourceDir;

		if (!empty($package)) {
			$dir .= str_replace('/', DIRECTORY_SEPARATOR, $package).DIRECTORY_SEPARATOR;
		}

		return $dir;
	}

	/**
	 * @param  string $package  package name
	 * @return boolean
	 */
	public function exists($package) {
		$base = $this->baseDirectory($package);
		return file_exists($base.'composer.json');
	}

	/**
	 * Get package author
	 *
	 * @param  string $package  package name
	 * @param  mixed  $default  default value if no author was specified in composer.json
	 * @return mixed            the author as given in static.yml
	 */
	public function getAuthor($package, $default = null) {
		return $this->getKey($package, 'author', $default);
	}

	/**
	 * Get support page
	 *
	 * @param  string $package  package name
	 * @param  mixed  $default  default value if no page was specified in composer.json
	 * @return mixed            the support page as given in static.yml
	 */
	public function getHomepage($package, $default = null) {
		return $this->getKey($package, 'homepage', $default);
	}

	/**
	 * Get parent package (only relevant for backend list)
	 *
	 * @param  string $package  package name
	 * @param  mixed  $default  default value if no page was specified in composer.json
	 * @return mixed            the parent package or null if not given
	 */
	public function getParent($package) {
		return $this->getKey($package, 'parent', null);
	}

	/**
	 * Get version
	 *
	 * This method tries to get the version from the composer.json. If no version
	 * is found, it tries to read the contents of a version file in the
	 * package's directory.
	 *
	 * @param  string $package  package name
	 * @param  mixed  $default  default value if no version was specified
	 * @return string           the version
	 */
	public function getVersion($package, $default = null) {
		$version     = $this->getKey($package, 'version', null);
		$baseDir     = $this->baseDirectory($package);
		$versionFile = $baseDir.'/version';

		if ($version === null && file_exists($versionFile)) {
			$version = trim(file_get_contents($versionFile));
		}

		$versionFile = $baseDir.'/VERSION';

		if ($version === null && file_exists($versionFile)) {
			$version = trim(file_get_contents($versionFile));
		}

		return $version === null ? $default : $version;
	}

	/**
	 * @param  string $package  package name
	 * @return string           e.g. 'package/vendor:package'
	 */
	public function getVersionKey($package) {
		return 'package/'.str_replace('/', ':', $package);
	}

	/**
	 * Get last known version
	 *
	 * This method reads the last known version from the local config. This can
	 * be used to determine whether a package has been updated.
	 *
	 * @param  string $package  package name
	 * @param  mixed  $default  default value if no version was specified
	 * @return string           the version
	 */
	public function getKnownVersion($package, $default = null) {
		$key     = $this->getVersionKey($package);
		$version = sly_Util_Versions::get($key);

		return $version === false ? $default : $version;
	}

	/**
	 * Set last known version
	 *
	 * @param  string $package  package name
	 * @param  string $version  new version
	 * @return string           the version
	 */
	public function setKnownVersion($package, $version) {
		$key = $this->getVersionKey($package);
		return sly_Util_Versions::set($key, $version);
	}

	/**
	 * Read a config value from the composer.json
	 *
	 * @param  string $package  package name
	 * @param  string $key      array key
	 * @param  mixed  $default  value if key is not set
	 * @return mixed            value or default
	 */
	public function getKey($package, $key, $default = null) {
		$composer = $this->baseDirectory($package).'composer.json';
		$value    = sly_Util_Composer::getKey($composer, $key);

		return $value === null ? $default : $value;
	}

	/**
	 * Return a list of required packages
	 *
	 * Required packages are packages that $package itself needs to run.
	 *
	 * @param  string  $package    package name
	 * @param  boolean $recursive  if true, requirements are search resursively
	 * @param  array   $ignore     list of packages to ignore (and not recurse into)
	 * @return array               list of required packages
	 */
	public function getRequirements($package, $recursive = true, array $ignore = array()) {
		$stack  = array($package);
		$result = array();

		// don't add self
		$ignore[] = $package;

		do {
			// take one out
			$pkg = array_shift($stack);

			// add its requirements
			if ($this->exists($pkg)) {
				$stack = array_merge($stack, array_keys($this->getKey($pkg, 'require', array())));
				$stack = array_unique($stack);
			}

			// filter out non-packages
			foreach ($stack as $idx => $req) {
				if (strpos($req, '/') === false) unset($stack[$idx]);
			}

			// respect ignore list
			foreach ($ignore as $i) {
				$idx = array_search($i, $stack);
				if ($idx !== false) unset($stack[$idx]);
			}

			// do not add $package itself or duplicates
			if ($pkg !== $package && !in_array($pkg, $result)) {
				$result[] = $pkg;
			}
		}
		while ($recursive && !empty($stack));

		natcasesort($result);
		return $result;
	}

	/**
	 * Return a list of dependent packages
	 *
	 * Dependent packages are packages that need $package to run.
	 *
	 * @param  string  $package    package name
	 * @param  boolean $recursive  if true, dependencies are search resursively
	 * @return array               list of required packages
	 */
	public function getDependencies($package, $recursive = true) {
		$all    = $this->findPackages();
		$stack  = array($package);
		$result = array();

		do {
			// take one out
			$pkg = array_shift($stack);

			// find packages requiering $pkg
			foreach ($all as $p) {
				$requirements = array_keys($this->getKey($p, 'require', array()));

				if (in_array($pkg, $requirements)) {
					$result[] = $p;
					$stack[]  = $p;
				}
			}

			$stack = array_unique($stack);
		}
		while ($recursive && !empty($stack));

		$result = array_unique($result);
		natcasesort($result);

		return $result;
	}

	/**
	 * @return array  list of found packages
	 */
	public function findPackages() {
		$root     = $this->baseDirectory();
		$packages = array();

		// If we're in a real composer vendor directory, there is a installed.json,
		// that contains a list of all packages. We use this to detect packages
		// have no composer.json themselves (aka leafo/lessphp).
		$installed = $root.'composer/installed.json';

		if (file_exists($installed)) {
			$data = sly_Util_JSON::load($installed);

			foreach ($data as $pkg) {
				$packages[] = $pkg['name'];
			}
		}

		// Or else scan the filesystem.
		else {
			$dirs = $this->readDir($root);

			foreach ($dirs as $dir) {
				// evil package not conforming to naming convention
				if ($this->exists($dir)) {
					$packages[] = $dir;
				}
				else {
					$subdirs = $this->readDir($root.$dir);

					foreach ($subdirs as $subdir) {
						// good package
						if ($this->exists($dir.$subdir)) {
							$packages[] = $dir.$subdir;
						}
					}
				}
			}
		}

		natcasesort($packages);
		return $packages;
	}

	private function readDir($dir) {
		$dir = new sly_Util_Directory($dir);
		return $dir->exists() ? $dir->listPlain(false, true) : array();
	}
}
