<?php $pm instanceof GWF_PM; ?>
<md-card>
  <md-card-title>
    <md-card-title-text>
      <span class="md-headline"><?php $pm->edisplay('pm_title'); ?></span>
    </md-card-title-text>
  </md-card-title>
  <md-card-content>
    <section layout="row" flex layout-fill>
      <div>
        <b><?php l('pm_by', [$pm->getSender()->displayName()]); ?></b><br/><b><?php l('pm_to', [$pm->getReceiver()->displayName()]); ?></b>
      </div>
      <div>
        <b><?php l('pm_sent', [$pm->displayDate()]); ?></b>
      </div>
    </section>
    <section layout="column" flex layout-fill>
<?php echo $pm->gdoColumn('pm_message')->renderCell(); ?>
    </section>
  </md-card-content>
  <md-card-actions layout="row" layout-align="end center">
    <?php foreach ($actions as $action) : $action instanceof GDO_Button; ?>
    <?php echo $action->renderCell(); ?>
    <?php endforeach; ?>
  </md-card-actions>
</md-card>
