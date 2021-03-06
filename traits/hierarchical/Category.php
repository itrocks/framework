<?php
namespace ITRocks\Framework\Traits\Hierarchical;

use ITRocks\Framework\Traits\Hierarchical;

/**
 * Hierarchical categories are common things
 *
 * @after_write writeSubCategories
 */
trait Category
{
	use Hierarchical;

	//------------------------------------------------------------------------------- $sub_categories
	/**
	 * @foreign super_category
	 * @getter getSubCategories
	 * @var static[]
	 */
	public $sub_categories;

	//------------------------------------------------------------------------------- $super_category
	/**
	 * @foreign sub_category
	 * @link Object
	 * @var static
	 */
	public $super_category;

	//--------------------------------------------------------------------------- getAllSubCategories
	/**
	 * Gets all sub categories from all sub_categories property (recursively)
	 *
	 * @return static[]
	 */
	public function getAllSubCategories()
	{
		return $this->getAllSub('sub_categories', 'super_category');
	}

	//------------------------------------------------------------------------- getAllSuperCategories
	/**
	 * Gets all parent categories from the super_category property (recursively)
	 *
	 * The resulting list will begin with the top category, then descends until the super-category
	 *
	 * @return static[]
	 */
	public function getAllSuperCategories()
	{
		return $this->getAllSuper('super_category');
	}

	//------------------------------------------------------------------------------ getSubCategories
	/**
	 * @return static[]
	 */
	protected function getSubCategories()
	{
		return $this->readSub('sub_categories', 'super_category');
	}

	//-------------------------------------------------------------------------------- getTopCategory
	/**
	 * Gets top category
	 *
	 * @return static
	 */
	public function getTopCategory()
	{
		return $this->getTop('super_category');
	}

	//---------------------------------------------------------------------------- writeSubCategories
	/**
	 * Write sub categories : updates super_category for all sub category
	 */
	public function writeSubCategories()
	{
		$this->writeSub('sub_categories', 'super_category');
	}

}
