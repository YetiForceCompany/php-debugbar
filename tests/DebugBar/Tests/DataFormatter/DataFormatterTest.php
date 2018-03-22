<?php

namespace DebugBar\Tests\DataFormatter;

use DebugBar\DataFormatter\DataFormatter;
use DebugBar\Tests\DebugBarTestCase;

class DataFormatterTest extends DebugBarTestCase
{
	public function testFormatVar()
	{
		$f = new DataFormatter();
		$this->assertSame('true', $f->formatVar(true));
	}

	public function testFormatDuration()
	{
		$f = new DataFormatter();
		$this->assertSame('100μs', $f->formatDuration(0.0001));
		$this->assertSame('100ms', $f->formatDuration(0.1));
		$this->assertSame('1s', $f->formatDuration(1));
		$this->assertSame('1.35s', $f->formatDuration(1.345));
	}

	public function testFormatBytes()
	{
		$f = new DataFormatter();
		$this->assertSame('0B', $f->formatBytes(0));
		$this->assertSame('1B', $f->formatBytes(1));
		$this->assertSame('1KB', $f->formatBytes(1024));
		$this->assertSame('1MB', $f->formatBytes(1024 * 1024));
		$this->assertSame('-1B', $f->formatBytes(-1));
		$this->assertSame('-1KB', $f->formatBytes(-1024));
		$this->assertSame('-1MB', $f->formatBytes(-1024 * 1024));
	}
}
