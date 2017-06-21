<?php
final class GWF_PMInstall
{
	public static function install(Module_PM $module)
	{
		self::installFolders($module).
		self::installPMBotID($module);
	}
	
	private static function installFolders(Module_PM $module)
	{
		if (!GWF_PMFolder::table()->countWhere('true'))
		{
			GWF_PMFolder::blank(['pmf_name' => 'INBOX'])->insert();
			GWF_PMFolder::blank(['pmf_name' => 'OUTBOX'])->insert();
		}
	}
	
	private static function installPMBotID(Module_PM $module)
	{
		if (!($user = $module->cfgBotUser()))
		{
			if ($module->cfgOwnBot())
			{
				self::installPMBot($module);
			}
			else 
			{
				self::installAdminAsPMBot($module);
			}
		}
	}
	
	private static function installAdminAsPMBot(Module_PM $module)
	{
		$users = GWF_User::withPermission('admin');
		if ($user = @$users[0])
		{
			$module->saveConfigVar('pm_bot_uid', $user->getID());
		}
	}
	
	private static function installPMBot(Module_PM $module)
	{
		$user = GWF_User::blank(array(
			'user_name' => '_GWF_PM_BOT_',
			'user_real_name' => GWF_BOT_NAME,
			'user_type' => GWF_User::BOT,
			'user_email' => GWF_BOT_EMAIL,
			'user_register_time' => GWF_Time::getDate(),
		));
		$user->insert();
		$module->saveConfigVar('pm_bot_uid', $user->getID());
	}
}
