<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Locale\Translation;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;

/**
 * The search array builder builds search arrays from properties paths and search phrases
 */
class Search_Array_Builder
{

	//---------------------------------------------------------------------------------------- $class
	protected Reflection_Class $class;

	//------------------------------------------------------------------------------------------ $and
	/** And separator */
	public string $and = SP;

	//------------------------------------------------------------------------------------------- $or
	/** Or separator */
	public string $or = ',';

	//------------------------------------------------------------------------------- $property_types
	/** @var Type[] Key is string */
	protected array $property_types;

	//----------------------------------------------------------------------------------------- build
	public function build(
		string $property_name, string $search_phrase, string $prepend = '', string $append = ''
	) : array|string
	{
		$search_phrase = trim($search_phrase);
		// search phrase contains OR
		if (str_contains($search_phrase, $this->or)) {
			$result = [];
			foreach (explode($this->or, $search_phrase) as $search) {
				$sub_result = $this->build('', $search, $prepend, $append);
				if (is_string($sub_result) || (count($sub_result) > 1)) {
					$result[$property_name][] = $sub_result;
				}
				elseif (isset($result[$property_name])) {
					$result[$property_name] = array_merge($result[$property_name], $sub_result);
				}
				else {
					$result[$property_name] = $sub_result;
				}
			}
			return $property_name ? $result : reset($result);
		}
		// search phrase contains AND
		elseif (str_contains($search_phrase, $this->and)) {
			$and = [];
			foreach (explode($this->and, $search_phrase) as $search) {
				$and[]   = $this->build('', $search, $prepend, $append);
				$prepend = '%';
			}
			$result[$property_name] = Func::andOp($and);
			return $property_name ? $result : reset($result);
		}
		// simple search phrase
		else {
			return $property_name
				? [$property_name => $prepend . $search_phrase . $append]
				: ($prepend . $search_phrase . $append);
		}
	}

	//--------------------------------------------------------------------------------- buildMultiple
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $property_names_or_class string[]|Reflection_Class
	 * @param $search_phrase           string
	 * @param $prepend                 string
	 * @param $append                  string
	 * @param $translated              array string[][] translations, by property name
	 * @return array|Logical
	 */
	public function buildMultiple(
		array|Reflection_Class $property_names_or_class, string $search_phrase, string $prepend = '',
		string $append = '', array $translated = []
	) : array|Logical
	{
		$search_phrase  = str_replace(['*', '?'], ['%', '_'], $search_phrase);
		if ($property_names_or_class instanceof Reflection_Class) {
			$this->class    = $property_names_or_class;
			$property_names = $this->classRepresentativeProperties($property_names_or_class);
			$translated = $this->buildWithReverseTranslation($property_names_or_class, $search_phrase);
			foreach ($property_names as $property_name) {
				/** @noinspection PhpUnhandledExceptionInspection known */
				$this->property_types[$property_name] = $this->class->getProperty($property_name)
					->getType();
			}
		}
		else {
			$property_names = $property_names_or_class;
		}
		// search phrase contains OR
		if (str_contains($search_phrase, $this->or)) {
			$or = [];
			foreach ($property_names as $property_name) {
				$search = ($this->property_types[$property_name]->isDateTime())
					? Loc::dateToIso($search_phrase)
					: $search_phrase;
				$or[$property_name] = $this->build('', $search, $prepend, $append);
			}
			$result = Func::orOp($or);
		}
		// search phrase contains AND
		elseif (str_contains($search_phrase, $this->and)) {
			$and = [];
			foreach (explode($this->and, $search_phrase) as $search) {
				$and[]   = $this->buildMultiple($property_names, $search, $prepend, $append, $translated);
				$prepend = '%';
			}
			$result = Func::andOp($and);
		}
		// simple search phrase
		else {
			$or = [];
			foreach ($property_names as $property_name) {
				$search = ($this->property_types[$property_name]->isDateTime())
					? Loc::dateToIso($search_phrase)
					: $search_phrase;
				$or[$property_name] = $prepend . $search . $append;
				if (isset($translated[$property_name])) {
					$or[$property_name] = Func::orOp([
						$or[$property_name], Func::in($translated[$property_name])
					]);
				}
			}
			$result = (count($or) > 1) ? Func::orOp($or) : $or;
		}
		return $result;
	}

	//------------------------------------------------------------------- buildWithReverseTranslation
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array $text string[$property_name][]
	 */
	protected function buildWithReverseTranslation(Reflection_Class $class, string $search) : array
	{
		$found = [];
		foreach ($this->classRepresentativeProperties($class) as $property_name) {
			if (!property_exists($class->name, $property_name)) {
				continue;
			}
			/** @noinspection PhpUnhandledExceptionInspection properties should be good */
			$property = new Reflection_Property($class->name, $property_name);
			$texts    = null;
			if ($values = (Values::of($property)?->values ?: [])) {
				$texts = Loc::rtr($search . '%', $class->name, $property_name, $values);
			}
			elseif (!is_null($translate = $property->getAnnotation('translate')->value)) {
				switch ($translate) {
					case 'common':
						$texts = Loc::rtr($search . '%', $class->name, $property_name);
						break;
					case '':
					case 'data':
						$property_name = $property->name;
						$filters = [
							'class_name'    => Builder::current()->sourceClassName($property->getFinalClassName()),
							'language.code' => Loc::language(),
							'property_name' => $property_name,
							'translation'   => $search . '%'
						];
						/** @var $translations Translation\Data[] */
						$translations = Dao::search($filters, Translation\Data::class);
						foreach ($translations as $translation) {
							$texts[] = $translation->object->$property_name;
						}
						break;
				}
			}
			if ($texts && ($texts !== Translator::TOO_MANY_RESULTS_MATCH_YOUR_INPUT)) {
				$found[$property_name] = is_array($texts) ? $texts : [$texts];
			}
		}
		return $found;
	}

	//----------------------------------------------------------------- classRepresentativeProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection #Representative and @var not verified at this stage
	 * @param $class   Reflection_Class
	 * @param $already string[] For recursion limits : already got classes
	 * @return string[]
	 */
	private function classRepresentativeProperties(Reflection_Class $class, array $already = [])
		: array
	{
		$property_names = Representative::of($class)->values;
		foreach ($property_names as $key => $property_name) {
			/** @noinspection PhpUnhandledExceptionInspection #Representative properties must be good */
			$property = str_contains($property_name, DOT)
				? new Reflection_Property($class->name, $property_name)
				: $class->getProperty($property_name);
			$type        = $property->getType();
			$type_string = $type->asString();
			if (!$type->isBasic()) {
				unset($property_names[$key]);
				if (!isset($already[$type_string])) {
					/** @noinspection PhpUnhandledExceptionInspection @var Type should be valid */
					$sub_class                 = new Reflection_Class($type_string);
					$sub_already               = $already;
					$sub_already[$type_string] = $type_string;
					foreach (
						$this->classRepresentativeProperties($sub_class, $sub_already) as $sub_property_name
					) {
						$property_names[] = $property_name . DOT . $sub_property_name;
					}
				}
			}
		}
		if (!$property_names && $class->isAbstract()) {
			$property_names = ['representative'];
		}
		return $property_names;
	}

}
