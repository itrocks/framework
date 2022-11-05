<?php
namespace ITRocks\Framework\Objects\Note;

use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Component\Button\Align;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Edit;
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

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'notes';

	//-------------------------------------------------------------------------------- addNotesButton
	/**
	 * @param $object object
	 * @param $result Button[]
	 */
	public function addNotesButton(object $object, array &$result) : void
	{
		if (!Dao::getObjectIdentifier($object) || isset($result[static::FEATURE])) {
			return;
		}

		$buttons =& $result;
		$count   =  Dao::count(['object' => $object], Note::class) ?: null;

		$buttons[static::FEATURE] = new Button(
			'Notes',
			View::link(Note::class, Summary\Controller::FEATURE, [$object]),
			static::FEATURE,
			['#notes-summary', Align::RIGHT, Button::DATA => ['count' => $count]]
		);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$register->aop->afterMethod(
			[Edit\Controller::class, 'getGeneralButtons'], [$this, 'addNotesButton']
		);
		$register->aop->afterMethod(
			[Output\Controller::class, 'getGeneralButtons'], [$this, 'addNotesButton']
		);
	}

}
