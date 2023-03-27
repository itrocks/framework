<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\PHP;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\Documented_Type_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Describes the data type of the property.
 *
 * Only values of that type should be stored into the property.
 * If no @var ... annotation is set, the default property is guessed knowing its default value.
 * It is highly recommended to set the @var ... annotation for all business classes properties.
 */
class Var_Annotation extends Documented_Type_Annotation implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'var';

	//----------------------------------------------------------------------------------- __construct
	public function __construct(?string $value, Reflection_Property $reflection_property)
	{
		if (!$value) {
			$value = $this->fromRealType($reflection_property);
		}
		parent::__construct($value);
		if (!$this->value) {
			$types       = $reflection_property->getDeclaringClass()->getDefaultProperties();
			$this->value = gettype($types[$reflection_property->getName()]);
		}
	}

	//---------------------------------------------------------------------------------- fromRealType
	protected function fromRealType(Reflection_Property $reflection_property) : ?string
	{
		if ($reflection_property instanceof Reflection\Reflection_Property) {
			$types = $reflection_property->getTypeOrigin();
			if (
				($types instanceof ReflectionIntersectionType)
				|| ($types instanceof ReflectionUnionType)
			) {
				$value = [];
				foreach ($types->getTypes() as $type) {
					$type = $type->getName();
					if (ctype_upper($type[0])) {
						$type = BS . $type;
					}
					$value[] = $type;
				}
				$value = join('|', $value);
			}
			elseif ($types instanceof ReflectionNamedType) {
				$value = $types->getName();
			}
			else {
				return null;
			}
			if ($types->allowsNull()) {
				$value .= '|null';
			}
		}
		elseif ($reflection_property instanceof PHP\Reflection_Property) {
			$value = $reflection_property->type;
			if (str_starts_with($value, '?')) {
				$value = substr($value, 1) . '|null';
			}
		}
		else {
			return null;
		}
		$value = explode('|', $value);
		foreach ($value as &$part) {
			switch ($part) {
				case 'bool': $part = 'boolean'; break;
				case 'int':  $part = 'integer'; break;
				default:
					if (ctype_upper($part[0]) && ($reflection_property instanceof PHP\Reflection_Property)) {
						$part = $reflection_property->class->fullClassName($part);
					}
			}
		}
		return join('|', $value);
	}

	//--------------------------------------------------------------------------------------- getType
	public function getType() : Type
	{
		return new Type($this->value . ($this->documentation ? ('|' . $this->documentation) : ''));
	}

}
