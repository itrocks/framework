<?php
namespace SAF\Framework\Traits;

/**
 * For all classes having a code made of true ascii string ([a-zA-Z0-9_])
 *
 * @before_write transformToCode
 * @todo HIGHEST Should not it be $code's @setter, instead of @before_write ?
 */
trait Has_Code
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public $code;

	//------------------------------------------------------------------------------- transformToCode
	/**
	 * Set identifier unique
	 */
	public function transformToCode()
	{
		if (isset($this->code)) {
			$this->code = preg_replace(
				['/(\s+)/', '/' . Q . '/'],
				['-', ''],
				trim(strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $this->code)))
			);
			if (is_null($this->code)) {
				$this->code = '';
			}
		}
	}

}
