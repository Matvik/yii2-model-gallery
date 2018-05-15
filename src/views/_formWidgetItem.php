<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $image matvik\modelGallery\Image */
?>
<?= Html::img($image->getUrl('preview'), [
    'width' => $this->context->imageWidth ? $this->context->imageWidth : false,
    'height' => $this->context->imageHeight ? $this->context->imageHeight : false,
]) ?>
<label for="form-delete-image-checkbox-<?= $image->id ?>" class="delete-image-label"><?= $this->context->messages['deleteCheckboxLabel'] ?>
    <input type="checkbox"
           class="delete-image-checkbox" id="form-delete-image-checkbox-<?= $image->id ?>">
	<span class="checkmark"></span>
</label>