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

	//--------------------------------------------------------------------------------- $applications
	/** @var static[] Child configurations coming from application inheritance */
	public array $applications = [];

	//------------------------------------------------------------------------------------- $excluded
	/** @var Element[] */
	public array $excluded = [];

	//------------------------------------------------------------------------------------ $file_path
	/** Direct path to assets file for this configuration */
	public string $file_path = '';

	//---------------------------------------------------------------------------------------- $first
	/** @var Element[] Locations of assets that should be loaded first */
	public array $first = [];

	//------------------------------------------------------------------------------------- $included
	/** @var Element[] Locations of assets not requiring specific order */
	public array $included = [];

	//----------------------------------------------------------------------------------------- $last
	/** @var Element[] Locations of assets that should be loaded at the end */
	public array $last = [];

	//-------------------------------------------------------------------------------------- $plugins
	/** @var static[] Child configurations coming from application inheritance */
	public array $plugins = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Configuration constructor
	 *
	 * @throws Assets_Exception
	 */
	protected function __construct(string $file_path)
	{
		$file_path = $this->checkPath($file_path);
		if (!$file_path) {
			return;
		}
		$this->file_path = $file_path;
		$this->load();
	}

	//------------------------------------------------------------------------------------------- add
	/** @throws Assets_Exception */
	public function add(string $file_path, bool $is_plugin = true) : void
	{
		$file_path = $this->checkPath($file_path);
		if (!$file_path) {
			return;
		}
		$configuration = new static($file_path);
		if ($is_plugin) {
			$this->plugins[] = $configuration;
		}
		else {
			$this->applications[] = $configuration;
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
	private function aggregateElements(array $configurations, string $priority, array &$cache) : array
	{
		$elements = [];
		foreach ($configurations as $configuration) {
			if (!count($configuration->$priority)) continue;
			$elements[] = sprintf(
				static::COMMENT,
				$priority,
				Paths::getRelativeFileName($configuration->file_path)
			);

			/** @var $element Element */
			foreach ($configuration->$priority as $element) {
				$element->toRelativePath();
				if (!isset($cache[$element->path])) {
					$cache[$element->path] = true;
					$elements[]            = strval($element);
				}
			}
		}
		return $elements;
	}

	//------------------------------------------------------------------------------------- checkPath
	/** Gets direct path to assets file : if file exist and is not already imported */
	protected function checkPath(string $path) : string
	{
		$path = realpath($path);
		$path = (is_dir($path) ? $path : dirname($path)) . SL . static::ASSETS_FILENAME;
		return (file_exists($path) && !in_array($path, $this->getAllFilePaths())) ? $path : '';
	}

	//------------------------------------------------------------------------------------------- get
	/** @throws Assets_Exception */
	public static function get() : static
	{
		$configuration = new static(
			Names::classToFilePath(get_class(Application::current()))
		);

		// Add all applications assets
		foreach (Application::current()->include_path->getSourceDirectories() as $directory) {
			$configuration->add(Paths::$project_root . SL . $directory, false);
		}

		// Add all loaded plugin
		foreach (Session::current()->plugins->getAll() as $plugin => $props) {
			$configuration->add(Names::classToFilePath($plugin));
		}

		return $configuration;
	}

	//------------------------------------------------------------------------------- getAllFilePaths
	/** @return string[] */
	public function getAllFilePaths() : array
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
	/** @return Configuration[] */
	private function getConfigurations(bool $reverse = false) : array
	{
		if ($reverse) {
			return array_merge($this->plugins, [$this], $this->applications);
		}
		return array_merge(array_reverse($this->applications), [$this], $this->plugins);
	}

	//----------------------------------------------------------------------------- getStringElements
	/** @return string[] */
	public function getStringElements() : array
	{
		$cache = [];
		foreach ($this->getConfigurations() as $configuration) {
			foreach ($configuration->excluded as $element) {
				$element->toRelativePath();
				$cache[$element->path] = true;
			}
		}

		$first = $this->aggregateElements(
			$this->getConfigurations(true),
			Priority::FIRST,
			$cache
		);

		$last = $this->aggregateElements(
			$this->getConfigurations(),
			Priority::LAST,
			$cache
		);

		$included = $this->aggregateElements(
			$this->getConfigurations(),
			Priority::INCLUDED,
			$cache
		);

		return array_merge($first, $included, $last);
	}

	//------------------------------------------------------------------------------------------ load
	/** @throws Assets_Exception */
	protected function load() : void
	{
		$dom = new DOMDocument();
		if (!$dom->loadHTMLFile($this->file_path)) {
			throw new Assets_Exception('Cannot parse file ' . $this->file_path);
		}
		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query(Element::REGEX);

		$mode = Priority::INCLUDED;
		/** @var $node DOMElement */
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
