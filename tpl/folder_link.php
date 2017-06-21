<?php $gdo instanceof GWF_PMFolder; ?>
<?php
echo GDO_Anchor::make('link_pm_folder')->label($gdo->getName())->href(href('PM', 'Overview', '&folder='.$gdo->getID()))->render();
?>