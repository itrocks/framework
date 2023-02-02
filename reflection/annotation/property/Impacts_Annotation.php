<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;

/**
 * The @impacts annotation allow to declare which properties will be impacted by each modification
 * of the property value (e.g. using #Setter)
 */
class Impacts_Annotation extends List_Annotation implements Multiple_Annotation
{

}
