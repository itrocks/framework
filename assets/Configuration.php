<?php
namespace ITRocks\Framework\Assets;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ITRocks\Framework\Application;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Paths;

/**
 * Configuration of assets for an application or a plugin
 */
class Configuration
{

	//------------------------------------------------------------------------------- ASSETS_FILENAME
	const ASSETS_FILENAME = 'assets.html';

	//--------------------------------------------------------------------------------------- COMMENT
	const COMMENT = '<!-- Source : %s %s -->' . LF;

	//---------------------------------------------------------------------------------- COMMENT_TYPE
	/**
	 * Configuration constructor.
	 * Uses a pattern factory via static method get()
	 *
	 * @param $file_path string
	 * @see Configuration::get()
	 */
	const COMMENT_TYPE = 8;

	//--------------------------------------------------------------------------------- $applications
	/**
	 * @var static[]
	 */
	public $applications = [];

	//------------------------------------------------------------------------------------- $excluded
	/**
	 * @var Element[]
	 */
	public $excluded = [];

	//------------------------------------------------------------------------------------ $file_path
	/**
	 * @var string
	 */
	public $file_path;

	//---------------------------------------------------------------------------------------- $first
	/**
	 * Locations of assets that should be loaded first
	 *
	 * @var Element[]
	 */
	public $first = [];

	//------------------------------------------------------------------------------------- $included
	/**
	 * Locations of assets not requiring specific order
	 *
	 * @var Element[]
	 */
	public $included = [];

	//----------------------------------------------------------------------------------------- $last
	/**
	 * Locations of assets that should be loaded at the end
	 *
	 * @var Element[]
	 */
	public $last = [];

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @var static[]
	 */
	public $plugins = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Configuration constructor.
	 *
	 * @param $file_path string
	 * @throws Assets_Exception
	 */
	protected function __construct($file_path)
	{
		$file_path = $this->checkPath($file_path);
		if ($file_path && array_search($file_path, $this->getAllFilePaths()) === false) {
			$this->file_path = $file_path;
			$this->load();
		}
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $file_path string
	 * @param $is_plugin boolean
	 * @throws Assets_Exception
	 */
	public function add($file_path, $is_plugin = true)
	{
		$file_path = $this->checkPath($file_path);
		if ($file_path && array_search($file_path, $this->getAllFilePaths()) === false) {
			$conf = new self($file_path);
			if ($is_plugin) {
				$this->plugins[] = $conf;
			}
			else {
				$this->applications[] = $conf;
			}
		}
	}

	//----------------------------------------------------------------------------- aggregateElements
	/**
	 * @param $configurations static[]
	 * @param $priority       string   Priority of integration
	 * @param $cache          string[] Path to skip
	 * @return string[]
	 * @see Priority
	 */
	private function aggregateElements($configurations, $priority, &$cache)
	{
		$elements = [];
		foreach ($configurations as $configuration) {
			if (!count($configuration->$priority)) continue;
			$elements[] = sprintf(
				static::COMMENT,
				$priority,
				Paths::getRelativeFileName($configuration->file_path)
			);

			/** @var Element $element */
			foreach ($configuration->$priority as $element) {
				$element->toRelativePath();
				if (!isset($cache[$element->path])) {
					$cache[$element->path] = true;
					$elements[]            = (string)$element;
				}
			}
		}
		return $elements;
	}

	//------------------------------------------------------------------------------------- checkPath
	/**
	 * @param $path string
	 * @return string|false
	 */
	protected function checkPath($path)
	{
		$path = realpath($path);
		$path = (is_dir($path) ? $path : dirname($path)) . SL . static::ASSETS_FILENAME;
		return file_exists($path) ? $path : false;
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * @return static
	 * @throws Assets_Exception
	 */
	public static function get()
	{
		$configuration = new self(
			Names::classToFilePath(get_class(Application::current()))
		);

		// Add all applications assets
		foreach (Application::current()->include_path->getSourceDirectories() as $directory) {
			$configuration->add(Paths::$project_root . SL . $directory, false);
		};

		// Add all loaded plugin
		foreach (Session::current()->plugins->getAll() as $plugin => $props) {
			$configuration->add(Names::classToFilePath($plugin));
		};

		return $configuration;
	}

	//------------------------------------------------------------------------------- getAllFilePaths
	/**
	 * @return string[]
	 */
	public function getAllFilePaths()
	{
		$paths = [$this->file_path];
		foreach ($this->applications as $configuration) {
			$paths[] = $configuration->file_path;
		}
		foreach ($this->plugins as $configuration) {
			$paths[] = $configuration->file_path;
		}
		return $paths;
	}

	//----------------------------------------------------------------------------- getConfigurations
	/**
	 * @param $reverse boolean
	 * @return Configuration[]
	 */
	private function getConfigurations($reverse = false)
	{
		if ($reverse) {
			return array_merge($this->plugins, [$this], $this->applications);
		}
		return array_merge(array_reverse($this->applications), [$this], $this->plugins);
	}

	//----------------------------------------------------------------------------- getStringElements
	/**
	 * @return string[]
	 */
	public function getStringElements()
	{
		$cache = [];
		foreach ($this->getConfigurations() as $configuration) {
			foreach ($configuration->excluded as $element) {
				$element->toRelativePath();
				$cache[$element->path] = true;
			};
		}

		$first = $this->aggregateElements(
			$this->getConfigurations(true),
			Priority::FIRST,
			$cache);

		$last = $this->aggregateElements(
			$this->getConfigurations(),
			Priority::LAST,
			$cache);

		$included = $this->aggregateElements(
			$this->getConfigurations(),
			Priority::INCLUDED,
			$cache);

		return array_merge($first, $included, $last);
	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * Loads configuration
	 *
	 * @throws Assets_Exception
	 */
	protected function load()
	{
		$dom = new DOMDocument();
		if (!$dom->loadHTMLFile($this->file_path)) {
			throw new Assets_Exception('Cannot parse file ' . $this->file_path);
		}
		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query(Element::REGEX);

		$mode = Priority::INCLUDED;
		/** @var DOMElement $node */
		foreach ($nodes as $node) {
			$value = trim($node->nodeValue);
			if ($node->nodeType === XML_COMMENT_NODE) {
				// Ignore non-priority comment
				if (Priority::valid($value)) {
					$mode = $value;
				}
			}
			else if ($node->nodeType === XML_ELEMENT_NODE) {
				$this->{$mode}[] = new Element($node, dirname($this->file_path));
			}
		}
	}

}
