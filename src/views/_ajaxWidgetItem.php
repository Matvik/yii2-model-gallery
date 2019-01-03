<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $image matvik\modelGallery\Image */
?>
<?= Html::img($image ? $image->getUrl('preview') : '{preview}', [
    'width' => $this->context->imageWidth ? $this->context->imageWidth : false,
    'height' => $this->context->imageHeight ? $this->context->imageHeight : false,
]) ?>
<label for="ajax-delete-image-checkbox-<?= $image ? $image->id : '{id}' ?>" class="delete-image-label"><?= $this->context->messages['deleteCheckboxLabel'] ?>
    <input type="checkbox"
           class="delete-image-checkbox" id="ajax-delete-image-checkbox-<?= $image ? $image->id : '{id}' ?>">
	<span class="checkmark"></span>
</label>