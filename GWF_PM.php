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
			GDO_String::make('pm_title')->notNull(),
			GDO_Message::make('pm_message')->notNull(),
			GDO_Checkbox::make('pm_other_read')->initial('0'),
			GDO_Checkbox::make('pm_other_deleted')->initial('0'),
// 			GDO_Checkbox::make('pm_encrypted_gpg'),
// 			GDO_Checkbox::make('pm_encrypted_pass'),
		);
	}
	##################
	### Convinient ###
	##################
	public function isRead() { return $this->getVar('pm_read_at') !== null; }
	public function displayDate() { return GWF_Time::displayDate($this->getVar('pm_date')); }
	
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
	public function display_show() { return $this->display('pm_title'); }
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
		if ($null !== ($cache = $user->tempGet('gwf_pm_unread')))
		{
			$cache = self::table()->countWhere("pm_to={$user->getID()} AND pm_read_at IS NULL");
			$user->tempSet('gwf_pm_unread', $cache);
		}
		return $cache;
	}
	
// 	public function displayMessage()
// 	{
// 		return $this->displayMessageTranslated($this->getVar('pm_message'));
// 	}
// 	public function displayMessageTranslated($msg)
// 	{
// 		return GWF_Message::display($msg, true, $this->isOptionEnabled(self::SMILEYS), false, GWF_Message::getQuickSearchHighlights(Common::getRequest('term', '')));
// 	}
	
// 	public function isGuestPM() { return $this->getFromID() === '0'; }
	

// 	/**
// 	 * @param int $pmid
// 	 * @return GWF_PM
// 	 */
// 	public static function getByID($pmid)
// 	{
// 		$pmid = (int)$pmid;
// 		return self::table(__CLASS__)->selectFirstObject('*', "pm_id=$pmid");
// 	}
	
// 	public function getHTMLClass()
// 	{
// 		// I am the sender
// 		if (!$this->isRecipient())
// 		{
// 			// The receiver deleted this pm
// 			if ($this->isOptionEnabled(self::OTHER_DELETED)) {
// 				return 'gwf_pm_deleted';
// 			}
// 			// It got read by the recipient
// 			elseif ($this->isOtherRead()) {
// 				return 'gwf_pm_read';
// 			}
// 			// Iz got delivered, but not read yet.
// 			else {
// 				return 'gwf_pm_unread';
// 			}
			
// 		}
// 		// I am the receiver
// 		else
// 		{
// 			return $this->isRead() ? 'gwf_pm_old' : 'gwf_pm_new';
// 		}
// 	}
	
// 	public static function getUnreadPMs(Module_PM $module, $userid)
// 	{
// 		$conditions = '(pm_options&1=0) AND (pm_to='.intval($userid).')';
// 		return GDO::table('GWF_PM')->selectObjects('*', $conditions, 'pm_date DESC', $module->cfgPMPerPage());
// 	}
	
// 	/**
// 	 * Get the other user.
// 	 * If param user is sender it will return receiver and vice versa.
// 	 * @param GWF_User $user
// 	 * @return GWF_User
// 	 */
// 	public function getOtherUser(GWF_User $user)
// 	{
// 		$uid = $user->getID();
// 		if ($this->getFromID() === $uid) {
// 			return $this->getReceiver();
// 		} elseif ($this->getToID() === $uid) {
// 			return $this->getSender();
// 		} else {
// 			return false;
// 		}
// 	}
	
// 	public function displaySignature()
// 	{
// 		if (false === ($pmo = GWF_PMOptions::getPMOptions($this->getSender()))) {
// 			return '';
// 		}
		
// 		if ('' === ($signature = $pmo->getVar('pmo_signature'))) {
// 			return '';
// 		}
		
// //		$highlight = GDO::getQuickSearchHighlights(Common::getRequest('term'));
// 		$highlight = array();
// 		return '<div class="gwf_signature">'.GWF_Message::display($signature, true, true, true, $highlight).'</div>';
// 	}
	
// 	/**
// 	 * Get previous message.
// 	 * @return GWF_PM
// 	 */
// 	public function getReplyToPrev()
// 	{
// 		$uid = GWF_Session::getUserID();
// 		return self::table(__CLASS__)->selectObjects('*', "pm_owner=$uid AND pm_id=".$this->getVar('pm_parent'), 'pm_date DESC');
// 	}
	
// 	/**
// 	 * Get next message.
// 	 * @return GWF_PM
// 	 */
// 	public function getReplyToNext()
// 	{
// 		$uid = GWF_Session::getUserID();
// 		return self::table(__CLASS__)->selectObjects('*', "pm_owner=$uid AND pm_parent=".$this->getVar('pm_id'), 'pm_date ASC');
// 	}
	
// 	public static function getNewPMHREF() { return GWF_WEB_ROOT.'pm/create'; }
// 	public static function getNewPMHREF2($username) { return GWF_WEB_ROOT.'pm/send/to/'.urlencode($username); }
// 	public function getQuoteHREF() { return GWF_WEB_ROOT.'pm/quote/reply/to/'.$this->getID().'/'.$this->urlencodeSEO('pm_title'); }
// 	public function getReplyHREF() { return GWF_WEB_ROOT.'pm/reply/to/'.$this->getID().'/'.$this->urlencodeSEO('pm_title'); }
// 	public function getEditHREF() { return GWF_WEB_ROOT.'pm/edit/'.$this->getID().'/'.$this->urlencodeSEO('pm_title'); }
// 	public function getDisplayHREF($term='') { if ($term !== '') { $term = '/'.urlencode($term); } return GWF_WEB_ROOT.'pm/show/'.$this->getVar('pm_id').'/'.$this->urlencodeSEO('pm_title').$term; }
// 	public function getDeleteHREF($userid) { return GWF_WEB_ROOT.'index.php?mo=PM&me=Delete&pmid='.$this->getID().'&uid='.$userid.'&token='.$this->getHashcode(); }
// 	public function getRestoreHREF() { return GWF_WEB_ROOT.'index.php?mo=PM&me=Trashcan&undelete='.$this->getID(); }
// 	public function getAutoFolderHREF() { return GWF_WEB_ROOT.'index.php?mo=PM&me=AutoFolder&pmid='.$this->getID(); }
// 	public function getIgnoreHREF(GWF_User $user) { return GWF_WEB_ROOT.sprintf('pm/do/ignore/%d/%s', $user->getID(), $user->display('user_name'));  }
// 	public function getTranslateHREF() { return GWF_WEB_ROOT.'pm/show_translated/'.$this->getVar('pm_id').'/'.$this->urlencodeSEO('pm_title'); }
	###################
	### Permissions ###
	###################
// 	public function canEdit($user)
// 	{
// 		if ($user === false) {
// 			return false;
// 		}
		
// 		if ($this->isRead()) {
// 			return false;
// 		}
		
// //		return ($this->getOwnerID() === $user->getID()) {
// //			return true;
// //		}
// //		if ('0' === ($from = $this->getSender()->getID())) {
// //			return false;
// //		}
// //		if ($from !== $user->getID()) {
// //			return false;
// //		}
// //		if ($this->isRead()) {
// //			return false;
// //		}
// 		return true;
// 	}
	
// 	public function canRead($user)
// 	{
// 		if ($user === false) {
// 			return false;
// 		}
// 		return $user->getID() === $this->getOwnerID();
// 	}
	
	#######################
	### GDO_Displayable ###
	#######################
//	public function getDisplayableFields(GWF_User $user) { return $this->getColumnNames(); }
//	public function displayColumn(GWF_Module $module, GWF_User $user, $col_name)
//	{
//		switch ($col_name)
//		{
////			case 'pm_id':
//			case 'pm_to':
//				if ($this->getReceiver()->getID() === GWF_Session::getUserID()) {
//					return '';
//				}
//				return $this->getReceiver()->display('user_name');
//			case 'pm_from':
//				return $this->getSender()->display('user_name');
////			case 'pm_to_folder':
////			case 'pm_from_folder':
//			case 'pm_date':
//				return GWF_Time::displayDate($this->getVar('pm_date'));
//			case 'pm_title':
//				return $this->display('pm_title');
//			case 'pm_message':
//				return GWF_Message::display($this->getVar('pm_message'), true, true);
//			case 'pm_options':
//				return $this->displayActions($module, $user);
//			case 'pm_options&1':
//				$c = $this->isOptionEnabled(GWF_PM::READ) ? 'gwf_pm_old' : 'gwf_pm_new';
//				return sprintf('<span class="%s"></span>', $c);
//			default:
//				return GWF_HTML::error('GWF_PM', 'Unknown column: '.GWF_HTML::display($col_name));
//
//		}
//	}
//	
//	public function displayActions(Module_PM $module, $user)
//	{
//		$back = '';
//		if ($this->canEdit($user)) {
//			$back .= GWF_Button::edit($this->getEditHREF(), $module->lang('btn_edit'));
//		}
//		if ($this->canRead($user)) {
//			$back .=
//				GWF_Button::reply($this->getReplyHREF()).
//				GWF_Button::quote($this->getQuoteHREF()).
//				GWF_Button::delete($this->getDeleteHREF($user->getID()));
//		}
//		return $back;
//	}
//	
//	####################
//	### GDO_Sortable ###
//	####################
//	public function getSortableDefaultBy(GWF_User $user) { return 'pm_date'; }
//	public function getSortableDefaultDir(GWF_User $user) { return 'DESC'; }
//	public function getSortableFields(GWF_User $user) { return array('pm_options&'.self::READ, 'pm_date', 'pm_to', 'pm_from', 'pm_title', 'pm_options'); }
	
	######################
	### GDO_Searchable ###
	######################
//	public function getSearchableActions(GWF_User $user) { return ''; }
//	public function getSearchableFields(GWF_User $user) { return array('T_A.user_name', 'T_B.user_name', 'pm_title', 'pm_message'); }
//	public function getSearchableFormData(GWF_User $user) { return ''; }
	
	#############
	### Hooks ###
	#############
// 	public static function hookDeleteUser(GWF_User $user)
// 	{
// 		$uid = $user->getVar('user_id');
		
// 		$pms = self::table(__CLASS__);
// 		$del = self::OWNER_DELETED;
// 		if (false === ($pms->update("pm_options=pm_options|$del", "pm_owner=$uid"))) {
// 			return GWF_HTML::err('ERR_DATABASE', array( __FILE__, __LINE__));
// 		}
// 		$del = self::OTHER_DELETED;
// 		if (false === ($pms->update("pm_options=pm_options|$del", "pm_from=$uid"))) {
// 			return GWF_HTML::err('ERR_DATABASE', array( __FILE__, __LINE__));
// 		}
// 		return '';
// 	}
	
	################
	### Creation ###
// 	################
// 	/**
// 	 * Create a PM.
// 	 * @param int $uid_from
// 	 * @param int $uid_to
// 	 * @param string $title
// 	 * @param string $message
// 	 * @return GWF_PM
// 	 */
// // 	public static function fakePM($uid_from, $uid_to, $title, $message, $owner=0, $folder=0, $parent=0, $other_id=0)
// 	{
// 		return new self(array(
// 			'pm_id' => '0',
// 			'pm_date' => GWF_Time::getDate(GWF_Date::LEN_SECOND),
// 			'pm_owner' => $owner,
// 			'pm_folder' => $folder,
// 			'pm_parent' => $parent,
// 			'pm_to' => $uid_to,
// 			'pm_from' => $uid_from,
// 			'pm_otherid' => $other_id,
// 			'pm_title' => $title,
// 			'pm_message' => $message,
// 			'pm_options' => 0,
// 		));
// 	}
	
	################
	### Deletion ###
	################
// 	public function markDeleted(GWF_User $user, $deleted=true)
// 	{
// //		$senderid = $this->getSender()->getID();
// //		$receivid = $this->getReceiver()->getID();
// //		
// //		if ($senderid === $receivid) {
// //			$bit = self::FROM_DELETED|self::TO_DELETED;
// //		} else {
// //			$bit = $senderid === $this->getVar('pm_owner') ? self::FROM_DELETED : self::TO_DELETED;
// //		}
// //		
// 		if ($this->isOptionEnabled(self::OWNER_DELETED) === $deleted)
// 		{
// 			return false;
// 		}
		
// 		# save it
// 		$this->saveOption(self::OWNER_DELETED, $deleted);
// 		$this->markRead($user, true);

// 		$folder = $this->getFolder($user);
// 		if ($folder->isRealBox())
// 		{
// 			$folder->increase('pmf_count', $deleted?-1:1);
// 		}
// //		$this->saveOption($bit, $deleted);
		
// 		if (false !== ($other = $this->getOtherPM()))
// 		{
// 			$other->saveOption(self::OTHER_DELETED, $deleted);
// // 			$folder = $other->getFolder($other->getOwner());
// // 			if ($folder->isRealBox())
// // 			{
// // 				$folder->increase('pmf_count', $deleted?-1:1);
// // 			}
// 		}
// //		$other->getFolder($user)->increase('pmf_count', $deleted?-1:1);
// //		$$other->markRead($user, true);
// 		return true;
// 	}
	
// 	public function hasDeleted(GWF_User $user)
// 	{
// 		return $this->isOptionEnabled(self::OWNER_DELETED);
// //		return $this->isOptionEnabled($this->getSender()->getID() === $user->getID() ? self::FROM_DELETED : self::TO_DELETED);
// 	}
	
// 	public function getHashcode()
// 	{
// 		$hash = '';
// 		$cd = array('pm_date', 'pm_to', 'pm_from', 'pm_id');
// 		foreach ($cd as $c)
// 		{
// 			$hash .= ':::'.$this->getVar($c);
// 		}
// 		return substr(md5($hash), 0, 16);
// 	}
	
	#################
	### Mark Read ###
	#################
// 	public function isRead() { return $this->isOptionEnabled(self::READ); }
// 	public function isOtherRead() { return $this->isOptionEnabled(self::OTHER_READ); }
// 	public function markRead($user, $read=true)
// 	{
// 		if ($this->isRead() === $read) {
// 			return true;
// 		}
		
// 		if ($this->isRecipient())
// 		{
// 			if (false === $this->saveOption(self::READ, $read)) {
// 				return false;
// 			}
			
// 			if (false !== ($pm2 = $this->getOtherPM())) {
// 				if (false === $pm2->saveOption(self::OTHER_READ, $read)) {
// 					return false;
// 				}
// 			}			
// 		}
		
// 		return true;
// 	}
	
	############
	### Move ###
	############
// 	public function getFolder(GWF_User $user)
// 	{
// 		return GWF_PMFolder::getByID($this->getVar('pm_folder'));
// 	}
	
// 	public function move(GWF_User $user, GWF_PMFolder $folder)
// 	{
// 		if (false === ($old_folder = $this->getFolder($user))) {
// 			return false;
// 		}
		
// 		if ($old_folder->getID() === $folder->getID()) {
// 			return false;
// 		}
		
// 		if (false === $this->saveVar('pm_folder', $folder->getID())) {
// 			return false;
// 		}
		
// 		if (false === $folder->increase('pmf_count', 1)) {
// 			return false;
// 		}
		
// 		if (false === $old_folder->increase('pmf_count', -1)) {
// 			return false;
// 		}
		
// 		$this->markRead($user, true);

// 		return true;
// 	}
}
