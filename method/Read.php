<?php
final class PM_Read extends GWF_Method
{
	use GWF_PMMethod;
	
	public function execute()
	{
		if (!($pm = GWF_PM::getByIdAndUser(Common::getRequestString('id'), GWF_User::current())))
		{
			return $this->pmNavbar()->add($this->error('err_pm'));
		}
		return $this->pmNavbar()->add($this->pmRead($pm));
	}
	
	public function pmRead(GWF_PM $pm)
	{
		if (!$pm->isRead())
		{
			$pm->saveVar('pm_read_at', GWF_Time::getDate());
			$pm->getOtherPM()->saveVar('pm_other_read', '1');
		}
		$actions = array(
			GDO_Button::make('delete')->gdo($pm)->icon('delete'),
			GDO_Button::make('reply')->gdo($pm)->icon('reply'),
			GDO_Button::make('quote')->gdo($pm)->icon('quote'),
		);
		return $this->templatePHP('card_pm.php', ['pm' => $pm, 'actions' => $actions]);
	}
	
}
