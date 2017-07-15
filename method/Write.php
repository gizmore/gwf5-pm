<?php
final class PM_Write extends GWF_MethodForm
{
	use GWF_PMMethod;
	
	private $reply;
	
	public function execute()
	{
		$user = GWF_User::current();
		$module = Module_PM::instance();

		# Get in reply to
		if ($this->reply = GWF_PM::table()->find(Common::getRequestString('reply'), false))
		{
			if ($this->reply->getOwnerID() !== $user->getID())
			{
				$this->reply = null;
			}
		}
		
		if ($module->cfgIsPMLimited())
		{
			$limit = $module->cfgLimitForUser($user);
			$cut = time() - $module->cfgLimitTimeout();
			$sent = GWF_PM::table()->countWhere("pm_from={$user->getID()} and pm_sent_at>$cut");
			if ($sent >= $limit)
			{
				return $this->pmNavbar()->add($this->error('err_pm_limit_reached', [$limit, GWF_Time::displayAgeTS($cut)]));
			}
		}
		return $this->pmNavbar()->add(parent::execute());
	}
	
	public function createForm(GWF_Form $form)
	{
		list($username, $title, $message) = $this->initialValues();
		$table = GWF_PM::table();
		$form->addFields(array(
			GDO_Username::make('pm_to')->exists()->validator([$this, 'validateCanSend'])->value($username),
			$table->gdoColumn('pm_title')->value($title),
			$table->gdoColumn('pm_message')->value($message),
			GDO_Submit::make(),
			GDO_AntiCSRF::make(),
		));
	}
	
	private function initialValues()
	{
		$username = null; $title = null; $message = null;
		if ($this->reply)
		{
			# Recipient
			$username = $this->reply->getOtherUser(GWF_User::current())->displayName();
			# Message
			$message= $this->reply->getVar('pm_message');
			# Title
			$title = $this->reply->getVar('pm_title');
			$re = Module_PM::instance()->cfgRE();
			$title = $re . ' ' . trim(GWF_String::substrFrom($title, $re));
		}
		return [$username, $title, $message];
	}
	
	public function validateCanSend(GWF_Form $form, GDOType $type)
	{
		return true;
	}
	
	public function formValidated(GWF_Form $form)
	{
		$this->deliver(GWF_User::current(), $form->getField('pm_to')->gdo, $form->getVar('pm_title'), $form->getVar('pm_message'), $this->reply);
		return $this->message('msg_pm_sent');
	}
	
	public function deliver(GWF_User $from, GWF_User $to, string $title, string $message, GWF_PM $parent=null)
	{
		$pmFrom = GWF_PM::blank(array(
				'pm_parent' => $parent ? $parent->getPMFor($from)->getID() : null,
				'pm_read_at' => GWF_Time::getDate(),
				'pm_owner' => $from->getID(),
				'pm_from' => $from->getID(),
				'pm_to' => $to->getID(),
				'pm_folder' => GWF_PMFolder::OUTBOX,
				'pm_title' => $title,
				'pm_message' => $message,
		))->insert();
		$pmTo = GWF_PM::blank(array(
				'pm_parent' => $parent ? $parent->getPMFor($to)->getID() : null,
				'pm_owner' => $to->getID(),
				'pm_from' => $from->getID(),
				'pm_to' => $to->getID(),
				'pm_folder' => GWF_PMFolder::INBOX,
				'pm_title' => $title,
				'pm_message' => $message,
				'pm_other' => $pmFrom->getID(),
				'pm_other_read' => '1',
		))->insert();
		$pmFrom->saveVar('pm_other', $pmTo->getID());
		$to->tempUnset('gwf_pm_unread');
		$this->module->includeClass('GWF_EMailOnPM');
		GWF_EMailOnPM::deliver(Module_PM::instance(), $pmTo);
		GWF_Hook::call('PMSent', [$pmTo]);
	}
}
