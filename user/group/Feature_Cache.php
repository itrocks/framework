<?php
namespace ITRocks\Framework\User\Group;

use ITRocks\Framework\Application;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Annotation\Property\Feature_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;

/**
 * This processing class enables to store the list of features in cache :
 * - scan all the projects tree for business objects and yaml feature files
 *
 * The features cache is the Features set, stored into Dao's data link.
 */
class Feature_Cache
{

	//------------------------------------------------------------------------------- INVALIDATE_FILE
	const INVALIDATE_FILE = 'cache/user_group_feature_cache';

	//------------------------------------------------------------------------------------ invalidate
	/**
	 * Returns the complete files to scan list if the cache is invalidated
	 * If it returns an empty array, then the cache is still valid and does not need to be reset
	 *
	 * @param $last_time integer
	 * @return string[] php/yaml files paths
	 */
	public function invalidate(int $last_time) : array
	{
		/** @var $files_cache string[] ['file/path' => 'last-hash'] The list of cached hashes */
		$files_cache = [];
		if (file_exists(static::INVALIDATE_FILE)) {
			foreach (file(static::INVALIDATE_FILE, FILE_IGNORE_NEW_LINES) as $line) {
				[$name, $hash]      = explode(';', $line);
				$files_cache[$name] = $hash;
			}
		}
		else {
			$last_time = 0;
		}
		$application   = Application::current();
		$files         = $application->include_path->getSourceFiles();
		$invalidated   = false;
		$removed_files = $files_cache;
		foreach ($files as $key => $filename) {
			$hash = null;
			if (str_ends_with($filename, '.php')) {
				unset($removed_files[$filename]);
				if (filemtime($filename) >= $last_time) {
					$hash = md5(serialize($this->scanPhpFile($filename)));
				}
			}
			elseif (
				str_ends_with($filename, '.yaml')
				&& !str_starts_with($filename, 'itrocks/framework/user/group/defaults/')
				&& !str_ends_with($filename, '/exhaustive.yaml')
			) {
				unset($removed_files[$filename]);
				if (filemtime($filename) >= $last_time) {
					$hash = md5(file_get_contents($filename));
				}
			}
			else {
				unset($files[$key]);
			}
			if (isset($hash) && ($hash !== ($files_cache[$filename] ?? null))) {
				$files_cache[$filename] = $hash;
				$invalidated            = true;
			}
		}
		if ($removed_files) {
			$invalidated = true;
			foreach (array_keys($removed_files) as $filename) {
				unset($files_cache[$filename]);
			}
		}
		if ($invalidated) {
			foreach ($files_cache as $filename => $hash) {
				$files_cache[$filename] = $filename . ';' . $hash;
			}
			file_put_contents(static::INVALIDATE_FILE, join(LF, $files_cache));
		}
		return $invalidated ? $files : []; // // //
	}

	//-------------------------------------------------------------------------------- isFeatureClass
	/**
	 * Returns true if the file class header buffer reveals a @feature class
	 *
	 * The class source code must have been correctly set to @feature and correctly indented.
	 *
	 * @param $buffer string A file class header buffer : read by getClassHeader()
	 * @return boolean true if is a business class
	 */
	private function isFeatureClass(string $buffer) : bool
	{
		return str_contains($buffer, '{') && str_contains($buffer, '* @feature');
	}

	//----------------------------------------------------------------------------------- saveToCache
	/**
	 * Save updated features to cache
	 *
	 * TODO LOW This will crash if a feature linked to a user group is removed. Check this out !
	 *
	 * @param $features Feature[]
	 */
	public function saveToCache(array $features) : void
	{
		Dao::begin();
		$old_features = Dao::readAll(Feature::class, Dao::key('path'));
		foreach ($features as $feature) {
			if (isset($old_features[$feature->path])) {
				$old_feature = $old_features[$feature->path];
				if ($feature->name !== $old_feature->name) {
					$old_feature->name = $feature->name;
					Dao::write($old_feature, Dao::only('name'));
				}
				unset($old_features[$feature->path]);
			}
			else {
				Dao::write($feature);
			}
		}
		Dao::commit();
		Dao::begin();
		foreach ($old_features as $old_feature) {
			Dao::delete($old_feature);
		}
		Dao::commit();
	}

	//------------------------------------------------------------------------------------- scanClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @return Feature[]
	 */
	private function scanClass(string $class_name) : array
	{
		/** @noinspection PhpUnhandledExceptionInspection class must be valid */
		$class               = new Reflection_Class($class_name);
		$feature_annotations = $class->getAnnotations('feature');

		foreach ($feature_annotations as $annotation) {
			if ($annotation->value === true) {
				$implicit = true;
			}
			elseif (!ctype_upper(substr($annotation->value, 0, 1))) {
				$explicit = true;
			}
		}

		/** @var $features Feature[] */
		$features = [];

		// apply implicit features
		if (isset($implicit)) {
			foreach (Feature::getImplicitFeatures() as $feature) {
				$path = str_replace(BS, SL, $class_name) . SL . $feature;
				$features[$path] = new Feature($path);
			}
		}

		// apply explicit features
		if (isset($explicit)) {
			foreach ($feature_annotations as $annotation) {
				if (($annotation->value === true) || ctype_upper(substr($annotation->value, 0, 1))) {
					continue;
				}
				$path = lParse($annotation->value, SP);
				$name = rParse($annotation->value, SP);
				if (!str_contains($path, SL)) {
					$path = str_replace(BS, SL, $class_name) . SL . $path;
				}
				$features[$path] = new Feature($path, $name);
			}
		}

		// scan properties
		foreach ($class->getProperties([T_EXTENDS, T_USE]) as $property) {
			/** @var $annotations Feature_Annotation[] */
			$annotations = $property->getListAnnotations(Feature_Annotation::ANNOTATION);
			foreach ($annotations as $annotation) {
				$feature = new Feature($annotation->path);
				$options = $annotation->values();
				$feature->features = ['override' => new Low_Level_Feature('override', $options)];
				$features[$annotation->path] = $feature;
			}
		}

		return $features;
	}

	//---------------------------------------------------------------------------------- scanFeatures
	/**
	 * Scan the application source files for feature classes that may give us final user features
	 *
	 * @param $files string[] file paths read by invalidate()
	 * @return Feature[]
	 * @see invalidate
	 */
	public function scanFeatures(array $files) : array
	{
		/** @var $php_files_features  Feature[] */
		/** @var $yaml_files_features Feature[] */
		$php_files_features  = [];
		$yaml_files_features = [];
		foreach ($files as $filename) {
			if (str_ends_with($filename, '.php')) {
				$php_files_features = array_merge($php_files_features, $this->scanPhpFile($filename));
			}
			else {
				$yaml_files_features = array_merge($yaml_files_features, $this->scanYamlFile($filename));
			}
		}
		return array_merge($php_files_features, $yaml_files_features);
	}

	//----------------------------------------------------------------------------------- scanPhpFile
	/**
	 * @param $filename string
	 * @return Feature[]
	 */
	private function scanPhpFile(string $filename) : array
	{
		/** @var $features Feature[] */
		$features = [];
		$buffer   = file_get_contents($filename);
		if ($this->isFeatureClass($buffer)) {
			$class_name = Names::fileToClass($filename);
			$features   = $this->scanClass($class_name);
		}
		return $features;
	}

	//---------------------------------------------------------------------------------- scanYamlFile
	/**
	 * @param $filename string
	 * @return Feature[]
	 */
	private function scanYamlFile(string $filename) : array
	{
		/** @var $features Feature[] */
		$features = [];
		foreach (Yaml::fromFile($filename) as $path => $yaml) {
			$class_name = Names::pathToClass(lLastParse($path, SL));
			if (
				interface_exists($class_name) || trait_exists($class_name) || !class_exists($class_name)
			) {
				continue;
			}
			$features[$path] = new Feature($path);
			$features[$path]->yaml = $yaml;
		}
		return $features;
	}

}
