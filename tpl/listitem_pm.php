<?php
$pm instanceof GWF_PM;
$user = GWF_User::current();
$otherUser = $pm->getOtherUser($user);
$href = href('PM', 'Read', '&id='.$pm->getID());
$hrefDelete = href('PM', 'Overview', '&delete=1&id='.$pm->getID());
?>
<?php if ($pm->isFrom($user)) : ?>
<md-list-item class="md-3-line" ng-click="null" href="<?= $href; ?>">
  <?= GWF_Avatar::renderAvatar($otherUser); ?>
  <div class="md-list-item-text" layout="column">
    <h3><?= $otherUser->displayName(); ?></h3>
    <h4><?= htmle($pm->getTitle()); ?></h4>
    <p><?= t('pm_sent', [$pm->displayDate()]); ?></p>
  </div>
  <?= GDO_IconButton::make()->icon('delete')->href($hrefDelete); ?>
</md-list-item>
<?php else : ?>
<md-list-item class="md-3-line" ng-click="null" href="<?= $href; ?>">
  <?= GWF_Avatar::renderAvatar($otherUser); ?>
  <div class="md-list-item-text" layout="column">
    <h3><?= $otherUser->displayName(); ?></h3>
    <h4><?= htmle($pm->getTitle()); ?></h4>
    <p><?= t('pm_received', [$pm->displayDate()]); ?></p>
  </div>
  <?= GDO_IconButton::make()->icon('delete')->href($hrefDelete); ?>
</md-list-item>
<?php endif; ?>
