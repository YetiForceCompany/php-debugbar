<?php

namespace DebugBar\Tests\DataCollector;

use DebugBar\DataCollector\AggregatedCollector;
use DebugBar\Tests\DebugBarTestCase;

class AggregatedCollectorTest extends DebugBarTestCase
{
	public function setUp()
	{
		$this->c = new AggregatedCollector('test');
	}

	public function testAddCollector()
	{
		$this->c->addCollector($c = new MockCollector());
		$this->assertContains($c, $this->c->getCollectors());
		$this->assertSame($c, $this->c['mock']);
		$this->assertTrue(isset($this->c['mock']));
	}

	public function testCollect()
	{
		$this->c->addCollector(new MockCollector(['foo' => 'bar'], 'm1'));
		$this->c->addCollector(new MockCollector(['bar' => 'foo'], 'm2'));
		$data = $this->c->collect();
		$this->assertCount(2, $data);
		$this->assertArrayHasKey('foo', $data);
		$this->assertSame('bar', $data['foo']);
		$this->assertArrayHasKey('bar', $data);
		$this->assertSame('foo', $data['bar']);
	}

	public function testMergeProperty()
	{
		$this->c->addCollector(new MockCollector(['foo' => ['a' => 'b']], 'm1'));
		$this->c->addCollector(new MockCollector(['foo' => ['c' => 'd']], 'm2'));
		$this->c->setMergeProperty('foo');
		$data = $this->c->collect();
		$this->assertCount(2, $data);
		$this->assertArrayHasKey('a', $data);
		$this->assertSame('b', $data['a']);
		$this->assertArrayHasKey('c', $data);
		$this->assertSame('d', $data['c']);
	}

	public function testSort()
	{
		$this->c->addCollector(new MockCollector([['foo' => 2, 'id' => 1]], 'm1'));
		$this->c->addCollector(new MockCollector([['foo' => 1, 'id' => 2]], 'm2'));
		$this->c->setSort('foo');
		$data = $this->c->collect();
		$this->assertCount(2, $data);
		$this->assertSame(2, $data[0]['id']);
		$this->assertSame(1, $data[1]['id']);
	}
}
