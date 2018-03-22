<?php

namespace DebugBar\Tests\Storage;

use DebugBar\Storage\FileStorage;
use DebugBar\Tests\DebugBarTestCase;

class FileStorageTest extends DebugBarTestCase
{
	public function setUp()
	{
		$this->dirname = '/tmp/debugbar';
		if (!file_exists($this->dirname)) {
			mkdir($this->dirname, 0777);
		}
		$this->s = new FileStorage($this->dirname);
		$this->data = ['__meta' => ['id' => 'foo']];
	}

	public function testSave()
	{
		$this->s->save('foo', $this->data);
		$this->assertFileExists($this->dirname . '/foo.json');
		$this->assertJsonStringEqualsJsonFile($this->dirname . '/foo.json', json_encode($this->data));
	}

	public function testGet()
	{
		$data = $this->s->get('foo');
		$this->assertSame($this->data, $data);
	}

	public function testFind()
	{
		$results = $this->s->find();
		$this->assertContains($this->data['__meta'], $results);
	}

	public function testClear()
	{
		$this->s->clear();
		$this->assertFileNotExists($this->dirname . '/foo.json');
	}
}
