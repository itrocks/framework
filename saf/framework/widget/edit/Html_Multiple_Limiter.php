<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Dao\Option\Count;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Option;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Reflection\Reflection_Property_Value;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\View\Html\Builder\Collection;
use SAF\Framework\View\Html\Dom\Anchor;
use SAF\Framework\View\Html\Dom\Table;
use SAF\Framework\View\Html\Dom\Table\Header_Cell;
use SAF\Framework\View\Html\Dom\Table\Standard_Cell;

/**
 * This plugin limits the count of elements a Collection or a Map can display on an edit form
 */
class Html_Multiple_Limiter implements Registerable
{

	//---------------------------------------------------------------------------------------- $count
	/**
	 * @var Count
	 */
	private $count;

	//---------------------------------------------------------------------------------- $in_multiple
	/**
	 * @values '', 'search', 'build'
	 * @var string
	 */
	private $in_multiple = '';

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property_Value
	 */
	private $property;

	//----------------------------------------------------------------- afterHtmlBuilderMultipleBuild
	/**
	 * @param $result Table
	 */
	public function afterHtmlBuilderMultipleBuild(Table $result)
	{
		if ($this->in_multiple == 'build') {
			$table = $result;
			$length = count($table->body->rows) - 1;
			if ($this->count->count > $length) {
				// vertical scrollbar
				$vertical_scroll_bar = new Standard_Cell();
				$vertical_scroll_bar->addClass('vertical');
				$vertical_scroll_bar->addClass('scrollbar');
				$vertical_scroll_bar->setAttribute('rowspan', 1000000);
				$vertical_scroll_bar->setData('start', 0);
				$vertical_scroll_bar->setData('length', $length);
				$vertical_scroll_bar->setData('total', $this->count->count);
				$link = '/Html_Edit_Multiple/output/'
					. Namespaces::shortClassName($this->property->getDeclaringClass())
					. SL . Dao::getObjectIdentifier($this->property->getObject())
					. SL . $this->property->name
					. SL . '?move=';
				$up       = new Anchor($link . 'up');   $up->addClass('up');
				$position = new Anchor($link . 1);      $position->addClass('position');
				$down     = new Anchor($link . 'down'); $down->addClass('down');
				$vertical_scroll_bar->setContent($up . $position . $down);
				// add vertical scrollbar cells to multiple (collection or map) table
				$table->head->rows[0]->addCell(new Header_Cell(), 0);
				$table->body->rows[0]->addCell($vertical_scroll_bar, 0);
			}
			$this->in_multiple = '';
		}
	}

	//------------------------------------------------------------- beforeHtmlEditTemplateParseMethod
	/**
	 * Activate plugin before HTML method parsing of a Reflection_Property_Value named 'value'
	 *
	 * @param $object        object
	 * @param $property_name string
	 */
	public function beforeHtmlEditTemplateParseMethod($object, $property_name)
	{
		/** @noinspection PhpUndefinedMethodInspection */
		if (
			($object instanceof Reflection_Property_Value)
			&& ($property_name === 'value')
			&& ($object->getAnnotation('link')->isMultiple())
		) {
			$this->in_multiple = 'search';
			$this->property = $object;
		}
		else {
			$this->in_multiple = '';
		}
	}

	//--------------------------------------------------------------------------- beforeMysqlLinkRead
	/**
	 * If plugin is activated, limits result count of Mysql::search()
	 *
	 * This results on an incomplete object, but the object is used for editing form only so we don't
	 * care.
	 *
	 * @param $options Option[] some options for advanced search
	 */
	public function beforeMysqlLinkSearch(&$options)
	{
		if ($this->in_multiple === 'search') {
			if (is_object($options)) {
				$options = [$options];
			}
			$options[] = Dao::limit(10);
			$options[] = $this->count = new Count();
			$this->in_multiple = 'build';
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod(
			[Html_Template::class, 'parseMethod'],
			[$this, 'beforeHtmlEditTemplateParseMethod']
		);
		$aop->beforeMethod(
			[Link::class, 'search'],
			[$this, 'beforeMysqlLinkSearch']
		);
		$aop->afterMethod(
			[Collection::class, 'build'],
			[$this, 'afterHtmlBuilderMultipleBuild']
		);
		$aop->afterMethod(
			[Html_Builder_Map::class, 'build'],
			[$this, 'afterHtmlBuilderMultipleBuild']
		);
	}

}
