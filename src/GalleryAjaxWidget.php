<?php
namespace matvik\modelGallery;

use yii\base\Widget;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use matvik\modelGallery\GalleryAjaxWidgetAsset;
use Yii;

/**
 * Ajax gallery widget.
 */
class GalleryAjaxWidget extends Widget
{

    /**
     * Active Record model with GalleryBehaviour attached. 
     * @var yii\db\ActiveRecord
     */
    public $model;

    /**
     * Action for ajax requests. Can be action array or URL string.
     * @var string|array
     */
    public $action;

    /**
     * Image item width
     * @var integer
     */
    public $imageWidth = false;

    /**
     * Image item height
     * @var integer
     */
    public $imageHeight = 200;

    /**
     * The maximum number of model images that can be riched.
     * 0 means unlimited.
     * @var integer
     */
    public $maxFilesTotal = 0;
    
    /**
     * Custom messages
     * @var string[]
     */
    public $messages = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if (is_array($this->action)) {
            $this->action = Url::to($this->action);
        }

        Yii::$app->i18n->translations['model-gallery'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@matvik/modelGallery/messages',
            'fileMap' => [
                'model-gallery' => 'default.php',
            ],
        ];

        $defaultMessages = [
            'maxFilesTotalError' => Yii::t('model-gallery', 'Error! Maximum total number of images should not exceed {number}', ['number' => $this->maxFilesTotal]),
            'errorUpload' => Yii::t('model-gallery', 'Upload error'),
            'errorOrder' => Yii::t('model-gallery', 'Error changing images order'),
            'errorDelete' => Yii::t('model-gallery', 'Error deleting images'),
            'confirmDelete' => Yii::t('model-gallery', 'Delete images?'),
            'notSelected' => Yii::t('model-gallery', 'No images selected'),
            'buttonLabelDelete' => Yii::t('model-gallery', 'Delete checked'),
            'buttonLabelSelectAll' => Yii::t('model-gallery', 'Select all'),
            'buttonLabelDeselectAll' => Yii::t('model-gallery', 'Deselect all'),
            'deleteCheckboxLabel' => Yii::t('model-gallery', 'Delete'),
            'dropAreaLabel' => Yii::t('model-gallery', 'Drag and Drop Files Here'),
            'dropAreaLabelOr' => Yii::t('model-gallery', 'or'),
            'buttonAddImages' => Yii::t('model-gallery', 'Select from folder'),
            'loaderSaving' => Yii::t('model-gallery', 'saving'),
        ];
        $this->messages = ArrayHelper::merge($defaultMessages, $this->messages);
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        GalleryAjaxWidgetAsset::register($this->view);
        $wCss = $this->imageWidth ? "width: {$this->imageWidth}px;" : '';
        $hCss = $this->imageHeight ? "height: {$this->imageHeight}px;" : '';
        $this->view->registerCss(
            ".ajax-gallery-list li { $wCss $hCss }"
        );

        // sortable items
        $items = [];
        foreach ($this->model->galleryImages as $image) {
            $items[] = [
                'content' => $this->render('_ajaxWidgetItem', [
                    'image' => $image,
                ]),
                'options' => [
                    'data-image-id' => $image->id,
                    'class' => 'ui-state-default ui-sortable-handle ajax-gallery-item',
                ]
            ];
        }

        return $this->render('ajaxWidget', [
                'items' => $items,
        ]);
    }
}
