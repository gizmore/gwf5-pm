<?php $field instanceof GDO_Template; $pm = $field->gdo; $pm instanceof GWF_PM; ?>
<?php if (!$pm->isRead()) : ?>
<?php echo GDO_Icon::iconS('notifications_active'); ?>
<?php endif; ?>

