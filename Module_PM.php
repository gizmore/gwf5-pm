<?php
include 'GWF_PMMethod.php';

final class Module_PM extends GWF_Module
{
	##############
	### Module ###
	##############
	public function getClasses() { return array('GWF_PMFolder', 'GWF_PM'); }
	public function onLoadLanguage() { return $this->loadLanguage('lang/pm'); }
	public function onInstall() { include 'GWF_PMInstall.php'; GWF_PMInstall::install($this); }
	
	##############
	### Config ###
	##############
	public function getUserSettings()
	{
		return array(
			GDO_Int::make('pm_auto_folder')->initial('0')->notNull(),
			GDO_Level::make('pm_level')->initial('0')->notNull(),
			GDO_Message::make('pm_signature')->max(1024),
			GDO_Checkbox::make('pm_email')->initial('0'),
			GDO_Checkbox::make('pm_guests')->initial('0'),
			GDO_Checkbox::make('pm_ghosts')->initial('0'),
		);
	}
	public function getConfig()
	{
		return array(
			GDO_String::make('pm_re')->initial('RE: '),
			GDO_Int::make('pm_limit')->initial('5')->unsigned()->min(0)->max(10000),
			GDO_Duration::make('pm_limit_timeout')->initial(GWF_Time::ONE_HOUR*16),
			GDO_Int::make('pm_max_folders')->initial('0')->unsigned(),
			GDO_Checkbox::make('pm_for_guests')->initial('1'),
			GDO_Checkbox::make('pm_captcha')->initial('0'),
			GDO_Checkbox::make('pm_causes_mail')->initial('0'),
			GDO_User::make('pm_bot_uid')->label('pm_bot_uid'),
			GDO_Checkbox::make('pm_own_bot')->initial('0'),
			GDO_Int::make('pm_per_page')->initial('20')->unsigned(),
			GDO_Checkbox::make('pm_welcome')->initial('0'),
			GDO_Int::make('pm_sig_len')->initial('255')->max(1024)->unsigned(),
			GDO_Int::make('pm_msg_len')->initial('2048')->max(65535)->unsigned(),
			GDO_Int::make('pm_title_len')->initial('64')->max(255)->unsigned(),
			GDO_Int::make('pm_fname_len')->initial(GDO_Username::LENGTH)->max(GDO_Name::LENGTH),
			GDO_Checkbox::make('pm_delete')->initial('1'),
			GDO_Int::make('pm_limit_per_level')->initial('1000000')->unsigned(),
		);
	}
	public function cfgRE() { return $this->getConfigValue('pm_re'); }
	public function cfgIsPMLimited() { return $this->cfgLimitTimeout() >= 0; }
	public function cfgPMLimit() { return $this->getConfigValue('pm_limit'); }
	public function cfgLimitTimeout() { return $this->getConfigValue('pm_limit_timeout'); }
	public function cfgMaxFolders() { return $this->getConfigValue('pm_maxfolders'); }
	public function cfgAllowOwnFolders() { return $this->cfgMaxFolders() > 0; }
	public function cfgGuestPMs() { return $this->getConfigValue('pm_for_guests'); }
	public function cfgGuestCaptcha() { return $this->getConfigValue('pm_captcha'); }
	public function cfgEmailOnPM() { return $this->getConfigValue('pm_causes_mail'); }
	public function cfgEmailSender() { return $this->getConfigValue('pm_mail_sender'); }
	public function cfgBotUserID() { return $this->getConfigVar('pm_bot_uid'); }
	public function cfgBotUser() { return $this->getConfigValue('pm_bot_uid'); }
	public function cfgOwnBot() { return $this->getConfigValue('pm_own_bot'); }
	public function cfgPMPerPage() { return $this->getConfigValue('pm_per_page'); }
	public function cfgWelcomePM() { return $this->getConfigValue('pm_welcome'); }
	public function cfgMaxSigLen() { return $this->getConfigValue('pm_sig_len'); }
	public function cfgMaxMsgLen() { return $this->getConfigValue('pm_msg_len'); }
	public function cfgMaxTitleLen() { return $this->getConfigValue('pm_title_len'); }
	public function cfgMaxFolderNameLen() { return $this->getConfigValue('pm_fname_len'); }
	public function cfgAllowDelete() { return $this->getConfigValue('pm_delete'); }
	public function cfgLimitPerLevel() { return $this->getConfigValue('pm_limit_per_level'); }
	public function cfgLimitForUser(GWF_User $user)
	{
		$min = $this->cfgPMLimit();
		$level = $user->getLevel();
		return $min + floor($level / $this->cfgLimitPerLevel());
	}
	
	#############
	### Hooks ###
	#############
	public function hookUserActivated(array $args)
	{
		if ($this->cfgWelcomePM())
		{
			if ($bot = $this->cfgBotUser())
			{
				$this->sendWelcomePM(method('PM', 'Write'), $bot, $args[0]);
			}
		}
	}
	
	private function sendWelcomePM(PM_Write $method, GWF_User $from, GWF_User $to)
	{
		$title = t('pm_welcome_title', [$this->getSiteName()]);
		$message = t('pm_welcome_message', [$to->displayName(), $this->getSiteName()]);
		$method->deliver($from, $to, $title, $message);
	}

	##############
	### Navbar ###
	##############
	public function onRenderFor(GWF_Navbar $navbar)
	{
		$user = GWF_User::current();
		if ($navbar->isRight() && $user->isAuthenticated())
		{
// 			$this->initModule();
			$count = GWF_PM::table()->countWhere("pm_to={$user->getID()} AND pm_read_at IS NULL");
			$button = GDO_Link::make('btn_pm')->href(href('PM', 'Overview'));
			if ($count > 0)
			{
				$button->label('btn_pm_unread', [$count]);
			}
			$navbar->addField($button);
		}
	}
	
}
