<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DebugBar\DataCollector;

/**
 * Collects array data.
 */
class ConfigCollector extends DataCollector implements Renderable
{
	protected $name;
	protected $data;

	/**
	 * @param array  $data
	 * @param string $name
	 */
	public function __construct(array $data = [], $name = 'config')
	{
		$this->name = $name;
		$this->data = $data;
	}

	/**
	 * Sets the data.
	 *
	 * @param array $data
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @return array
	 */
	public function collect()
	{
		$data = [];
		foreach ($this->data as $k => $v) {
			if (!is_string($v)) {
				$v = $this->getDataFormatter()->formatVar($v);
			}
			$data[$k] = $v;
		}
		return $data;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getWidgets()
	{
		$name = $this->getName();
		return [
			"$name" => [
				'icon' => 'gear',
				'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
				'map' => "$name",
				'default' => '{}'
			]
		];
	}
}
