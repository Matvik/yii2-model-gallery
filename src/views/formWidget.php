<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\jui\Sortable;

/* @var $this yii\web\View */
/* @var $items array */
$messages = $this->context->messages;

?>

<?=
Sortable::widget([
    'items' => $items,
    'options' => [
        'class' => 'form-gallery-list'
    ],
])

?>
<?php if ($this->context->renderInput) : ?>
    <?= Html::activeFileInput($this->context->formModel, 'galleryImageFiles[]', ['multiple' => true, 'id' => 'gallery-form-widget-input-files', 'accept' => 'image/*']) ?>
    <div class="buttons-wrapper">
        <label id="gallery-form-widget-input-files-trigger" for="gallery-form-widget-input-files"><?= $messages['buttonLabelLoad'] ?></label>
        <button type="button" id="gallery-form-widget-input-files-clear" class="red-button" style="display: none"><?= $messages['buttonLabelClear'] ?></button>
    </div>
    
    <ul id="gallery-form-widget-input-files-list" class="form-gallery-list" 
        data-item-width="<?= $this->context->imageWidth ?>" data-item-height="<?= $this->context->imageHeight ?>"
        data-current-images-count="<?= count($items) ?>"
        data-max-files-uploaded="<?= $this->context->maxFilesUploaded ?>"
        data-max-files-total="<?= $this->context->maxFilesTotal ?>"
        data-messages='<?= Json::encode($messages) ?>'>

    </ul>
<?php endif ?>

<?= Html::activeHiddenInput($this->context->formModel, 'galleryImagesDelete', ['id' => 'gallery-form-widget-input-deleting']) ?>
<?= Html::activeHiddenInput($this->context->formModel, 'galleryImagesOrder', ['id' => 'gallery-form-widget-input-order']) ?>