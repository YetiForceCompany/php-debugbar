<?php

namespace DebugBar\Tests\DataCollector;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\Tests\DebugBarTestCase;

class ConfigCollectorTest extends DebugBarTestCase
{
	public function testCollect()
	{
		$c = new ConfigCollector(['s' => 'bar', 'a' => [], 'o' => new \stdClass()]);
		$data = $c->collect();
		$this->assertArrayHasKey('s', $data);
		$this->assertSame('bar', $data['s']);
		$this->assertArrayHasKey('a', $data);
		$this->assertSame('[]', $data['a']);
		$this->assertArrayHasKey('o', $data);
	}

	public function testName()
	{
		$c = new ConfigCollector([], 'foo');
		$this->assertSame('foo', $c->getName());
		$this->assertArrayHasKey('foo', $c->getWidgets());
	}
}
