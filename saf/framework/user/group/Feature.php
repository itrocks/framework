<?php
namespace SAF\Framework\User\Group;

use SAF\Framework\Controller;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;

/**
 * The represents an atomic end-user feature into the software :
 * a feature which a user group gives access to
 *
 * @before_write beforeWrite
 * @representative name
 * @sort name
 */
class Feature
{

	const ADMIN = [
		Controller\Feature::F_ADMIN,
		Controller\Feature::F_DELETE
	];

	const EDIT = [
		Controller\Feature::F_ADD,
		Controller\Feature::F_EDIT,
		Controller\Feature::F_WRITE
	];

	const OUTPUT = [
		Controller\Feature::F_LIST,
		Controller\Feature::F_OUTPUT
	];

	//------------------------------------------------------------------------------------- $features
	/**
	 * The low-level features activated by this end-user feature
	 *
	 * @getter getFeatures
	 * @link Collection
	 * @store false
	 * @var Low_Level_Feature[]
	 */
	public $features;

	//------------------------------------------------------------------------------------- $implicit
	/**
	 * The list of implicit low-level features :
	 * They are constants from SAF\Framework\Controller\Feature that do not need a yaml file
	 * Defined as an addition of ADMINISTRATE, EDIT and OUTPUT constants
	 * More can be dynamically added if needed
	 *
	 * @var string[]
	 */
	public static $implicit;

	//------------------------------------------------------------------------------------- $includes
	/**
	 * Included end-user features
	 *
	 * @getter getIncludes
	 * @link Collection
	 * @store false
	 * @var Feature[]
	 */
	public $includes;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * The fully readable name of the end-user feature.
	 * Default will be calculated from $path :
	 * Names::classToDisplay(getClassName()) . SP . Names::methodToDisplay(getFeatureName())
	 *
	 * @getter getName
	 * @mandatory
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * This is the path of the end-user feature
	 *
	 * Respect the case, the same used for URIs !
	 *
	 * @example A/Module/Namespace/A_Class/featureName
	 * @see getFileNames() for possible storage files for a the example path
	 * @var string
	 */
	public $path;

	//----------------------------------------------------------------------------------------- $yaml
	/**
	 * The end-user feature details, stored into a raw structure like it is into the yaml fil
	 * (recursive array)
	 *
	 * If a file contains several end-user features, this contains only the matching end-user feature
	 * Set by :
	 * - fileMatches() (once a file matches)
	 *
	 * @getter getYaml
	 * @store false
	 * @var Yaml|boolean false if the feature is not applicable (no file, not implicit)
	 */
	public $yaml;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $path string eg 'A/Module/Namespace/A_Class/featureName' always the full path
	 * @param $name string eg 'A class feature name'
	 */
	public function __construct($path = null, $name = null)
	{
		if (isset($path)) {
			$this->path = $path;
		}
		if (isset($name)) {
			$this->name = $this->resolveName($name);
		}
		if (!isset(self::$implicit)) {
			$implicit_features = array_merge(Feature::ADMIN, Feature::EDIT, Feature::OUTPUT);
			Feature::$implicit = array_combine($implicit_features, $implicit_features);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name ? Loc::tr($this->name) : '';
	}

	//----------------------------------------------------------------------------------- beforeWrite
	/**
	 * Called @before_write
	 *
	 * Call all getters necessary for a correct write of the feature into the data link
	 * (needs name)
	 */
	public function beforeWrite()
	{
		$this->getName();
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * Gets the full name of the class, read into the path
	 *
	 * @example 'A/Module/Namespace/A_Class/featureName' :
	 * the class name is 'A/Module/Namespace/A_Class';
	 * @return string
	 */
	private function getClassName()
	{
		return Names::pathToClass(lLastParse($this->path, SL));
	}

	//-------------------------------------------------------------------------------- getFeatureName
	/**
	 * Gets the name of the end-user feature, read into the path, without the name of the class
	 *
	 * @example 'A/Module/Namespace/A_Class/featureName' :
	 * the feature name is 'featureName';
	 * @return string
	 */
	private function getFeatureName()
	{
		return rLastParse($this->path, SL);
	}

	//-------------------------------------------------------------------------------- getAllFeatures
	/**
	 * Gets all features from $this->includes + $this->features
	 *
	 * @return Low_Level_Feature[]
	 */
	public function getAllFeatures()
	{
		$features = [];
		foreach ($this->includes as $include) {
			$features = array_merge($features, $include->getAllFeatures());
		}
		return array_merge($features, $this->features);
	}

	//----------------------------------------------------------------------------------- getFeatures
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * Low-level features are always stored into yaml files or implicit, not in databases or others
	 * data links
	 *
	 * @return Low_Level_Feature[]
	 */
	private function getFeatures()
	{
		if (!isset($this->features)) {
			$class_path = str_replace(BS, SL, $this->getClassName());
			$features = $this->yaml ? $this->yaml->getFeatures($class_path) : [];
			$this->features = $features;
		}
		return $this->features;
	}

	//---------------------------------------------------------------------------------- getFileNames
	/**
	 * Gets the list of the names of the potential files that may contain the end-user feature detail
	 *
	 * @example
	 * - file 'a/module/namespace/a_class/featureName.yaml'
	 * - multi-features file 'a/module/namespace/a_class/A_Class.yaml'
	 * - multi-features file 'a/module/namespace/a_class/features.yaml'
	 * - file 'a/module/namespace/A_Class_featureName.yaml'
	 * - multi-features file 'a/module/namespace/A_Class.yaml'
	 * @return string[]
	 */
	private function getFileNames()
	{
		$class_name     = $this->getClassName();
		$class_path     = strtolower(Names::classToPath($class_name));
		$namespace_path = lLastParse($class_path, SL);
		$short_class    = Namespaces::shortClassName($class_name);

		$feature = $this->getFeatureName();

		return [
			$class_path . SL . $feature . DOT . Yaml::YAML,
			$class_path . SL . $short_class . DOT . Yaml::YAML,
			$class_path . SL . Yaml::FEATURES . DOT . Yaml::YAML,
			$namespace_path . SL . $short_class . '_' . $feature . DOT . Yaml::YAML,
			$namespace_path . SL . $short_class . DOT . Yaml::YAML
		];
	}

	//--------------------------------------------------------------------------- getImplicitFeatures
	/**
	 * Get implicit end-user features names
	 *
	 * @return string[] @example ['admin', 'edit', 'output']
	 */
	public static function getImplicitFeatures()
	{
		return [Controller\Feature::F_ADMIN, Controller\Feature::F_EDIT, Controller\Feature::F_OUTPUT];
	}

	//----------------------------------------------------------------------------------- getIncludes
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * Get included end-user features
	 *
	 * @return Feature[]
	 */
	private function getIncludes()
	{
		if (!isset($this->includes)) {
			$this->includes = $this->yaml ? $this->yaml->getIncludes($this->path) : [];
		}
		return $this->includes;
	}

	//--------------------------------------------------------------------------------------- getName
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * Initialises the name from the yaml file or generate its default value from $path
	 *
	 * @return string
	 */
	private function getName()
	{
		if (!isset($this->name)) {
			$name = $this->yaml ? $this->yaml->getName() : null;
			if (isset($name)) {
				$this->name = $this->resolveName($name);
			}
			// default name
			elseif (isset($this->path)) {
				$this->name = ucfirst(Names::classToDisplay(Names::classToSet($this->getClassName())))
					. SP . Names::methodToDisplay($this->getFeatureName());
			}
		}
		return $this->name;
	}

	//--------------------------------------------------------------------------------------- getYaml
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * Gets the yaml object of the feature : matches the file that contains the end-user feature data,
	 * or false if there is no file and the feature is not implicit (bad feature).
	 *
	 * @return Yaml|boolean
	 */
	private function getYaml()
	{
		if (!isset($this->yaml)) {
			// find existing yaml file
			foreach ($this->getFileNames() as $filename) {
				if (file_exists($filename)) {
					$yaml = new Yaml($filename);
					if ($yaml->fileMatches($this->path)) {
						$this->yaml = $yaml;
						break;
					}
				}
			}
			// implicit yaml file content
			if (!isset($this->yaml)) {
				if ($this->isImplicit()) {
					$default_yaml = new Default_Yaml($this->getClassName(), $this->getFeatureName());
					$this->yaml = $default_yaml->toYaml();
				}
			}
		}
		return $this->yaml;
	}

	//------------------------------------------------------------------------------------ isImplicit
	/**
	 * Returns true if the feature can be considered as implicit (use default feature file)
	 *
	 * @return boolean
	 */
	private function isImplicit()
	{
		return isset(self::$implicit[$this->getFeatureName()]);
	}

	//----------------------------------------------------------------------------------- resolveName
	/**
	 * @param $name string
	 * @return string
	 */
	private function resolveName($name)
	{
		// name can contain $class and $feature
		if (strpos($name, '$') !== false) {
			$name = ucfirst(str_replace(
				['$class', '$feature'],
				[
					ucfirst(Names::classToDisplay(Names::classToSet($this->getClassName()))),
					Names::methodToDisplay($this->getFeatureName())
				],
				$name
			));
		}
		return $name;
	}

}
