<?php
namespace ITRocks\Framework\Layout\Print_Model\Status;

use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Layout\Print_Model\Status;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * @before_update setStatusToCustom
 */
#[Extend(Print_Model::class)]
trait Has
{

	//--------------------------------------------------------------------------------------- $status
	/** @user readonly */
	#[Values(Status::class)]
	public string $status = Status::CUSTOM;

	//----------------------------------------------------------------------------- setStatusToCustom
	/**
	 * Sets status to custom each time the print model is updated
	 *
	 * @noinspection PhpUnused @before_update
	 */
	public function setStatusToCustom() : void
	{
		if ($this->status === Status::CUSTOM) {
			return;
		}
		$this->status = Status::CUSTOM;
	}

}
