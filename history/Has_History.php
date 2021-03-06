<?php
namespace ITRocks\Framework\History;

/**
 * Use it with classes which you want to save modifications history
 *
 * @after_write \ITRocks\Framework\History\Writer::afterWrite
 * @before_write \ITRocks\Framework\History\Writer::beforeWrite
 * @todo Writer:: should be enough, but the name resolving comes from the final class namespace
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
