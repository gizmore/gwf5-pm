<?php $navbar instanceof GWF_Navbar;
$user = GWF_User::current();
if ($navbar->isRight() && $user->isAuthenticated())
{
	$count = GWF_PM::countUnread($user);
	$button = GDO_Link::make('btn_pm')->href(href('PM', 'Overview'));
	if ($count > 0)
	{
		$button->label('btn_pm_unread', [$count]);
	}
	$navbar->addField($button);
}
