<?php
$navbar = new GDO_Bar();
$navbar->addFields(array(
		GDO_Link::make('link_overview')->href(href('PM', 'Overview'))->matIcon('storage'),
		GDO_Link::make('link_settings')->href(href('Account', 'Settings', '&module=PM'))->matIcon('settings'),
		GDO_Link::make('link_trashcan')->href(href('PM', 'Trashcan'))->matIcon('delete'),
		GDO_Link::make('link_write_pm')->href(href('PM', 'Write'))->matIcon('create'),
));
echo $navbar->render();
