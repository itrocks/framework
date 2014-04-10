<?php
namespace SAF\RAD;

/**
 * A layer control can contain other grouped controls
 */
class Layer extends Control
{

	/**
	 * @var Control;[]
	 */
	public $content = [];

}
