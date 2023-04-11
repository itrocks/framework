<?php
namespace ITRocks\Framework\Feature\History;

/**
 * Use it with classes which you want to save modifications history
 *
 * @after_write \ITRocks\Framework\Feature\History\Writer::afterWrite
 * @before_write \ITRocks\Framework\Feature\History\Writer::beforeWrite
 * @see Writer::afterWrite
 * @see Writer::beforeWrite
 * @todo Writer:: should be enough, but the name resolving comes from the final class namespace
 * instead of using the interface one.
 */
interface Has_History
{

	//--------------------------------------------------------------------------- getHistoryClassName
	public function getHistoryClassName() : string;

}
