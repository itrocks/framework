<?php
namespace ITRocks\Framework\PHP\Dependency\Repository;

trait Classify
{

	//---------------------------------------------------------------------------------- TYPE_EXTENDS
	protected const TYPE_EXTENDS = [
		'class'      => ['class', 'class_type'],
		'dependency' => ['dependency', 'dependency_type'],
		'type'       => ['type_class', 'type_dependency']
	];

	//-------------------------------------------------------------------------------------- classify
	public function classify() : void
	{
		$this->by_file            = [];
		$this->by_class           = [];
		$this->by_class_type      = [];
		$this->by_dependency      = [];
		$this->by_dependency_type = [];

		$home_length = strlen($this->home) + 1;
		foreach ($this->references as $file_name => $references) {
			$file_name = substr($file_name, $home_length);
			if (!$this->refresh) {
				$this->loadAndFilter($file_name);
			}
			if (!$references) {
				$this->by_file[$file_name] = [];
			}
			foreach ($references as $reference) {
				[$class, $dependency, $type, $line] = $reference;
				if ($class !== '') {
					$this->by_file[$file_name]['class'][$class] = true;
					$this->by_class      [$class][$dependency][$type][$file_name][] = $line;
					$this->by_class_type [$class][$type][$dependency][$file_name][] = $line;
				}
				if ($dependency !== '') {
					$this->by_file[$file_name]['dependency'][$dependency] = true;
					$this->by_dependency      [$dependency][$class][$type][$file_name][] = $line;
					$this->by_dependency_type [$dependency][$type][$class][$file_name][] = $line;
				}
				$this->by_file[$file_name]['type'][$type] = true;
				$this->by_type_class      [$type][$class][$dependency][$file_name][] = $line;
				$this->by_type_dependency [$type][$dependency][$class][$file_name][] = $line;
			}
			foreach ($this->by_file[$file_name] as &$values) {
				$values = array_keys($values);
			}
		}
	}

	//--------------------------------------------------------------------------------- loadAndFilter
	protected function loadAndFilter(string $file_name) : void
	{
		if (!file_exists($cache_file_name = $this->cacheFileName($file_name, 'file'))) {
			return;
		}
		foreach (
			json_decode(file_get_contents($cache_file_name), JSON_OBJECT_AS_ARRAY) as $type => $values
		) {
			foreach (self::TYPE_EXTENDS[$type] as $type) {
				foreach ($values as $value) {
					// load
					$is_set = isset($this->{"by_$type"}[$value]);
					if (!$is_set && file_exists($cache_file_name = $this->cacheFileName($value, $type))) {
						$is_set = true;
						$this->{"by_$type"}[$value] = json_decode(
							file_get_contents($cache_file_name), JSON_OBJECT_AS_ARRAY
						);
					}
					if (!$is_set) {
						continue;
					}
					// filter
					$references =& $this->{"by_$type"}[$value];
					foreach ($references as $key => &$references1) {
						foreach ($references1 as $key1 => &$references2) {
							unset($references2[$file_name]);
							if (!$references2) {
								unset($references1[$key1]);
							}
						}
						if (!$references1) {
							unset($references[$key]);
						}
					}
				}
			}
		}
	}

}
