<?php
namespace SAF\Framework;

/**
 * Interface for classes that have doc comments.
 *
 * Common classes having doc comments are Reflection_Class, Reflection_Property and Reflection_Method
 */
interface Has_Doc_Comment
{

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * Gets doc comment
	 *
	 * @param $get_parents boolean
	 * @return string
	 */
	public function getDocComment($get_parents = false);

}
