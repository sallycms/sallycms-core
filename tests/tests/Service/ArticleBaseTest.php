<?php
/*
 * Copyright (c) 2013, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

class sly_Service_ArticleBaseTest extends sly_Service_ArticleTestBase {
	private static $clang = 5;

	public static function setUpBeforeClass() {
		sly_Core::setCurrentClang(self::$clang);
	}

	protected function getDataSetName() {
		return 'pristine-sally';
	}

	public function testGetNonExisting() {
		$this->assertNull($this->getService()->findById(1, self::$clang));
		$this->assertNull($this->getService()->findById(1, 2));
	}

	public function testAdd() {
		$service = $this->getService();
		$newID   = $service->add(0, 'my "article"', 1, -1);

		$this->assertInternalType('int', $newID);

		$art = $service->findById($newID, self::$clang);
		$this->assertInstanceOf('sly_Model_Article', $art);

		$this->assertEquals('my "article"', $art->getName());
		$this->assertEquals('', $art->getCatName());
		$this->assertEquals(1, $art->getPosition());
		$this->assertEquals('|', $art->getPath());
		$this->assertEquals(0, $art->getParentId());
		$this->assertTrue($art->isOnline());
	}

	public function testEdit() {
		$service = $this->getService();
		$id      = $service->add(0, 'my article', 1, -1);
		$art     = $service->findById($id, self::$clang);

		$service->edit($art, 'new title', 0);

		$art = $service->findById($id, self::$clang);

		$this->assertEquals('new title', $art->getName());
		$this->assertEquals('', $art->getCatName());
	}

	public function testDelete() {
		$service = $this->getService();
		$user    = sly_Service_Factory::getUserService()->findById(1);
		$new     = $service->add(0, 'Test', 1, -1, $user);

		// add a nw revision
		$article = $service->findById($new, self::$clang);
		$service->touch($article, $user);

		$service->deleteById($new);
		$this->assertFalse($service->exists($new));

		$article = $service->findById($new, self::$clang);
		$this->assertNull($article);
	}

	public function testChangeStatus() {
		$service = $this->getService();
		$id      = $service->add(0, 'tmp', 1, -1);

		$article = $service->findById($id, self::$clang);
		$this->assertTrue($article->isOnline());
		$service->changeStatus($article, 0);

		$article = $service->findById($id, self::$clang);
		$this->assertFalse($article->isOnline());

		$service->changeStatus($article, 1);
		$article = $service->findById($id, self::$clang);
		$this->assertTrue($article->isOnline());
	}
}
