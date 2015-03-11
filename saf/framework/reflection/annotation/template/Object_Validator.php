<?php
namespace SAF\Framework\Reflection\Annotation\Template;

/**
 * An object validator annotation implements a validate() method to validate an object using
 * interactions between its properties (this is not a property validator).
 * This is used by the Validator plugin and widget.
 *
 * This is a class context annotation, as we may need the class information to validate the object.
 */
interface Object_Validator extends Class_Context_Annotation, Validator
{

}
