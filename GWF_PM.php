<?php
final class GWF_PM extends GDO # implements GDO_Searchable
{
	public function gdoCached() { return false; }
	
	###########
	### GDO ###
	###########
	public function gdoColumns()
	{
		return array(
			GDO_AutoInc::make('pm_id'),
			GDO_CreatedAt::make('pm_sent_at'),
			GDO_DeletedAt::make('pm_deleted_at'),
			GDO_DateTime::make('pm_read_at'),
			GDO_User::make('pm_owner')->notNull(),
			GDO_User::make('pm_from')->cascadeNull(),
			GDO_User::make('pm_to')->notNull(),
			GDO_Object::make('pm_folder')->klass('GWF_PMFolder')->notNull(),
			GDO_Object::make('pm_parent')->klass('GWF_PM')->cascadeNull(),
			GDO_Object::make('pm_other')->klass('GWF_PM')->cascadeNull(),
			GDO_String::make('pm_title')->notNull()->label('title'),
			GDO_Message::make('pm_message')->notNull(),
			GDO_Checkbox::make('pm_other_read')->initial('0'),
			GDO_Checkbox::make('pm_other_deleted')->initial('0'),
		);
	}
	
	##############
	### Render ###
	##############
	public function renderList() { return GWF_Template::modulePHP('PM', 'listitem_pm.php', ['pm' => $this]); }
	
	##################
	### Convinient ###
	##################
	public function isRead() { return $this->getVar('pm_read_at') !== null; }
	public function displayDate() { return GWF_Time::displayDate($this->getVar('pm_date')); }
	public function getTitle() { return $this->getVar('pm_title'); }
	
	/**
	 * @return GWF_User
	 */
	public function getSender() { return $this->getValue('pm_from'); }
	
	/**
	 * @return GWF_User
	 */
	public function getReceiver() { return $this->getValue('pm_to'); }
	
	/**
	 * @return GWF_User
	 */
	public function getOwner() { return $this->getValue('pm_owner'); }
	public function getOwnerID() { return $this->getVar('pm_owner'); }
	public function getOtherID() { return $this->getVar('pm_other'); }

	/**
	 * Get the other user that differs from param user.
	 * One of the two users has to match.
	 * @param GWF_User $user
	 * @return GWF_User
	 */
	public function getOtherUser(GWF_User $user)
	{
		if ($user->getID() === $this->getFromID())
		{
			return $this->getReceiver();
		}
		elseif ($user->getID() === $this->getToID())
		{
			return $this->getSender();
		}
	}
	
	/**
	 * @return GWF_PM
	 */
	public function getOtherPM() { return $this->getValue('pm_other'); }

	public function getFromID() { return $this->getVar('pm_from'); }
	public function getToID() { return $this->getVar('pm_to'); }
	
	/**
	 * @return GWF_PM
	 */
	public function getParent() { return $this->getValue('pm_parent'); }
	
	/**
	 * @param GWF_User $owner
	 * @return GWF_PM
	 */
	public function getPMFor(GWF_User $owner) { return $this->getOwnerID() === $owner->getID() ? $this : $this->getOtherPM(); }
	
	public function isFrom(GWF_User $user) { return $this->getFromID() === $user->getID(); }
	public function isTo(GWF_User $user) { return $this->getToID() === $user->getID(); }
	
	#############
	### HREFs ###
	#############
// 	public function display_show() { return $this->display('pm_title'); }
	public function href_show() { return href('PM', 'Read', "&id={$this->getID()}"); }
	public function href_delete() { return href('PM', 'Overview', "&delete=1&rbx[{$this->getID()}]=1"); }
	public function href_reply() { return href('PM', 'Write', '&reply='.$this->getID()); }
	public function href_quote() { return href('PM', 'Write', '&quote=yes&reply='.$this->getID()); }
	
	##############
	### Static ###
	##############
	public static function updateOtherDeleted()
	{
		self::table()->update()->set("pm_other_deleted=1")->
		where(" ( SELECT pm_id FROM ( SELECT * FROM gwf_pm ) b WHERE gwf_pm.pm_other = b.pm_id ) IS NULL ")->
		or(" ( SELECT pm_deleted_at FROM ( SELECT * FROM gwf_pm ) b WHERE b.pm_id = gwf_pm.pm_other ) IS NOT NULL ")->exec();
	}
	
	public static function getByIdAndUser(string $id, GWF_User $user)
	{
		$id = self::quoteS($id);
		return self::table()->select('*')->where("pm_id={$id} AND pm_owner={$user->getID()}")->exec()->fetchObject();
	}
	
	##############
	### Unread ###
	##############
	public static function countUnread(GWF_User $user)
	{
		if (null !== ($cache = $user->tempGet('gwf_pm_unread')))
		{
			$cache = self::table()->countWhere("pm_to={$user->getID()} AND pm_read_at IS NULL");
			$user->tempSet('gwf_pm_unread', $cache);
		}
		return $cache;
	}
}
