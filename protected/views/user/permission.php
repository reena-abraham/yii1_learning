<h2>Manage Permissions for <?php echo CHtml::encode($user->name); ?></h2>

<?php if (Yii::app()->user->hasFlash('success')): ?>
    <div class="success"><?php echo Yii::app()->user->getFlash('success'); ?></div>
<?php endif; ?>

<?php echo CHtml::beginForm(); ?>

<ul>
<?php foreach ($allPermissions as $perm): ?>
    <li>
        <?php echo CHtml::checkBox('permissions[]', in_array($perm->id, $assignedPermissions), array('value' => $perm->id)); ?>
        <?php echo CHtml::encode($perm->name); ?>
    </li>
<?php endforeach; ?>
</ul>

<?php echo CHtml::submitButton('Save Permissions'); ?>
<?php echo CHtml::endForm(); ?>
