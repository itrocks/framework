<?php
namespace ITRocks\Framework\View\Html\Template;

/**
 * The loop manager
 */
class Loop
{

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @var string
	 */
	public string $content;

	//-------------------------------------------------------------------------------------- $counter
	/**
	 * @var integer
	 */
	public int $counter = 0;

	//-------------------------------------------------------------------------------------- $element
	/**
	 * @var mixed
	 */
	public mixed $element;

	//--------------------------------------------------------------------------------- $else_content
	/**
	 * @var string
	 */
	public string $else_content;

	//---------------------------------------------------------------------------------------- $first
	/**
	 * @var boolean
	 */
	public bool $first = true;

	//------------------------------------------------------------------------------ $force_condition
	/**
	 * @var boolean
	 */
	public bool $force_condition = false;

	//------------------------------------------------------------------------------- $force_equality
	/**
	 * @var boolean
	 */
	public bool $force_equality = false;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * @var integer|string|null
	 */
	public int|string|null $from;

	//------------------------------------------------------------------------------------- $has_expr
	/**
	 * @var ?string
	 */
	public ?string $has_expr = null;

	//--------------------------------------------------------------------------------------- $has_id
	/**
	 * @var boolean
	 */
	public bool $has_id = false;

	//------------------------------------------------------------------------------------------ $key
	/**
	 * @var integer|string
	 */
	public int|string $key;

	//------------------------------------------------------------------------------------ $separator
	/**
	 * @var string
	 */
	public string $separator;

	//------------------------------------------------------------------------------------------- $to
	/**
	 * @var int|string|null
	 */
	public int|string|null $to;

	//-------------------------------------------------------------------------------------- $use_end
	/**
	 * If true, the template uses <!--end--> instead of <!--start-condition-->
	 *
	 * @var boolean
	 */
	public bool $use_end;

	//------------------------------------------------------------------------------------- $var_name
	/**
	 * @var string
	 */
	public string $var_name;

}
