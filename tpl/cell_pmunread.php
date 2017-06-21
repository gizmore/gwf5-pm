<?php $field instanceof GDO_Template; $pm = $field->gdo; $pm instanceof GWF_PM; ?>
<?php if (!$pm->isRead()) : ?>
<?php echo GDO_Icon::matIconS('alert'); ?>
<?php endif; ?>

