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
 * DB Model Klasse für Benutzer
 *
 * @author  christoph@webvariants.de
 * @ingroup service
 */
class sly_Service_User extends sly_Service_Model_Base_Id {
	private static $currentUser = false; ///< mixed
	protected $tablename = 'user'; ///< string

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

		if (!isset($params['login']) || mb_strlen($params['login']) === 0) {
			throw new sly_Exception(t('no_username_given'));
		}

		if (!isset($params['psw']) || mb_strlen($params['psw']) === 0) {
			throw new sly_Exception(t('no_password_given'));
		}

		if ($this->find(array('login' => $params['login']))) {
			throw new sly_Exception(t('user_login_already_exists'));
		}

		$defaults = array(
			'status'      => false,
			'rights'      => '',
			'name'        => '',
			'description' => '',
			'lasttrydate' => null,
			'revision'    => 0,
			'updatedate'  => time(),
			'createdate'  => time(),
			'updateuser'  => $creator->getLogin(),
			'createuser'  => $creator->getLogin(),
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
	 * @param  string         $rights
	 * @param  sly_Model_User $creator   creator or null for the current user
	 * @return sly_Model_User $user
	 */
	public function add($login, $password, $active, $rights, sly_Model_User $creator = null) {
		return $this->create(array(
			'login'  => $login,
			'psw'    => $password,
			'status' => (boolean) $active,
			'rights' => $rights
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
		$event   = ($user->getId() == sly_Model_Base_Id::NEW_ID) ? 'SLY_USER_ADDED' : 'SLY_USER_UPDATED';
		$user    = parent::save($user);

		sly_Core::cache()->flush('sly.user');
		sly_Core::dispatcher()->notify($event, $user, array('user' => $manager));

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
		$retval = parent::delete($where);

		sly_Core::cache()->flush('sly.user');
		sly_Core::dispatcher()->notify('SLY_USER_DELETED', $where['id']);

		return $retval;
	}

	/**
	 * return user object with login
	 *
	 * @param  string $login
	 * @return sly_Model_User
	 */
	public function findByLogin($login) {
		$res = $this->find(array('login' => $login));
		if (count($res) == 1) return $res[0];
		return null;
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
		$obj       = sly_Core::cache()->get($namespace, $id, null);

		if ($obj === null) {
			$obj = $this->findOne(array('id' => $id));

			if ($obj !== null) {
				sly_Core::cache()->set($namespace, $id, $obj);
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
		if (sly_Core::config()->get('SETUP')) return null;

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
			$loginOK = $user->getLastTryDate() < time()-sly_Core::config()->get('RELOGINDELAY')
					&& $user->getStatus() == 1
					&& $this->checkPassword($user, $password);

			if ($loginOK) {
				sly_Util_Session::set('UID', $user->getId());
				sly_Util_Session::regenerate_id();

				// upgrade hash if possible
				$current  = $user->getPassword();
				$upgraded = sly_Util_Password::upgrade($password, $current);

				if ($upgraded) {
					$user->setHashedPassword($upgraded);
				}
			}

			$user->setLastTryDate(time());
			$this->save($user);

			self::$currentUser = false;
		}

		return $loginOK;
	}

	public function logout() {
		sly_Util_Session::set('UID', '');
		self::$currentUser = null;
	}

	/**
	 * Checks if the given password matches to the users password
	 *
	 * @param  sly_Model_User $user      The user object
	 * @param  string         $password  Password to check
	 * @return boolean                   true if the passwords match, otherwise false.
	 */
	public function checkPassword(sly_Model_User $user, $password) {
		return sly_Util_Password::verify($password, $user->getPassword(), $user->getCreateDate());
	}
}
