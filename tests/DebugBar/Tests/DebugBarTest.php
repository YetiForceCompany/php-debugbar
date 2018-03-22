<?php

namespace DebugBar\Tests;

use DebugBar\Tests\DataCollector\MockCollector;
use DebugBar\Tests\Storage\MockStorage;

class DebugBarTest extends DebugBarTestCase
{
	public function testAddCollector()
	{
		$this->debugbar->addCollector($c = new MockCollector());
		$this->assertTrue($this->debugbar->hasCollector('mock'));
		$this->assertSame($c, $this->debugbar->getCollector('mock'));
		$this->assertContains($c, $this->debugbar->getCollectors());
	}

	/**
	 * @expectedException \DebugBar\DebugBarException
	 */
	public function testAddCollectorWithSameName()
	{
		$this->debugbar->addCollector(new MockCollector());
		$this->debugbar->addCollector(new MockCollector());
	}

	public function testCollect()
	{
		$data = ['foo' => 'bar'];
		$this->debugbar->addCollector(new MockCollector($data));
		$datac = $this->debugbar->collect();

		$this->assertArrayHasKey('mock', $datac);
		$this->assertSame($datac['mock'], $data);
		$this->assertSame($datac, $this->debugbar->getData());
	}

	public function testArrayAccess()
	{
		$this->debugbar->addCollector($c = new MockCollector());
		$this->assertSame($c, $this->debugbar['mock']);
		$this->assertTrue(isset($this->debugbar['mock']));
		$this->assertFalse(isset($this->debugbar['foo']));
	}

	public function testStorage()
	{
		$this->debugbar->setStorage($s = new MockStorage());
		$this->debugbar->addCollector(new MockCollector(['foo']));
		$data = $this->debugbar->collect();
		$this->assertSame($s->data[$this->debugbar->getCurrentRequestId()], $data);
	}

	public function testGetDataAsHeaders()
	{
		$this->debugbar->addCollector($c = new MockCollector(['foo']));
		$headers = $this->debugbar->getDataAsHeaders();
		$this->assertArrayHasKey('phpdebugbar', $headers);
	}

	public function testSendDataInHeaders()
	{
		$http = $this->debugbar->getHttpDriver();
		$this->debugbar->addCollector($c = new MockCollector(['foo']));

		$this->debugbar->sendDataInHeaders();
		$this->assertArrayHasKey('phpdebugbar', $http->headers);
	}

	public function testSendDataInHeadersWithOpenHandler()
	{
		$http = $this->debugbar->getHttpDriver();
		$this->debugbar->setStorage($s = new MockStorage());
		$this->debugbar->addCollector($c = new MockCollector(['foo']));

		$this->debugbar->sendDataInHeaders(true);
		$this->assertArrayHasKey('phpdebugbar-id', $http->headers);
		$this->assertSame($this->debugbar->getCurrentRequestId(), $http->headers['phpdebugbar-id']);
	}

	public function testStackedData()
	{
		$http = $this->debugbar->getHttpDriver();
		$this->debugbar->addCollector($c = new MockCollector(['foo']));
		$this->debugbar->stackData();

		$this->assertArrayHasKey($ns = $this->debugbar->getStackDataSessionNamespace(), $http->session);
		$this->assertArrayHasKey($id = $this->debugbar->getCurrentRequestId(), $http->session[$ns]);
		$this->assertArrayHasKey('mock', $http->session[$ns][$id]);
		$this->assertSame($c->collect(), $http->session[$ns][$id]['mock']);
		$this->assertTrue($this->debugbar->hasStackedData());

		$data = $this->debugbar->getStackedData();
		$this->assertArrayNotHasKey($ns, $http->session);
		$this->assertArrayHasKey($id, $data);
		$this->assertSame(1, count($data));
		$this->assertArrayHasKey('mock', $data[$id]);
		$this->assertSame($c->collect(), $data[$id]['mock']);
	}

	public function testStackedDataWithStorage()
	{
		$http = $this->debugbar->getHttpDriver();
		$this->debugbar->setStorage($s = new MockStorage());
		$this->debugbar->addCollector($c = new MockCollector(['foo']));
		$this->debugbar->stackData();

		$id = $this->debugbar->getCurrentRequestId();
		$this->assertNull($http->session[$this->debugbar->getStackDataSessionNamespace()][$id]);

		$data = $this->debugbar->getStackedData();
		$this->assertSame($c->collect(), $data[$id]['mock']);
	}
}
