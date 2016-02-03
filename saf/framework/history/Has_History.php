<?php
namespace SAF\Framework\History;

/**
 * Use it with classes which you want to save modifications history
 *
 * @after_write \SAF\Framework\History\Writer::afterWrite
 * @before_write \SAF\Framework\History\Writer::beforeWrite
 * @todo Writer:: should be enouth, but the name resolving comes from the final class namespace
 * instead of using the interface one.
 */
interface Has_History
{

	//--------------------------------------------------------------------------- getHistoryClassName
	/**
	 * @return string
	 */
	public function getHistoryClassName();

}
