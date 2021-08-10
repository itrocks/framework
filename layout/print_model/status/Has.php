<?php
namespace ITRocks\Framework\Layout\Print_Model\Status;

use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Layout\Print_Model\Status;

/**
 * @before_update setStatusToCustom
 * @extends Print_Model
 * @see Print_Model
 */
trait Has
{

	//--------------------------------------------------------------------------------------- $status
	/**
	 * @user readonly
	 * @values Status::const
	 * @var string
	 */
	public $status = Status::CUSTOM;

	//----------------------------------------------------------------------------- setStatusToCustom
	/**
	 * Sets status to custom each time the print model is updated
	 *
	 * @noinspection PhpUnused @before_update
	 */
	public function setStatusToCustom()
	{
		if ($this->status === Status::CUSTOM) {
			return;
		}
		$this->status = Status::CUSTOM;
	}

}
