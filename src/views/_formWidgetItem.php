<?php
/* @var $this yii\web\View */
/* @var $image matvik\modelGallery\Image */
/* @var $width integer */
/* @var $height integer */
?>
<img src="<?= $image->getUrl('preview') ?>" width="<?= $width ?>" height="<?= $height ?>">
<label for="delete-image-checkbox-<?= $image->id ?>" class="delete-image-label"><?= Yii::t('model-gallery', 'Delete') ?>
    <input type="checkbox"
           class="delete-image-checkbox" id="delete-image-checkbox-<?= $image->id ?>">
</label>