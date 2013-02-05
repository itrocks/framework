<?php
namespace SAF\Framework;

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
