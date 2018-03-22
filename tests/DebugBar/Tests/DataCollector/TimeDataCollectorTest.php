<?php

namespace DebugBar\Tests\DataCollector;

use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\Tests\DebugBarTestCase;

class TimeDataCollectorTest extends DebugBarTestCase
{
	public function setUp()
	{
		$this->s = microtime(true);
		$this->c = new TimeDataCollector($this->s);
	}

	public function testAddMeasure()
	{
		$this->c->addMeasure('foo', $this->s, $this->s + 10, ['a' => 'b'], 'timer');
		$m = $this->c->getMeasures();
		$this->assertCount(1, $m);
		$this->assertSame('foo', $m[0]['label']);
		$this->assertSame(10, $m[0]['duration']);
		$this->assertSame(['a' => 'b'], $m[0]['params']);
		$this->assertSame('timer', $m[0]['collector']);
	}

	public function testStartStopMeasure()
	{
		$this->c->startMeasure('foo', 'bar', 'baz');
		$this->c->stopMeasure('foo', ['bar' => 'baz']);
		$m = $this->c->getMeasures();
		$this->assertCount(1, $m);
		$this->assertSame('bar', $m[0]['label']);
		$this->assertSame('baz', $m[0]['collector']);
		$this->assertSame(['bar' => 'baz'], $m[0]['params']);
		$this->assertTrue($m[0]['start'] < $m[0]['end']);
	}

	public function testCollect()
	{
		$this->c->addMeasure('foo', 0, 10);
		$this->c->addMeasure('bar', 10, 20);
		$data = $this->c->collect();
		$this->assertTrue($data['end'] > $this->s);
		$this->assertTrue($data['duration'] > 0);
		$this->assertCount(2, $data['measures']);
	}
}
