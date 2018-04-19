<?php

use yii\helpers\Html;
use yii\jui\Sortable;

/* @var $this yii\web\View */
/* @var $model yii\base\Model */
/* @var $items string[] */
/* @var $renderInput boolean */
/* @var $itemWidth integer */
/* @var $itemHeight integer */
/* @var $maxFilesUploaded integer */
/* @var $maxFilesTotal integer */
/* @var $maxFilesUploadedErrorMessage string */
/* @var $maxFilesTotalErrorMessage string */
?>

<?= Sortable::widget([
    'items' => $items,
    'options' => [
        'class' => 'form-gallery-list'
    ],
]) ?>
<?php if ($renderInput) : ?>
<?= Html::activeFileInput($model, 'galleryImages[]', ['multiple' => true, 'id' => 'gallery-form-widget-input-files', 'accept' => 'image/*']) ?>
<label id="gallery-form-widget-input-files-trigger" for="gallery-form-widget-input-files"><?= Yii::t('model-gallery', 'Load') ?></label>
<button type="button" id="gallery-form-widget-input-files-clear"><?= Yii::t('model-gallery', 'Clear') ?></button>
<ul id="gallery-form-widget-input-files-list" class="form-gallery-list" 
    data-item-width="<?= $itemWidth ?>" data-item-height="<?= $itemHeight ?>"
    data-current-images-count="<?= count($items) ?>"
    data-max-files-uploaded="<?= $maxFilesUploaded ?>"
    data-max-files-total="<?= $maxFilesTotal ?>"
    data-max-files-uploaded-error="<?= $maxFilesUploadedErrorMessage ?>"
    data-max-files-total-error="<?= $maxFilesTotalErrorMessage ?>">
    
</ul>
<?php endif ?>

<?= Html::activeHiddenInput($model, 'galleryImagesDelete', ['id' => 'gallery-form-widget-input-deleting']) ?>
<?= Html::activeHiddenInput($model, 'galleryImagesOrder', ['id' => 'gallery-form-widget-input-order']) ?>