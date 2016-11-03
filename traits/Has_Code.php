<?php
namespace ITRocks\Framework\Traits;

/**
 * For all classes having a code made of true ascii string ([-a-zA-Z0-9_])
 *
 */
trait Has_Code
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @setter
	 * @var string
	 */
	public $code;

	//--------------------------------------------------------------------------------------- setCode
	/** @noinspection PhpUnusedPrivateMethodInspection @setter */
	/**
	 * Set identifier unique
	 * @param $code string
	 */
	private function setCode($code = null)
	{
		$this->code = preg_replace('/(\s+)/', '-', trim(preg_replace('/[^-a-zA-Z0-9_\s]/', '',
			trim(strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $code)))))
		);
		if (is_null($this->code)) {
			$this->code = '';
		}
	}

}
