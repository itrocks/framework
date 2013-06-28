<?php
namespace SAF\Framework;

/**
 * The default output controller will be called if no output controller is available for a class
 */
class Default_Output_Controller extends Output_Controller
{

	//----------------------------------------------------------------------------- getGeneralButtons
	/**
	 * @param $object object|string object or class name
	 * @param $parameters string[] parameters
	 * @return Button[]
	 */
	protected function getGeneralButtons($object, $parameters)
	{
		return array(
			new Button("Close",
				View::link(Names::classToSet(get_class($object))),
				"close",
				array(Color::of("close"), "#main")
			),
			new Button("Edit",
				View::link($object, "edit"),
				"edit",
				array(Color::of("green"), "#main")
			),
			new Button("Print",
				View::link($object, "print"),
				"print",
				array(Color::of("blue"), "#main", "sub_buttons" => array(
					new Button(
						"Models",
						View::link(
							'SAF\Framework\Print_Models', "list", Namespaces::shortClassName(get_class($object))
						),
						"models",
						"#main"
					)
				))
			)
		);
	}

}
