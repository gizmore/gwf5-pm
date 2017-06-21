<?php
/**
 * Sends Email on PM.
 * 
 * @author gizmore
 *
 */
final class GWF_EMailOnPM
{
	public static function deliver(Module_PM $module, GWF_PM $pm)
	{
		$receiver = $pm->getReceiver();
		if (GWF_UserSetting::userGet($receiver, 'pm_email')->getGDOValue())
		{
			if ($receiver->getMail())
			{
				self::sendMail($module, $pm, $receiver);
			}
		}
	}
	
	private static function sendMail(Module_PM $module, GWF_PM $pm, GWF_User $receiver)
	{
		$sender = $pm->getSender();
		
		$email = new GWF_Mail();
		$email->setSender(GWF_BOT_EMAIL);
		$email->setSenderName(GWF_BOT_NAME);
		if (GWF_UserSetting::userGet($sender, 'user_allow_email'))
		{
			$email->setReturn($sender->getMail());
		}
		
		$sitename = GWF5::instance()->getSiteName();
		$email->setSubject(tusr($receiver, 'mail_subj_pm', [$sitename, $sender->displayName()]));
		$email->setBody(tusr($receiver, 'mail_body_pm', array(
			$receiver->displayName(),
			$sender->displayName(),
			$sitename,
			$pm->display('pm_title'),
			$pm->display('pm_message'),
			GWF_HTML::anchor(href('PM', 'Delete', "&id={$pm->getID()}&token={$pm->gdoHashcode()}")),
		)));
		$email->sendToUser($receiver);
		echo GWF_Response::message('msg_pm_mail_sent', [$receiver->displayName()])->render();
	}
}
