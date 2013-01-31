<?php
namespace SAF\Framework;

class Acls_Group
{

	//-------------------------------------------------------------------------------------- $caption
	/**
	 * @var string
	 */
	public $caption;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 * @values template, software, module, object, feature, users_group, group, user,
	 */
	public $type;

	//--------------------------------------------------------------------------------------- $groups
	/**
	 * @contained
	 * @getter Aop::getCollection
	 * @var Acls_Link[]
	 */
	public $content;

	//--------------------------------------------------------------------------------------- $rights
	/**
	 * @contained
	 * @getter Aop::getCollection
	 * @var Acls_Right[]
	 */
	public $rights;

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add an Acls_Link to $content or an Acls_Right to rights
	 *
	 * @param $object Acls_Link|Acls_Right
	 * @return Acls_Group
	 */
	public function add($object)
	{
		array_push(($object instanceof Acls_Right) ? $this->rights : $this->content, $object);
		return $this;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an Acls_Link from $content, or an Acls_Right from rights
	 *
	 * @param $object Acls_Link|Acls_Right
	 */
	public function remove($object)
	{
		if ($object instanceof Acls_Right) $collection =& $this->rights;
		else                              $collection =& $this->content;
		foreach ($collection as $key => $collection_object) {
			if ($object == $collection_object) {
				unset($collection[$key]);
			}
		}
	}

}
