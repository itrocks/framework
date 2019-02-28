<?php
namespace ITRocks\Framework\PHP\Compiler;

use ITRocks\Framework\PHP\Reflection_Source;

/**
 * More sources : tools to add more sources
 */
class More_Sources
{

	//---------------------------------------------------------------------------------------- $added
	/**
	 * @var Reflection_Source[]
	 */
	public $added = [];

	//-------------------------------------------------------------------------------------- $sources
	/**
	 * @var Reflection_Source[]
	 */
	public $sources;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $sources      Reflection_Source[]
	 * @param $more_sources Reflection_Source[]
	 */
	public function __construct(array& $sources, array& $more_sources = null)
	{
		$this->sources =& $sources;
		if ($more_sources) {
			$this->added =& $more_sources;
		}
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * @param $source         Reflection_Source
	 * @param $class_name     string class name of the first class name in the source
	 * @param $file_name      string if set, name of the file in case o class name being null
	 * @param $add_to_sources boolean if true, add source to $this->sources too
	 */
	public function add(
		Reflection_Source $source, $class_name, $file_name = null, $add_to_sources = false
	) {
		$add_key               = ($class_name ?: $file_name);
		$this->added[$add_key] = $source;
		if ($add_to_sources) {
			$this->sources[$add_key] = $source;
		}
	}

}
