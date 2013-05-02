<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

/**
 * Business Model Klasse für Benutzer
 *
 * @author  christoph@webvariants.de
 * @ingroup model
 */
class sly_Model_User extends sly_Model_Base_Id {
	protected $name;          ///< string
	protected $description;   ///< string
	protected $login;         ///< string
	protected $password;      ///< string
	protected $status;        ///< int
	protected $attributes;    ///< array
	protected $createuser;    ///< string
	protected $updateuser;    ///< string
	protected $createdate;    ///< int
	protected $updatedate;    ///< int
	protected $lasttrydate;   ///< int
	protected $timezone;      ///< string
	protected $revision;      ///< int

	protected $startpage;     ///< string
	protected $backendLocale; ///< string
	protected $isAdmin;       ///< boolean

	protected $_attributes = array(
		'name' => 'string', 'description' => 'string', 'login' => 'string', 'password' => 'string',
		'status' => 'int', 'attributes' => 'array', 'updateuser' => 'string',
		'updatedate' => 'datetime', 'createuser' => 'string', 'createdate' => 'datetime',
		'lasttrydate' => 'datetime', 'timezone' => 'string', 'revision' => 'int'
	); ///< array

	/**
	 * Constructor
	 *
	 * Both $default* variables are resolved by using the config if set to null.
	 *
	 * @param array  $params
	 * @param string $defaultStartpage  startpage to use if the user has none set yet
	 * @param string $defaultLocale     backend locale to use if none is set yet
	 */
	public function __construct($params = array(), $defaultStartpage = null, $defaultLocale = null) {
		parent::__construct($params);

		$this->evalRights($defaultStartpage, $defaultLocale);
	}

	/**
	 * Evaluate the encoded rights
	 *
	 * Both $default* variables are resolved by using the config if set to null.
	 *
	 * @param string $defaultStartpage  startpage to use if the user has none set yet
	 * @param string $defaultLocale     backend locale to use if none is set yet
	 */
	protected function evalRights($defaultStartpage = null, $defaultLocale = null) {
		if ($defaultStartpage === null || $defaultLocale === null) {
			$config = sly_Core::getContainer()->getConfig();
		}

		$this->startpage     = $defaultStartpage === null ? $config->get('start_page') : $defaultStartpage;
		$this->backendLocale = $defaultLocale === null ? $config->get('default_locale') : $defaultLocale;
		$this->isAdmin       = false;

		if (isset($this->attributes['isAdmin'])) {
			$this->isAdmin = (boolean) $this->attributes['isAdmin'];
		}

		if (isset($this->attributes['startpage'])) {
			$this->startpage = $this->attributes['startpage'];
		}

		if (isset($this->attributes['backendLocale'])) {
			$this->backendLocale = $this->attributes['backendLocale'];
		}
	}

	public function setName($name)               { $this->name        = $name;        } ///< @param string $name
	public function setDescription($description) { $this->description = $description; } ///< @param string $description
	public function setLogin($login)             { $this->login       = $login;       } ///< @param string $login

	/**
	 * Sets a password into the user model.
	 *
	 * This method is doing the hashing.
	 *
	 * @param string $password  The password (plain)
	 */
	public function setPassword($password) {
		if (mb_strlen($password) === 0) {
			throw new sly_Exception(t('no_password_given'));
		}

		$this->setHashedPassword(sly_Util_Password::hash($password));
	}

	/**
	 * Sets a password into the user model, where hashing is already done
	 *
	 * @param string $password  The hashed password
	 */
	public function setHashedPassword($password) {
		$this->password = $password;
	}

	/**
	 * @param mixed $createdate  unix timestamp or date using 'YYYY-MM-DD HH:MM:SS' format
	 */
	public function setCreateDate($createdate) {
		$this->createdate = sly_Util_String::isInteger($createdate) ? (int) $createdate : strtotime($createdate);
	}

	/**
	 * @param mixed $updatedate  unix timestamp or date using 'YYYY-MM-DD HH:MM:SS' format
	 */
	public function setUpdateDate($updatedate) {
		$this->updatedate = sly_Util_String::isInteger($updatedate) ? (int) $updatedate : strtotime($updatedate);
	}

	/**
	 * @param mixed $lasttrydate  unix timestamp or date using 'YYYY-MM-DD HH:MM:SS' format
	 */
	public function setLastTryDate($lasttrydate) {
		$this->lasttrydate = sly_Util_String::isInteger($lasttrydate) ? (int) $lasttrydate : strtotime($lasttrydate);
	}

	public function setStatus($status)         { $this->status     = (int) $status;   } ///< @param int    $status
	public function setCreateUser($createuser) { $this->createuser = $createuser;     } ///< @param string $createuser
	public function setUpdateUser($updateuser) { $this->updateuser = $updateuser;     } ///< @param string $updateuser
	public function setTimeZone($timezone)     { $this->timezone   = $timezone;       } ///< @param string $timezone
	public function setRevision($revision)     { $this->revision   = (int) $revision; } ///< @param int    $revision

	public function getName()        { return $this->name;        } ///< @return string
	public function getDescription() { return $this->description; } ///< @return string
	public function getLogin()       { return $this->login;       } ///< @return string
	public function getPassword()    { return $this->password;    } ///< @return string
	public function getStatus()      { return $this->status;      } ///< @return int
	public function getRights()      { return $this->rights;      } ///< @return string
	public function getCreateDate()  { return $this->createdate;  } ///< @return int
	public function getUpdateDate()  { return $this->updatedate;  } ///< @return int
	public function getCreateUser()  { return $this->createuser;  } ///< @return string
	public function getUpdateUser()  { return $this->updateuser;  } ///< @return string
	public function getLastTryDate() { return $this->lasttrydate; } ///< @return int
	public function getTimeZone()    { return $this->timezone;    } ///< @return string
	public function getRevision()    { return $this->revision;    } ///< @return int

	/**
	 * sets an attribute
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function setAttrubute($key, $value) {
		$this->attributes[(string) $key] = $value;
	}

	/**
	 * gets an attribute
	 *
	 * @param string $key
	 * @param mixed  $default
	 */
	public function getAttribute($key, $default = null) {
		$key = (string) $key;

		if (isset($this->attributes[$key])) {
			return $this->attributes[$key];
		}

		return $default;
	}

	/**
	 *
	 * @param boolean $isAdmin
	 */
	public function setIsAdmin($isAdmin) {
		$this->isAdmin = $this->attributes['isAdmin'] = (boolean) $isAdmin;
	}

	/**
	 *
	 * @param string $startPage
	 */
	public function setStartPage($startPage) {
		$this->startpage = $this->attributes['startpage'] = $startPage;
	}

	/**
	 *
	 * @param string $backendLocale
	 */
	public function setBackendLocale($backendLocale) {
		$this->backendLocale = $this->attributes['backendLocale'] = $backendLocale;
	}

	public function getStartPage()     { return $this->startpage;     } ///< @return string
	public function getBackendLocale() { return $this->backendLocale; } ///< @return string
	public function isAdmin()          { return $this->isAdmin;       } ///< @return boolean

	/**
	 * @return array
	 */
	public function getAllowedCLangs() {
		$service = sly_Core::getContainer()->getLanguageService();
		$allowed = array();

		foreach ($service->findAll(true) as $language) {
			if ($this->isAdmin() || $this->hasRight('language', 'access', $language)) {
				$allowed[] = $language;
			}
		}

		return $allowed;
	}

	/**
	 * @deprecated since 0.8 use hasPermission() instead
	 *
	 * @param  string $context
	 * @param  string $right
	 * @return boolean
	 */
	public function hasRight($context, $right, $value = true) {
		return $this->hasPermission($context, $right, $value);
	}

	/**
	 * @param  string $context
	 * @param  string $right
	 * @return boolean
	 */
	public function hasPermission($context, $right, $value = true) {
		return sly_Authorisation::hasPermission($this->getId(), $context, $right, $value);
	}

	/**
	 * @return int
	 */
	public function delete() {
		$service = sly_Core::getContainer()->getUserService();

		return $service->deleteById($this->id);
	}
}
