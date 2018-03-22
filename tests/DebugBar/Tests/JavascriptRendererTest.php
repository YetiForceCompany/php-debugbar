<?php

namespace DebugBar\Tests;

use DebugBar\DebugBar;
use DebugBar\JavascriptRenderer;

class JavascriptRendererTest extends DebugBarTestCase
{
	public function setUp()
	{
		parent::setUp();
		$this->r = new JavascriptRenderer($this->debugbar);
		$this->r->setBasePath('/bpath');
		$this->r->setBaseUrl('/burl');
	}

	public function testOptions()
	{
		$this->r->setOptions([
			'base_path' => '/foo',
			'base_url' => '/foo',
			'include_vendors' => false,
			'javascript_class' => 'Foobar',
			'variable_name' => 'foovar',
			'initialization' => JavascriptRenderer::INITIALIZE_CONTROLS,
			'enable_jquery_noconflict' => true,
			'controls' => [
				'memory' => [
					'icon' => 'cogs',
					'map' => 'memory.peak_usage_str',
					'default' => "'0B'"
				]
			],
			'disable_controls' => ['messages'],
			'ignore_collectors' => 'config',
			'ajax_handler_classname' => 'AjaxFoo',
			'ajax_handler_bind_to_jquery' => false,
			'open_handler_classname' => 'OpenFoo',
			'open_handler_url' => 'open.php'
		]);

		$this->assertSame('/foo', $this->r->getBasePath());
		$this->assertSame('/foo', $this->r->getBaseUrl());
		$this->assertFalse($this->r->areVendorsIncluded());
		$this->assertSame('Foobar', $this->r->getJavascriptClass());
		$this->assertSame('foovar', $this->r->getVariableName());
		$this->assertSame(JavascriptRenderer::INITIALIZE_CONTROLS, $this->r->getInitialization());
		$this->assertTrue($this->r->isJqueryNoConflictEnabled());
		$controls = $this->r->getControls();
		$this->assertCount(2, $controls);
		$this->assertArrayHasKey('memory', $controls);
		$this->assertArrayHasKey('messages', $controls);
		$this->assertNull($controls['messages']);
		$this->assertContains('config', $this->r->getIgnoredCollectors());
		$this->assertSame('AjaxFoo', $this->r->getAjaxHandlerClass());
		$this->assertFalse($this->r->isAjaxHandlerBoundToJquery());
		$this->assertSame('OpenFoo', $this->r->getOpenHandlerClass());
		$this->assertSame('open.php', $this->r->getOpenHandlerUrl());
	}

	public function testAddAssets()
	{
		$this->r->addAssets('foo.css', 'foo.js', '/bar', '/foobar');

		list($css, $js) = $this->r->getAssets();
		$this->assertContains('/bar/foo.css', $css);
		$this->assertContains('/bar/foo.js', $js);

		$html = $this->r->renderHead();
		//$this->assertTag(array('tag' => 'script', 'attributes' => array('src' => '/foobar/foo.js')), $html);
		$this->assertContains('<script type="text/javascript" src="/foobar/foo.js"></script>', $html);
	}

	public function testGetAssets()
	{
		list($css, $js) = $this->r->getAssets();
		$this->assertContains('/bpath/debugbar.css', $css);
		$this->assertContains('/bpath/widgets.js', $js);
		$this->assertContains('/bpath/vendor/jquery/dist/jquery.min.js', $js);

		$this->r->setIncludeVendors(false);
		$js = $this->r->getAssets('js');
		$this->assertContains('/bpath/debugbar.js', $js);
		$this->assertNotContains('/bpath/vendor/jquery/dist/jquery.min.js', $js);
	}

	public function testRenderHead()
	{
		$html = $this->r->renderHead();
		$this->assertContains('<script type="text/javascript" src="/burl/debugbar.js"></script>', $html);
		$this->assertTrue(strpos($html, 'jQuery.noConflict(true);') > -1);

		$this->r->setEnableJqueryNoConflict(false);
		$html = $this->r->renderHead();
		$this->assertFalse(strpos($html, 'jQuery.noConflict(true);'));
	}

	public function testRenderFullInitialization()
	{
		$this->debugbar->addCollector(new \DebugBar\DataCollector\MessagesCollector());
		$this->r->addControl('time', ['icon' => 'time', 'map' => 'time', 'default' => '"0s"']);
		$expected = rtrim(file_get_contents(__DIR__ . '/full_init.html'));
		$this->assertStringStartsWith($expected, $this->r->render());
	}

	public function testRenderConstructorOnly()
	{
		$this->r->setInitialization(JavascriptRenderer::INITIALIZE_CONSTRUCTOR);
		$this->r->setJavascriptClass('Foobar');
		$this->r->setVariableName('foovar');
		$this->r->setAjaxHandlerClass(false);
		$this->assertStringStartsWith("<script type=\"text/javascript\">\nvar foovar = new Foobar();\nfoovar.addDataSet(", $this->r->render());
	}

	public function testJQueryNoConflictAutoDisabling()
	{
		$this->assertTrue($this->r->isJqueryNoConflictEnabled());
		$this->r->setIncludeVendors(false);
		$this->assertFalse($this->r->isJqueryNoConflictEnabled());
		$this->r->setEnableJqueryNoConflict(true);
		$this->r->setIncludeVendors('css');
		$this->assertFalse($this->r->isJqueryNoConflictEnabled());
		$this->r->setEnableJqueryNoConflict(true);
		$this->r->setIncludeVendors(['css', 'js']);
		$this->assertTrue($this->r->isJqueryNoConflictEnabled());
	}

	public function testCanDisableSpecificVendors()
	{
		$this->assertContains('jquery.min.js', $this->r->renderHead());
		$this->r->disableVendor('jquery');
		$this->assertNotContains('jquery.min.js', $this->r->renderHead());
	}
}
