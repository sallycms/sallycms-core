<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

use wv\BabelCache\CacheInterface;

/**
 * Service class for managing users
 *
 * @author  christoph@webvariants.de
 * @ingroup service
 */
class sly_Service_User extends sly_Service_Model_Base_Id {
	private static $currentUser = false; ///< mixed

	protected $tablename = 'user'; ///< string
	protected $cache;              ///< CacheInterface
	protected $dispatcher;         ///< sly_Event_IDispatcher
	protected $config;             ///< sly_Configuration

	/**
	 * Constructor
	 *
	 * @param sly_DB_Persistence    $persistence
	 * @param CacheInterface        $cache
	 * @param sly_Event_IDispatcher $dispatcher
	 * @param sly_Configuration     $config
	 */
	public function __construct(sly_DB_Persistence $persistence, CacheInterface $cache, sly_Event_IDispatcher $dispatcher, sly_Configuration $config) {
		parent::__construct($persistence);

		$this->cache      = $cache;
		$this->dispatcher = $dispatcher;
		$this->config     = $config;
	}

	/**
	 * @param  array  $where
	 * @param  string $group
	 * @param  string $order
	 * @param  int    $offset
	 * @param  int    $limit
	 * @param  string $having
	 * @return array
	 */
	public function find($where = null, $group = null, $order = null, $offset = null, $limit = null, $having = null) {
		return parent::find($where, $group, $order, $offset, $limit, $having);
	}

	/**
	 * @param  array $params
	 * @return sly_Model_User
	 */
	protected function makeInstance(array $params) {
		return new sly_Model_User($params);
	}

	/**
	 * @param  array          $params
	 * @param  sly_Model_User $creator  creator or null for the current user
	 * @return sly_Model_User
	 */
	public function create($params, sly_Model_User $creator = null) {
		$creator = $this->getActor($creator, __METHOD__);

		$defaults = array(
			'status'      => false,
			'attributes'  => array(),
			'name'        => '',
			'psw'         => '',
			'description' => '',
			'lasttrydate' => null,
			'revision'    => 0
		);

		$params = array_merge($defaults, $params);
		$model  = $this->makeInstance($params);
		$model->setPassword($params['psw']);

		return $this->save($model, $creator);
	}

	/**
	 * @param  string         $login
	 * @param  string         $password
	 * @param  boolean        $active
	 * @param  array          $attributes
	 * @param  sly_Model_User $creator   creator or null for the current user
	 * @return sly_Model_User $user
	 */
	public function add($login, $password, $active, $attributes, sly_Model_User $creator = null) {
		return $this->create(array(
			'login'      => $login,
			'psw'        => $password,
			'status'     => (boolean) $active,
			'attributes' => $attributes
		), $creator);
	}

	/**
	 * Save an existing user model instance
	 *
	 * @param  sly_Model_User $user
	 * @param  sly_Model_User $manager  saving user or null for the current user
	 * @return sly_Model_User $user
	 */
	public function save(sly_Model_Base $user, sly_Model_User $manager = null) {
		$manager = $this->getActor($manager, __METHOD__);
		$adding  = $user->getId() == sly_Model_Base_Id::NEW_ID;

		if (mb_strlen($user->getLogin()) === 0) {
			throw new sly_Exception(t('no_username_given'));
		}

		if ($adding) {
			if ($this->count(array('login' => $user->getLogin())) > 0) {
				throw new sly_Exception(t('user_login_already_exists'));
			}

			$user->setCreateColumns($manager);
		}

		$user->setUpdateColumns($manager);

		// notify addOns
		$event = $adding ? 'SLY_PRE_USER_ADD' : 'SLY_PRE_USER_UPDATE';
		$this->dispatcher->notify($event, $user, compact('manager'));

		// save the changes
		$event = $adding ? 'SLY_USER_ADDED' : 'SLY_USER_UPDATED';
		$user  = parent::save($user);

		$this->cache->clear('sly.user');
		$this->dispatcher->notify($event, $user, array('user' => $manager));

		return $user;
	}

	/**
	 * @throws sly_Exception
	 * @param  sly_Model_User $user
	 * @return int
	 */
	public function deleteByUser(sly_Model_User $user) {
		return $this->deleteById($user->getId());
	}

	public function delete($where) {
		$users = $this->find($where);

		foreach($users as $user) {
			// allow external code to stop the delete operation
			$this->dispatcher->notify('SLY_PRE_USER_DELETE', $user);
			$retval = parent::delete(array('id' => $user->getId()));
			$this->dispatcher->notify('SLY_USER_DELETED', $user);
		}

		$this->cache->clear('sly.user');

		return $retval;
	}

	/**
	 * return user object with login
	 *
	 * @param  string $login
	 * @return sly_Model_User
	 */
	public function findByLogin($login) {
		return $this->findOne(array('login' => $login));
	}

	/**
	 * @param  int $id
	 * @return sly_Model_User
	 */
	public function findById($id) {
		$id = (int) $id;

		if ($id <= 0) {
			return null;
		}

		$namespace = 'sly.user';
		$obj       = $this->cache->get($namespace, $id, null);

		if ($obj === null) {
			$obj = $this->findOne(array('id' => $id));

			if ($obj !== null) {
				$this->cache->set($namespace, $id, $obj);
			}
		}

		return $obj;
	}

	/**
	 * return current user object
	 *
	 * @param  boolean $forceRefresh
	 * @return sly_Model_User
	 */
	public function getCurrentUser($forceRefresh = false) {
		if (sly_Core::isSetup()) return null;

		if (self::$currentUser === false || $forceRefresh) {
			$userID = sly_Util_Session::get('UID', 'int', -1);
			self::$currentUser = $this->findById($userID);
		}

		return self::$currentUser;
	}

	/**
	 * set current user object
	 *
	 * @param sly_Model_User $user  the user that should be logged in from now on
	 */
	public function setCurrentUser(sly_Model_User $user) {
		sly_Util_Session::set('UID', $user->getId());
		self::$currentUser = $user;
	}

	/**
	 * @param  string $login
	 * @param  string $password
	 * @return boolean
	 */
	public function login($login, $password) {
		$user    = $this->findByLogin($login);
		$loginOK = false;

		if ($user instanceof sly_Model_User) {
			$loginOK = $this->isValidLogin($user, $password);

			if ($loginOK) {
				$session = sly_Core::getSession();
				$session->set('UID', $user->getId());
				$session->regenerateID();

				sly_Util_Csrf::setToken(null, $session);

				// upgrade hash if possible
				$this->upgradePasswordHash($user, $password);
			}

			$user->setLastTryDate(time());
			$this->save($user, $user);

			self::$currentUser = false;
		}

		return $loginOK;
	}

	public function logout() {
		sly_Core::getSession()->flush();
		self::$currentUser = null;
	}

	protected function upgradePasswordHash(sly_Model_User $user, $password) {
		$current  = $user->getPassword();
		$upgraded = sly_Util_Password::upgrade($password, $current);

		if ($upgraded) {
			$user->setHashedPassword($upgraded);
		}
	}

	protected function isValidLogin(sly_Model_User $user, $password) {
		return
			   $user->getStatus() == 1
			&& $user->getLastTryDate() < (time() - $this->config->get('relogindelay'))
			&& $this->checkPassword($user, $password);
	}

	/**
	 * Checks if the given password matches to the users password
	 *
	 * @param  sly_Model_User $user      The user object
	 * @param  string         $password  Password to check
	 * @return boolean                   true if the passwords match, otherwise false.
	 */
	public function checkPassword(sly_Model_User $user, $password) {
		// Old Sally versions used the createdate as the salt for the password.
		// As have changed since then not only from UNIX timestamps to MySQL
		// datetimes, but also to storing UTC, we now have to go back one more
		// time to build the proper salt (only needed if the password hasn't
		// been upgraded already).
		// Kill this once we don't expect old Sally versions to be migrated
		// anymore.
		$localCreateDate = strtotime(gmdate('Y-m-d H:i:s', $user->getCreateDate()));

		return sly_Util_Password::verify($password, $user->getPassword(), $localCreateDate);
	}
}
