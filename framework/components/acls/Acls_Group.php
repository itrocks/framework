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
	 * @link Collection
	 * @getter getLinks
	 * @remover remove
	 * @var Acls_Link[]
	 */
	public $content;

	//--------------------------------------------------------------------------------------- $rights
	/**
	 * @link Collection
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
			$right = $object;
			$value = isset($priority_value) ? $priority_value : true;
			if (isset($this->rights[$right])) {
				$this->rights[$right]->value = $value;
			}
			else {
				$this->rights[$right] = new Acls_Right(Acls_User::current()->group, $right, $value);
			}
		}
		elseif ($object instanceof Acls_Right) {
			$acls_right = $object;
			if (isset($priority_value)) {
				$acls_right->value = $priority_value;
			}
			$acls_right->setComposite($this);
			$this->rights[$acls_right->key] = $object;
		}
		elseif ($object instanceof Acls_Group) {
			$acls_group = $object;
			if (!isset($priority_value)) {
				$priority_value = 1;
			}
			$this->content[$acls_group->name] = new Acls_Link($this, $acls_group, $priority_value);
		}
		elseif ($object instanceof Acls_Link) {
			$acls_link = $object;
			$acls_link->setComposite($this);
			$this->content[$acls_link->content->name] = $object;
		}
		return $this;
	}

	//------------------------------------------------------------------------------ getContentGroups
	/**
	 * @return Acls_Group[]
	 */
	public function getContentGroups()
	{
		$groups = [];
		foreach ($this->getLinks() as $link) {
			$groups[$link->content->name] = $link->content;
		}
		return $groups;
	}

	//-------------------------------------------------------------------------------------- getLinks
	/**
	 * @return Acls_Link[]
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @getter */
	private function getLinks()
	{
		$links = isset($this->content) ? $this->content : null;
		if (!isset($links)) {
			$links = [];
			Getter::getCollection($collection, Acls_Link::class, $this);
			foreach ($collection as $link) {
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
	/* @noinspection PhpUnusedPrivateMethodInspection @getter */
	private function getRights()
	{
		$rights = isset($this->rights) ? $this->rights : null;
		if (!isset($rights)) {
			$rights = [];
			Getter::getCollection($collection, Acls_Right::class, $this);
			foreach ($collection as $right) {
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
			$acls_link = $object;
			unset($this->content[$acls_link->content->name]);
		}
		return $this;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

}
