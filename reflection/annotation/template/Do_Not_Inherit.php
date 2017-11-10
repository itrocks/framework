<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

/**
 * When an annotation class implements this interface, the annotation parser will not inherit
 * annotations from parents classes, used traits, or implemented interfaces :
 * We only keep the value set in this class doc-comment
 */
interface Do_Not_Inherit
{

}
