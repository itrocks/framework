<?php
namespace SAF\Framework\Reflection\Annotation\Template;

/**
 * A property validator annotation implements a validate() method to validate a property value into
 * an object context.
 * This is used by the Validator plugin and widget.
 *
 * This is a property context annotation, as we need the property information to validate it
 */
interface Property_Validator extends Property_Context_Annotation, Validator
{

}
