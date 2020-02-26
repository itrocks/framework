<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Controller;
use ITRocks\Framework\Controller\Getter;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Feature_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;

/**
 * The represents an atomic end-user feature into the software :
 * a feature which a user group gives access to
 *
 * @before_write beforeWrite
 * @business
 * @representative name
 * @sort name
 */
class Feature
{

	//----------------------------------------------------------------------------------------- ADMIN
	const ADMIN = [
		Controller\Feature::F_ADMIN,
		Controller\Feature::F_DELETE
	];

	//------------------------------------------------------------------------------------------- API
	const API = [
		Controller\Feature::F_API
	];

	//------------------------------------------------------------------------------------------ EDIT
	const EDIT = [
		Controller\Feature::F_ADD,
		Controller\Feature::F_EDIT,
		Controller\Feature::F_SAVE
	];

	//---------------------------------------------------------------------------------------- EXPORT
	const EXPORT = [
		Controller\Feature::F_EXPORT
	];

	//--------------------------------------------------------------------------------------- F_PRINT
	const F_PRINT = [
		Controller\Feature::F_PRINT
	];

	//---------------------------------------------------------------------------------------- IMPORT
	const IMPORT = [
		Controller\Feature::F_IMPORT
	];

	//---------------------------------------------------------------------------------------- OUTPUT
	const OUTPUT = [
		Controller\Feature::F_CARDS,
		Controller\Feature::F_LIST,
		Controller\Feature::F_OUTPUT
	];

	//-------------------------------------------------------------------------------------- OVERRIDE
	const OVERRIDE = 'override';

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
	 * They are constants from ITRocks\Framework\Controller\Feature that do not need a yaml file
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
			$implicit_features = array_merge(
				Feature::ADMIN,
				Feature::API,
				Feature::EDIT,
				Feature::EXPORT,
				Feature::F_PRINT,
				Feature::IMPORT,
				Feature::OUTPUT
			);
			Feature::$implicit = array_combine($implicit_features, $implicit_features);
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
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
		return Names::pathToClass($this->getClassPath());
	}

	//---------------------------------------------------------------------------------- getClassPath
	/**
	 * Gets the Full/Class_Path from the feature path
	 *
	 * @return string
	 */
	private function getClassPath()
	{
		return lLastParse($this->path, SL);
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

	//----------------------------------------------------------------------------------- getFeatures
	/**
	 * Low-level features are always stored into yaml files or implicit, not in databases or others
	 * data links
	 *
	 * @return Low_Level_Feature[]
	 */
	protected function getFeatures()
	{
		if (!isset($this->features)) {
			$class_name = $this->getClassName();
			// fix access to features of removed classes
			if (class_exists($class_name) || interface_exists($class_name) || trait_exists($class_name)) {
				$class_path = str_replace(BS, SL, $this->getClassName());
				$features   = array_merge(
					$this->yaml ? $this->yaml->getFeatures($class_path) : [],
					$this->getPropertiesFeatures()
				);
				$this->features = $features;
			}
			else {
				$this->features = [];
			}
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
	 * @return string[] @example ['admin', 'edit', 'export', 'output']
	 */
	public static function getImplicitFeatures()
	{
		return [
			Controller\Feature::F_ADMIN,
			Controller\Feature::F_API,
			Controller\Feature::F_EDIT,
			Controller\Feature::F_EXPORT,
			Controller\Feature::F_IMPORT,
			Controller\Feature::F_PRINT,
			Controller\Feature::F_OUTPUT
		];
	}

	//----------------------------------------------------------------------------------- getIncludes
	/**
	 * Get included end-user features
	 *
	 * @return Feature[]
	 */
	protected function getIncludes()
	{
		if (!isset($this->includes)) {
			$this->includes = $this->yaml ? $this->yaml->getIncludes($this->path) : [];
		}
		return $this->includes;
	}

	//--------------------------------------------------------------------------------------- getName
	/**
	 * Initialises the name from the yaml file or generate its default value from $path
	 *
	 * @return string
	 */
	protected function getName()
	{
		if (empty($this->name)) {
			$name = $this->yaml ? $this->yaml->getName() : null;
			if (isset($name)) {
				$this->name = $this->resolveName($name);
			}
			// default name
			elseif (isset($this->path)) {
				$loc_disabled  = Loc::$disabled;
				Loc::$disabled = false;
				$this->name    = ucfirst(Loc::tr(
					HOLE_PIPE . Names::classToDisplays($this->getClassName()) . HOLE_PIPE
					. SP . HOLE_PIPE . Names::methodToDisplay($this->getFeatureName()) . HOLE_PIPE,
					static::class
				));
				Loc::$disabled = $loc_disabled;
			}
			if (Dao::getObjectIdentifier($this)) {
				Dao::write($this, Dao::only('name'));
			}
		}
		return $this->name;
	}

	//------------------------------------------------------------------------- getPropertiesFeatures
	/**
	 * Scan class properties for @ feature with the same name, and add low-level feature 'override'
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Low_Level_Feature[]
	 */
	private function getPropertiesFeatures()
	{
		/** @var $features Low_Level_Feature[] */
		$features = [];
		/** @noinspection PhpUnhandledExceptionInspection valid class name */
		$class        = new Reflection_Class($this->getClassName());
		$feature_name = $this->getFeatureName();
		$feature_path = $this->getClassPath() . SL . self::OVERRIDE;
		foreach ($class->getProperties([T_EXTENDS, T_USE]) as $property) {
			/** @var $annotations Feature_Annotation[] */
			$annotations = $property->getAnnotations(Feature_Annotation::ANNOTATION);
			foreach ($annotations as $annotation) {
				if ($annotation->getFeatureName() === $feature_name) {
					$values  = $annotation->values();
					$options = [$property->name => $values];
					if (isset($features[$feature_path])) {
						$features[$feature_path]->options = arrayMergeRecursive(
							$features[$feature_path]->options, $options
						);
					}
					else {
						$features[$feature_path] = new Low_Level_Feature($feature_path, $options);
					}
				}
			}
		}
		return $features;
	}

	//--------------------------------------------------------------------------------------- getYaml
	/**
	 * Gets the yaml object of the feature : matches the file that contains the end-user feature data,
	 * or false if there is no file and the feature is not implicit (bad feature).
	 *
	 * @return Yaml|boolean
	 */
	protected function getYaml()
	{
		if (isset($this->yaml)) {
			return $this->yaml;
		}
		$class_name   = $this->getClassName();
		$feature_name = $this->getFeatureName();
		if ($class_name && $feature_name) {
			// use common algorithm to found yaml feature file everywhere in class tree
			$filename = Getter::get($class_name, $feature_name, '', 'yaml', false);
			if ($filename) {
				$filename = Names::classToPath(reset($filename)) . '.yaml';
				if (file_exists($filename)) {
					$yaml = new Yaml($filename);
					if ($yaml->fileMatches($this->path)) {
						$this->yaml = $yaml;
					}
				}
			}
		}
		// use historical algorithm to found class' featureName.yaml or common feature.yaml file
		if (!isset($this->yaml)) {
			foreach ($this->getFileNames() as $filename) {
				if (!file_exists($filename)) {
					continue;
				}
				$yaml = new Yaml($filename);
				if ($yaml->fileMatches($this->path)) {
					$this->yaml = $yaml;
					break;
				}
			}
		}
		// implicit yaml file content
		if (!isset($this->yaml) && $this->isImplicit()) {
			$default_yaml = new Default_Yaml($class_name, $feature_name);
			$this->yaml   = $default_yaml->toYaml();
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
			$class_name    = $this->getClassName();
			$feature_name  = $this->getFeatureName();
			$loc_disabled  = Loc::$disabled;
			Loc::$disabled = false;
			$name          = ucfirst(str_replace(
				['$class feature', '$class', '$feature'],
				[
					Loc::tr(
						HOLE_PIPE . ucfirst(Names::classToDisplays($class_name)) . HOLE_PIPE
						. SP . HOLE_PIPE . Names::methodToDisplay($feature_name) . HOLE_PIPE,
						static::class
					),
					Loc::tr(Names::classToDisplays($class_name), $class_name),
					Loc::tr(Names::methodToDisplay($feature_name), $class_name)
				],
				$name
			));
			Loc::$disabled = $loc_disabled;
		}
		return $name;
	}

}
