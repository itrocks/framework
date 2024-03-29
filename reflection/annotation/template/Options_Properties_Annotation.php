<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

/**
 * Common code for @annotation [option1] [option2] property1[, property2[, etc]]
 *
 * When you override this class :
 * - create one constant for each option reserved word
 * - list these options into overridden RESERVED_WORDS : string[]
 * - list default options into DEFAULT_OPTIONS
 * - some options may exclude each other : regroup them into EXCLUDED_OTIONS group string[][]
 * - don't forget to set your ANNOTATION constant
 *
 * @example Used by Property\Integrated_Annotation
 */
class Options_Properties_Annotation extends List_Annotation
{

	//------------------------------------------------------------------------------- DEFAULT_OPTIONS
	const DEFAULT_OPTIONS = [];

	//------------------------------------------------------------------------------ EXCLUDED_OPTIONS
	const EXCLUDED_OPTIONS = [];

	//-------------------------------------------------------------------------------- RESERVED_WORDS
	const RESERVED_WORDS = [];

	//----------------------------------------------------------------------------------- $properties
	/** @var string[] The list of property paths */
	public array $properties = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Default value is 'full' when no value is given
	 *
	 * Can be empty (eq full) contain 'full', 'simple', 'block' (implicitly 'simple')
	 */
	public function __construct(?string $value)
	{
		if (!isset($value)) {
			parent::__construct($value);
			return;
		}
		if (!$value) {
			parent::__construct(join(',', static::DEFAULT_OPTIONS));
			return;
		}
		$excluded = [];
		$values   = [];
		foreach (explode(SP, $value) AS $element) {
			if (
				str_contains($element, ',')
				|| in_array($element, $excluded)
				|| in_array($element, $values)
				|| !in_array($element, static::RESERVED_WORDS)
			) {
				foreach (explode(',', $element) as $sub_element) {
					if (trim($sub_element)) {
						$this->properties[] = trim($sub_element);
					}
				}
			}
			else {
				$element  = trim($element);
				$values[] = $element;
				foreach (static::EXCLUDED_OPTIONS as $excluded_options) {
					if (in_array($element, $excluded_options)) {
						$excluded += $excluded_options;
					}
				}
			}
		}
		$value = join(',', $values);
		parent::__construct($value);
	}

	//----------------------------------------------------------------------------------- addProperty
	/** Adds a property path */
	public function addProperty(string $property) : void
	{
		if (!$this->hasProperty($property)) {
			$this->properties[] = $property;
		}
	}

	//----------------------------------------------------------------------------------- hasProperty
	/** Returns true if the property paths list contains this property path */
	public function hasProperty(string $property) : bool
	{
		return in_array($property, $this->properties);
	}

	//-------------------------------------------------------------------------------- removeProperty
	/** Remove property path and return true if it was here and removed, false if it was not found */
	public function removeProperty(string $property) : bool
	{
		$key = array_search($property, $this->properties);
		if ($key !== false) {
			unset($this->properties[$key]);
			return true;
		}
		return false;
	}

}
