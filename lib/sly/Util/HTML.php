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
 * @ingroup util
 */
class sly_Util_HTML {
	/**
	 * Builds an attribute part for a tag and returns it as a string.
	 *
	 * @param  array  $attributes  Associative array of attribute values, where key is the attribute name and value is the attribute value.
	 *                             e.g. array('src' => 'picture.png', alt='my picture')
	 * @param  array  $force       Array of attributes that should be added, even if they are empty.
	 *                             e.g. array('alt')
	 * @return string              String with the attributes and their values
	 */
	public static function buildAttributeString($attributes, $force = array()) {
		$attributes = array_filter($attributes, array(__CLASS__, 'isAttribute'));

		foreach ($force as $attribute) {
			if (empty($attributes[$attribute])) $attributes[$attribute] = '';
		}

		foreach ($attributes as $key => &$value) {
			$value = strtolower(trim($key)).'="'.sly_html(trim($value)).'"';
		}

		return implode(' ', $attributes);
	}

	/**
	 * @deprecated moved to sallycms-backend sly_Helper_Sprite
	 * @param  string $target
	 * @param  string $text
	 * @param  string $class
	 * @return string
	 */
	public static function getSpriteLink($target, $text, $class) {
		if (empty($target)) {
			$span = array('class' => 'sly-sprite sly-sprite-'.$class);
			return sprintf('<span %s><span>%s</span></span>', self::buildAttributeString($span), sly_html($text));
		}

		$a = array('href' => $target, 'class' => 'sly-sprite sly-sprite-'.$class, 'title' => $text);
		return sprintf('<a %s><span>%s</span></a>', self::buildAttributeString($a), sly_html($text));
	}

	public static function startJavaScript() {
		ob_start();
		print "<script type=\"text/javascript\">\n// <![CDATA[\n";
	}

	public static function endJavaScript() {
		print "\n// ]]>\n</script>";
		print ob_get_clean();
	}

	/**
	 * @param string $content
	 */
	public static function printJavaScript($content) {
		self::startJavaScript();
		print $content;
		self::endJavaScript();
	}

	public static function startOnDOMReady() {
		self::startJavaScript();
		print 'jQuery(function($) { ';
	}

	public static function endOnDOMReady() {
		print ' });';
		self::endJavaScript();
	}

	/**
	 * @param string $content
	 */
	public static function onDOMReady($content) {
		self::startOnDOMReady();
		print $content;
		self::endOnDOMReady();
	}

	/**
	 * @param  mixed $value
	 * @return boolean
	 */
	public static function isAttribute($value) {
		return $value !== false && strlen(trim($value)) > 0;
	}

	/**
	 * @param string $value
	 * @param string $key
	 */
	public static function concatValues(&$value, $key) {
		$value = strtolower(trim($key)).'="'.sly_html(trim($value)).'"';
	}

	/**
	 * get an <img> tag
	 *
	 * @param  mixed   $image      image basename or sly_Model_Medium object
	 * @param  array   $attributes
	 * @param  boolean $forceUri
	 * @return string
	 */
	public static function getImageTag($image, array $attributes = array(), $forceUri = false) {
		$base = sly_Core::isBackend() ? '../' : '';

		if (is_string($image)) {
			$medium = sly_Util_Medium::findByFilename($image);
			if ($medium && $medium->exists()) $image = $medium;
		}

		if ($image instanceof sly_Model_Medium) {
			$src   = $base.'data/mediapool/'.$image->getFilename();
			$alt   = $image->getTitle();
			$title = $image->getTitle();
		}
		else {
			if ($forceUri) {
				$src = $base.'data/mediapool/'.$image;
			}
			else {
				// a transparent 1x1 sized PNG, 81byte in size
				$src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAAXNSR0IArs4c6QAAAAtJREFUCB1jYGAAAAADAAFPSAqvAAAAAElFTkSuQmCC';
			}

			$alt = $image;
		}

		$attributes['src'] = $src;

		if (!isset($attributes['alt'])) {
			$attributes['alt'] = $alt;
		}

		if (isset($title) && !isset($attributes['title'])) {
			$attributes['title'] = $title;
		}

		return self::buildNode('img', '', $attributes, array('alt'), true);
	}

	/**
	 * return a html node a string
	 *
	 * @param  string  $name             the nodename
	 * @param  string  $innerHtml        the nodes content
	 * @param  array   $attributes       all attributes as assoc array
	 * @param  array   $forceAttributes  attributed to add even is thay are empty
	 * @param  boolean $closeInline      whether the node is closed inline
	 * @return string
	 */
	public static function buildNode($name, $innerHtml = '', array $attributes = array(), array $forceAttributes = array(), $closeInline = false) {
		$attributeString = sly_Util_HTML::buildAttributeString($attributes, $forceAttributes);

		if ($closeInline) {
			return sprintf('<%s %s />', $name, $attributeString);
		}
		else {
			return sprintf('<%s %s>%s</%s>', $name, $attributeString, $innerHtml, $name);
		}
	}

	/**
	 * Replaces sally://ARTICLEID and sally://ARTICLEID-CLANGID in
	 * the $content by article http URLs.
	 *
	 * @param  string $content
	 * @return string
	 */
	public static function replaceSallyLinks($content) {
		preg_match_all('#sally://([0-9]+)(?:-([0-9]+))?/?#', $content, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

		$skew = 0;

		foreach ($matches as $match) {
			$complete = $match[0];
			$length   = strlen($complete[0]);
			$offset   = $complete[1];
			$id       = (int) $match[1][0];
			$clang    = isset($match[2]) ? (int) $match[2][0] : null;

			try {
				$repl = sly_Util_Article::getUrl($id, $clang);
			}
			catch (Exception $e) {
				$repl = '#';
			}

			// replace the match
			$content = substr_replace($content, $repl, $offset + $skew, $length);

			// ensure the next replacements get the correct offset
			$skew += strlen($repl) - $length;
		}

		return $content;
	}

	public static function getDatetimeTag($timestamp) {
		if ($timestamp === null) {
			$timestamp = time();
		}
		elseif (!sly_Util_String::isInteger($timestamp)) {
			$timestamp = strtotime($timestamp);
		}

		return '<abbr class="timeago" title="'.date('Y-m-d\TG:i:sP', $timestamp).'">'.sly_Util_String::formatDatetime($timestamp).'</abbr>';
	}
}
