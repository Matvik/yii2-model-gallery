<?php

use yii\widgets\Pjax;
use yii\jui\Sortable;
use matvik\modelGallery\GalleryAjaxAction;

/* @var $this yii\web\View */
/* @var $items array */

$messages = $this->context->messages;

?>

<?php Pjax::begin(['id' => 'gallery-ajax-widget-pjax', 'timeout' => 10000]); ?>
<?=
Sortable::widget([
    'items' => $items,
    'options' => [
        'class' => 'ajax-gallery-list',
        'data' => [
            'model-id' => $this->context->model->id,
            'action-url' => $this->context->action,
            'max-files-total' => $this->context->maxFilesTotal,
            'param-upload' => GalleryAjaxAction::ACTION_UPLOAD_IMAGES,
            'param-order' => GalleryAjaxAction::ACTION_CHANGE_IMAGES_ORDER,
            'param-delete' => GalleryAjaxAction::ACTION_DELETE_IMAGES,
            'messages' => $messages,
        ]
    ],
])

?>
<?php Pjax::end(); ?>
<div class="buttons-wrapper">
    <button type="button" class="ajax-gallery-delete-button red-button"><?= $messages['buttonLabelDelete'] ?></button>
    <button type="button" class="ajax-gallery-select-all-button"><?= $messages['buttonLabelSelectAll'] ?></button>
    <button type="button" class="ajax-gallery-clear-selection-button yellow-button"><?= $messages['buttonLabelDeselectAll'] ?></button>
</div>


<div id="gallery-ajax-widget-drop-area">
    <h3>Drag and Drop Files Here</h3>
    <div class="input-file-button">
        <span>Click to add Files</span>
        <input type="file" title="Click to add Files">
    </div>
    <progress class="upload-progress" value="0" max="100" style="display: none;"></progress>
</div>