<?php $field instanceof GDO_PMFromTo; $pm instanceof GWF_PM; $user = GWF_User::current(); ?>
<?php if ($pm->isFrom($user)) : ?>
TO <?php echo $pm->getReceiver()->displayName(); ?>
<?php else : ?>
FROM <?php echo $pm->getSender()->displayName(); ?>
<?php endif; ?>
