<?php

namespace DebugBar\Tests;

use DebugBar\OpenHandler;
use DebugBar\Tests\Storage\MockStorage;

class OpenHandlerTest extends DebugBarTestCase
{
	public function setUp()
	{
		parent::setUp();
		$this->debugbar->setStorage(new MockStorage(['foo' => ['__meta' => ['id' => 'foo']]]));
		$this->openHandler = new OpenHandler($this->debugbar);
	}

	public function testFind()
	{
		$request = [];
		$result = $this->openHandler->handle($request, false, false);
		$this->assertJsonArrayNotEmpty($result);
	}

	public function testGet()
	{
		$request = ['op' => 'get', 'id' => 'foo'];
		$result = $this->openHandler->handle($request, false, false);
		$this->assertJsonIsObject($result);
		$this->assertJsonHasProperty($result, '__meta');
		$data = json_decode($result, true);
		$this->assertSame('foo', $data['__meta']['id']);
	}

	/**
	 * @expectedException \DebugBar\DebugBarException
	 */
	public function testGetMissingId()
	{
		$this->openHandler->handle(['op' => 'get'], false, false);
	}

	public function testClear()
	{
		$result = $this->openHandler->handle(['op' => 'clear'], false, false);
		$this->assertJsonPropertyEquals($result, 'success', true);
	}
}
