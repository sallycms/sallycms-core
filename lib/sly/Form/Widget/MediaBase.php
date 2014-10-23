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
 * Media widget
 *
 * This element will render a special widget that allows the user to select
 * a file from the mediapool. The handled value is the file's name, not its ID.
 *
 * @ingroup form
 * @author  Christoph
 */
abstract class sly_Form_Widget_MediaBase extends sly_Form_ElementBase {
	protected $filetypes  = array();
	protected $categories = array();
	protected $readonly   = false;

	/**
	 * @return sly_Form_Widget_MediaBase  the widget itself
	 */
	public function filterByCategories(array $cats, $recursive = false) {
		foreach ($cats as $cat) $this->filterByCategory($cat, $recursive);
		return $this;
	}

	/**
	 * @return sly_Form_Widget_MediaBase  the widget itself
	 */
	public function filterByCategory($cat, $recursive = false) {
		$catID = $cat instanceof sly_Model_MediaCategory ? $cat->getId() : (int) $cat;

		if (!$recursive) {
			if (!in_array($catID, $this->categories)) {
				$this->categories[] = $catID;
			}
		}
		else {
			$serv = sly_Core::getContainer()->getMediaCategoryService();
			$tree = $serv->findTree($catID, false);

			foreach ($tree as $id) {
				$this->categories[] = $id;
			}

			$this->categories = array_unique($this->categories);
		}

		return $this;
	}

	/**
	 * @return sly_Form_Widget_MediaBase  the widget itself
	 */
	public function filterByFiletypes(array $types) {
		foreach ($types as $type) {
			$this->filetypes[] = sly_Util_Mime::getType('tmp.'.ltrim($type, '.'));
		}

		$this->filetypes = array_unique($this->filetypes);
		return $this;
	}

	/**
	 * @return sly_Form_Widget_MediaBase  the widget itself
	 */
	public function clearCategoryFilter() {
		$this->categories = array();
		return $this;
	}

	/**
	 * @return sly_Form_Widget_MediaBase  the widget itself
	 */
	public function clearFiletypeFilter() {
		$this->filetypes = array();
		return $this;
	}

	/**
	 * Sets the input to read-only
	 *
	 * @param  boolean $readonly          true to set the widget readonly
	 * @return sly_Form_Widget_MediaBase  the widget itself
	 */
	public function setReadOnly($readonly = true) {
		$this->readonly = $readonly;

		return $this;
	}

	protected function canAccessMedia() {
		$user = sly_Util_User::getCurrentUser();

		if (!$user || (!$user->isAdmin() && !$user->hasRight('pages', 'mediapool'))) {
			return false;
		}

		return true;
	}
}
