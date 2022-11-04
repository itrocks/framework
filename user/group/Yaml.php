<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Tools\Names;

/**
 * User group feature(s) yaml file management class
 */
class Yaml
{

	//---------------------------------------------------------------------------------- DEFAULTS_DIR
	const DEFAULTS_DIR = __DIR__ . SL . 'defaults';

	//-------------------------------------------------------------------------------------- FEATURES
	const FEATURES = 'features';

	//-------------------------------------------------------------------------------------- INCLUDES
	const INCLUDES = 'includes';

	//------------------------------------------------------------------------------------------ NAME
	const NAME = 'name';

	//------------------------------------------------------------------------------------------ PATH
	const PATH = 'path';

	//------------------------------------------------------------------------------------------ YAML
	const YAML = 'yaml';

	//----------------------------------------------------------------------------------------- $data
	/**
	 * The yaml data, stored as an array
	 *
	 * @var array
	 */
	private array $data;

	//------------------------------------------------------------------------------------- $filename
	/**
	 * The name of the file where the end-user feature is stored.
	 * - null   : not set (the getter sets this on first read)
	 * - string : a file was found by fileMatches()
	 * - true   : the file is implicit (then a default raw content has been set)
	 * - false  : there is no file nor implicit configuration for this path
	 *
	 * @store false
	 * @var bool|string|null
	 */
	public bool|string|null $filename;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Yaml file constructor
	 *
	 * @param $filename string|null
	 */
	public function __construct(string $filename = null)
	{
		if (isset($filename)) {
			$this->data     = yaml_parse_file($filename);
			$this->filename = $filename;
			if (!$this->data) {
				trigger_error(PRE . file_get_contents($filename) . _PRE, E_USER_WARNING);
			}
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return yaml_emit($this->data);
	}

	//------------------------------------------------------------------------------------ addFeature
	/**
	 * Adds a low-level feature
	 *
	 * @param $feature string
	 * @param $options array
	 */
	public function addFeature(string $feature, array $options = []) : void
	{
		if (!isset($this->data[self::FEATURES])) {
			$this->data[self::FEATURES] = [];
		}
		$features_data =& $this->data[self::FEATURES];
		if (!isset($features_data[$feature]) && !in_array($feature, $features_data)) {
			if ($options) {
				$features_data[$feature] = $options;
			}
			else {
				$features_data[] = $feature;
			}
		}
	}

	//------------------------------------------------------------------------------- defaultFileName
	/**
	 * Reads the content of the default file, stored into defaults/, for a given end-user feature
	 *
	 * @param $feature string An implicit end-user feature name
	 * @return string The content of the yaml file
	 */
	public static function defaultFileName(string $feature) : string
	{
		return self::DEFAULTS_DIR . SL . $feature . DOT . self::YAML;
	}

	//------------------------------------------------------------------------------------ extendYaml
	/**
	 * Extends low-level features and includes lists from the yaml structure
	 * If their value are strings, change them to arrays (comma-separated values)
	 *
	 * @example 'includes: edit, output'
	 * will be read as ['includes' => 'edit, output']
	 * extendsYaml will change it to ['includes' => ['edit', 'output']]
	 */
	public function extendYaml() : void
	{
		foreach ([self::FEATURES, self::INCLUDES] as $key) {
			if (isset($this->data[$key]) && is_string($this->data[$key])) {
				$value = $this->data[$key];
				$this->data[$key] = [];
				foreach (explode(',', $value) as $k => $v) {
					$this->data[$key][$k] = trim($v);
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- fileMatches
	/**
	 * Returns true if the yaml file matches (contains) the feature path
	 *
	 * The file matches the path if it contains an end-user feature without any path (not set),
	 * or if the path of the end-user feature matches $feature->path.
	 *
	 * If a matching end-user feature if found, sets $feature->raw and returns true
	 *
	 * @param $path string The full path of the atomic end-user feature to match
	 * @return boolean
	 */
	public function fileMatches(string $path) : bool
	{
		$default_path = lLastParse($path, SL);
		foreach ($this->data as $yaml_path => $feature_data) {
			if (is_string($feature_data) && ($yaml_path === self::PATH)) {
				$default_path = str_replace(BS, SL, $feature_data);
			}
			elseif ($yaml_path === self::FEATURES) {
				$this->extendYaml();
				return true;
			}
			elseif (is_array($feature_data)) {
				$yaml_path = str_replace(BS, SL, $yaml_path);
				if (!empty($yaml_path) && !str_contains($yaml_path, SL)) {
					$yaml_path = $default_path . SL . $yaml_path;
				}
				if ($yaml_path === $path) {
					$this->data = $feature_data;
					$this->extendYaml();
					return true;
				}
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- fromFile
	/**
	 * Gets all available yaml features data from file
	 *
	 * @param $filename string
	 * @return Yaml[]
	 */
	public static function fromFile(string $filename) : array
	{
		$result = [];
		$yaml   = new Yaml($filename);
		foreach ($yaml->data as $path => $feature_data) {
			if ($path === self::FEATURES) {
				$yaml->extendYaml();
				$result = [$yaml->getPath() => $yaml];
				break;
			}
			elseif (is_array($feature_data)) {
				$path = str_replace(BS, SL, $path);
				if (!empty($path) && !str_contains($path, SL)) {
					$path = lLastParse($yaml->getPath(), SL) . SL . $path;
				}
				$result_yaml = new Yaml();
				$result_yaml->data = $feature_data;
				$result_yaml->filename = $filename;
				$result_yaml->extendYaml();
				$result[$path] = $result_yaml;
			}
		}
		return $result;
	}

	//----------------------------------------------------------------------------------- getFeatures
	/**
	 * Gets the low-level features list stored into the yaml file
	 *
	 * @param $default_path string
	 * @return Low_Level_Feature[]
	 */
	public function getFeatures(string $default_path) : array
	{
		$features = [];
		if (isset($this->data[self::FEATURES])) {
			foreach ($this->data[self::FEATURES] as $feature => $feature_detail) {
				if (is_string($feature_detail) && !is_string($feature)) {
					$feature        = $feature_detail;
					$feature_detail = [];
				}
				elseif (!(is_string($feature) && is_array($feature_detail))) {
					trigger_error(
						'Parse of ' . $this->filename . ' features : feature is not allowed ['
						. print_r($feature, true) . ': ' . print_r($feature_detail, true) . ']',
						E_USER_ERROR
					);
				}
				if (!str_contains($feature, SL)) {
					$feature = $default_path . SL . $feature;
				}
				$features[$feature] = new Low_Level_Feature($feature, $feature_detail);
			}
		}
		return $features;
	}

	//------------------------------------------------------------------------------- getFilenamePath
	/**
	 * Returns the full path calculated from the name of the file
	 *
	 * @example
	 * a/full/path/class_name/feature.yaml -> a/full/path/class_name/feature
	 * a/full/path/Class_Name_feature.yaml -> a/full/path/Class_Name/feature
	 * @return string
	 */
	private function getFilenamePath() : string
	{
		$path       = lLastParse($this->filename, SL);
		$file_parts = explode('_', lParse(rLastParse($this->filename, SL), DOT));
		foreach ($file_parts as $key => $file_part) {
			if (ctype_lower($file_part[0])) {
				$path = str_replace(BS, SL, Names::fileToClass($path))
					. SL . join('_', array_splice($file_parts, $key));
				break;
			}
			$path .= $key ? ('_' . $file_part) : (SL . $file_part);
		}
		return $path;
	}

	//----------------------------------------------------------------------------------- getIncludes
	/**
	 * @param $default_path string
	 * @return Feature[]
	 */
	public function getIncludes(string $default_path) : array
	{
		$includes = [];
		if (isset($this->data[self::INCLUDES])) {
			foreach ($this->data[self::INCLUDES] as $feature) {
				if (!str_contains($feature, SL)) {
					$feature = lLastParse($default_path, SL) . SL . $feature;
				}
				$includes[$feature] = new Feature($feature);
			}
		}
		return $includes;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * Gets the atomic end-user feature name stored into the yaml file
	 *
	 * @return ?string
	 */
	public function getName() : ?string
	{
		return $this->data[self::NAME] ?? null;
	}

	//--------------------------------------------------------------------------------------- getPath
	/**
	 * Gets the full path of the feature stored into the file, or using the name of the file
	 *
	 * @return string
	 */
	private function getPath() : string
	{
		if (isset($this->data[self::PATH])) {
			$path = $this->data[self::PATH];
			if (!str_contains($path, SL)) {
				$path = lLastParse($this->getFilenamePath(), SL) . SL . $path;
			}
		}
		else {
			$path = $this->getFilenamePath();
		}
		return $path;
	}

}
