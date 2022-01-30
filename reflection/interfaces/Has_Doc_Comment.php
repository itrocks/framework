<?php
namespace ITRocks\Framework\Reflection\Interfaces;

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
	 * @param $flags integer[]|null T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string
	 */
	public function getDocComment(array|null $flags = []) : string;

}
