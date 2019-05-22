<?php
namespace ITRocks\Framework\Plugin\Installable;

use ITRocks\Framework;
use ITRocks\Framework\Builder\Class_Builder;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder;
use ITRocks\Framework\Configuration\File\Builder\Assembled;
use ITRocks\Framework\Configuration\File\Builder\Replaced;
use ITRocks\Framework\Configuration\File\Source;
use ITRocks\Framework\Configuration\File\Source\Class_Use;
use ITRocks\Framework\Reflection\Annotation\Parser;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\Tools\Value_Lists;
use ReflectionException;

/**
 * Exhaustive class data management
 */
class Exhaustive_Class
{

	//------------------------------------------------------------------------------ ANNOTATION_NAMES
	const ANNOTATION_NAMES = ['display_order'];

	//------------------------------------------------------------------------------------- $assembly
	/**
	 * Assembled / replaced classes data, coming from Installer::$files
	 *
	 * @var Assembled[]|Replaced[]|Source[]
	 */
	public $assembly;

	//----------------------------------------------------------------------------- $class_components
	/**
	 * Class components local cache
	 *
	 * @var array string[][]
	 */
	protected $class_components = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $files File[]
	 */
	public function __construct(array $files)
	{
		$this->assembly = $this->assemblyFiles($files);
	}

	//--------------------------------------------------------------------------------- assemblyFiles
	/**
	 * @param $files File[]
	 * @return Assembled[]|Replaced[]|Source[]
	 */
	protected function assemblyFiles(array $files)
	{
		$assembly = [];
		// First pass : Builder
		foreach ($files as $file) {
			if ($file instanceof Builder) {
				foreach ($file->classes as $class) {
					if (($class instanceof Assembled) || ($class instanceof Replaced)) {
						$assembly[$class->class_name] = $class;
					}
				}
			}
		}
		// Source may override Builder, so it must be done during a second pass
		foreach ($files as $file) {
			if ($file instanceof Source) {
				$assembly[$file->class_name] = $file;
			}
		}
		return $assembly;
	}

	//--------------------------------------------------------------------- classAnnotationValueLists
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name      string
	 * @param $annotation_name string
	 * @return array string[][] value lists
	 */
	protected function classAnnotationValueLists($class_name, $annotation_name)
	{
		$value_lists = [];
		foreach ($this->classComponents($class_name) as $component) {
			/** @noinspection PhpUnhandledExceptionInspection must be valid */
			$annotation = Parser::byName(new Reflection_Class($component), $annotation_name, null, true);
			if ($annotation->value) {
				$value_lists[] = $annotation->value;
			}
		}
		return $value_lists;
	}

	//------------------------------------------------------------------------------ classAnnotations
	/**
	 * @param $class_name string
	 * @return string[] key is annotation name without @, value is the raw value of the annotation
	 */
	public function classAnnotations($class_name)
	{
		$annotations            = [];
		$property_names         = $this->classPropertyNames($class_name);
		foreach (static::ANNOTATION_NAMES as $annotation_name) {
			$class_value_lists      = $this->classAnnotationValueLists($class_name, $annotation_name);
			$exhaustive_value_lists = $this->exhaustiveValueLists($class_name, $annotation_name);
			$value_lists            = array_merge($class_value_lists, $exhaustive_value_lists);
			$value_list             = (new Value_Lists($value_lists))->assembly();
			$value_list             = array_intersect($value_list, $property_names);
			if ($value_list) {
				$annotations[$annotation_name] = join(', ', $value_list);
			}
		}
		return $annotations;
	}

	//------------------------------------------------------------------------------- classComponents
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $recurse    integer for internal use only
	 * @return string[] parent/trait component class names
	 */
	protected function classComponents($class_name, $recurse = 0)
	{
		if (isset($this->class_components[$class_name])) {
			return $this->class_components[$class_name];
		}
		$this->class_components[$class_name] = [];
		if (isset($this->assembly[$class_name])) {
			$assembly   = $this->assembly[$class_name];
			if (Class_Builder::isBuilt($class_name)) {
				$components = [];
			}
			else {
				try {
					$components = (new Reflection_Class($class_name))->getTraitNames();
				}
				catch (ReflectionException $exception) {
					$components = [];
				}
			}
			if ($assembly instanceof Assembled) {
				foreach ($assembly->components as $component) {
					if (!beginsWith($component, AT)) {
						$components[] = $component;
					}
				}
				array_unshift($components, $class_name);
				if ($parent_class_name = $this->parentClassName($class_name)) {
					array_unshift($components, $parent_class_name);
				}
			}
			elseif ($assembly instanceof Replaced) {
				$components = [$assembly->replacement];
			}
			elseif ($assembly instanceof Source) {
				$components = array_map(
					function(Class_Use $class_use) { return $class_use->trait_name; },
					$assembly->class_use
				);
				if ($parent_class_name = $this->parentClassName($assembly->class_extends)) {
					array_unshift($components, $parent_class_name);
				}
			}
		}
		else {
			/** @noinspection PhpUnhandledExceptionInspection must exist */
			$class      = new Reflection_Class($class_name);
			$components = $class->getTraitNames();
			if ($parent_class_name = $this->parentClassName($class_name)) {
				array_unshift($components, $parent_class_name);
			}
		}
		foreach ($components as $component) {
			if ($class_name !== $component) {
				$components = array_merge($this->classComponents($component, $recurse + 1), $components);
			}
		}
		$this->class_components[$class_name] = array_unique($components);
		return $recurse ? $components : array_unique($components);
	}

	//---------------------------------------------------------------------------- classPropertyNames
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return string[]
	 */
	protected function classPropertyNames($class_name)
	{
		$property_names = [];
		$components     = $this->classComponents($class_name);
		foreach ($components as $component) {
			// some components may be not already built classes : ignore them, they never have properties
			try {
				$class = new Reflection_Class($component);
			}
			catch (ReflectionException $exception) {
				continue;
			}
			foreach ($class->getProperties([]) as $property) {
				// this test would be useless if getProperties([]) works well, but we got trait properties
				if ($property->getDeclaringTraitName() === $component) {
					$property_names[$property->name] = $property->name;
				}
			}
		}
		return array_unique($property_names);
	}

	//-------------------------------------------------------------------------- exhaustiveClassFiles
	/**
	 * TODO multiple files may be taken. Beware of inherited classes files too. Beware of ordering
	 *
	 * @param $class_name string
	 * @return string[] file names
	 */
	protected function exhaustiveClassFiles($class_name)
	{
		$files     = [];
		$file_name = strtolower(str_replace(BS, SL, Namespaces::applicationNamespace($class_name)))
			. SL . 'exhaustive.yaml';
		if (is_file($file_name)) {
			$files[] = $file_name;
		}
		return $files;
	}

	//-------------------------------------------------------------------------- exhaustiveValueLists
	/**
	 * @param $class_name      string
	 * @param $annotation_name string
	 * @return array string[][] string $value[string $file_name][]
	 */
	protected function exhaustiveValueLists($class_name, $annotation_name)
	{
		$lists       = [];
		$class_names = $this->classComponents($class_name);
		foreach ($class_names as $class_name) {
			$file_names = $this->exhaustiveClassFiles($class_name);
			foreach ($file_names as $file_name) {
				$data = yaml_parse_file($file_name);
				if (isset($data[$class_name][$annotation_name])) {
					$lists[] = $data[$class_name][$annotation_name];
				}
			}
		}
		return $lists;
	}

	//------------------------------------------------------------------------------- parentClassName
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return string
	 */
	protected function parentClassName($class_name)
	{
		/** @noinspection PhpUnhandledExceptionInspection must exist */
		$class = new Reflection_Class($class_name);
		if ($parent_class_name = $class->getParentClassName()) {
			if (Class_Builder::isBuilt($parent_class_name)) {
				$parent_class_name = Framework\Builder::current()->sourceClassName($parent_class_name);
			}
		}
		return $parent_class_name;
	}

}
