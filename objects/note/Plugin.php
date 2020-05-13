<?php
namespace ITRocks\Framework\Objects\Note;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\Objects\Note;
use ITRocks\Framework\Objects\Note\Summary;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\View;

/**
 * Note plugin :
 *
 * - add a button to all edit / output views
 * - in case of access limitation, user will need a global access to notes
 *
 * @feature Notes for your objects
 * @priority lowest
 */
class Plugin implements Registerable
{

	//-------------------------------------------------------------------------------- addNotesButton
	/**
	 * @param $object object
	 * @param $result Button[]
	 */
	public function addNotesButton($object, array &$result)
	{
		$buttons =& $result;
		$count   =  Dao::count(['object' => $object], Note::class) ?: null;

		$buttons['notes'] = new Button(
			'Notes',
			View::link(Note::class, Summary\Controller::FEATURE, [$object]),
			'notes',
			['#notes-summary', Button::DATA => ['count' => $count]]
		);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Output\Controller::class, 'getGeneralButtons'], [$this, 'addNotesButton']
		);
	}

}
