<?php
namespace SAF\Framework;

/**
 * @representative name
 */
class Acls_Group
{
	use Remover;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string
	 * @values template, software, module, object, feature, users_group, group, user,
	 */
	public $type;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * @component
	 * @getter getLinks
	 * @remover remove
	 * @var Acls_Link[]
	 */
	public $content;

	//--------------------------------------------------------------------------------------- $rights
	/**
	 * @component
	 * @getter getRights
	 * @remover remove
	 * @var Acls_Right[] key is the property key
	 */
	public $rights;

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds an Acls_Link, an Acl_Group or an Acl_Link
	 *
	 * @param $object         Acls_Right|string|Acls_Group|Acls_Link
	 * @param $priority_value string|integer value of acls right, or position of acls group in links
	 * @return Acls_Group
	 */
	public function add($object, $priority_value = null)
	{
		if (is_string($object)) {
			$value = isset($priority_value) ? $priority_value : true;
			if (isset($this->rights[$object])) {
				$this->rights[$object]->value = $value;
			}
			else {
				$this->rights[$object] = new Acls_Right(Acls_User::current()->group, $object, $value);
			}
		}
		elseif ($object instanceof Acls_Right) {
			if (isset($priority_value)) {
				$object->value = $priority_value;
			}
			$object->setParent($this);
			$this->rights[$object->key] = $object;
		}
		elseif ($object instanceof Acls_Group) {
			if (!isset($priority_value)) {
				$priority_value = 1;
			}
			$this->content[$object->name] = new Acls_Link($this, $object, $priority_value);
		}
		elseif ($object instanceof Acls_Link) {
			$object->setParent($this);
			$this->content[$object->content->name] = $object;
		}
		return $this;
	}

	//------------------------------------------------------------------------------ getContentGroups
	/**
	 * @return Acls_Group[]
	 */
	public function getContentGroups()
	{
		$groups = array();
		foreach ($this->getLinks() as $link) {
			$groups[$link->content->name] = $link->content;
		}
		return $groups;
	}

	//-------------------------------------------------------------------------------------- getLinks
	/**
	 * @return Acls_Link[]
	 */
	public function getLinks()
	{
		$links = isset($this->content) ? $this->content : null;
		if (!isset($links)) {
			$links = array();
			foreach (Getter::getCollection(null, __NAMESPACE__ . "\\Acls_Link", $this) as $link) {
				$links[$link->content->name] = $link;
			}
			$this->content = $links;
		}
		return $links;
	}

	//------------------------------------------------------------------------------------- getRights
	/**
	 * @return Acls_Right[]
	 */
	public function getRights()
	{
		$rights = isset($this->rights) ? $this->rights : null;
		if (!isset($rights)) {
			$rights = array();
			foreach (Getter::getCollection(null, __NAMESPACE__ . "\\Acls_Right", $this) as $right) {
				$rights[$right->key] = $right;
			}
			$this->rights = $rights;
		}
		return $rights;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Removes an Acls_Right, an Acls_Group or an Acls_Link
	 *
	 * @param $object string|Acls_Right|Acls_Group|Acls_Link
	 * @return Acls_Group
	 */
	public function remove($object)
	{
		if (is_string($object)) {
			unset($this->rights[$object]);
		}
		elseif ($object instanceof Acls_Right) {
			unset($this->rights[$object->key]);
		}
		elseif ($object instanceof Acls_Group) {
			unset($this->content[$object->name]);
		}
		elseif ($object instanceof Acls_Link) {
			unset($this->content[$object->content->name]);
		}
		return $this;
	}

}
