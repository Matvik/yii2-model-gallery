<?php

namespace matvik\modelGallery;

use yii\base\Widget;
use matvik\modelGallery\GalleryFormWidgetAsset;
use Yii;

/**
 * Form gallery widget. New gallery state will be submitted when submiting form.
 */
class GalleryFormWidget extends Widget
{
    
    /**
     * Model associated with the form. Can be either ActiveRecord model 
     * or separate form model with GalleryFormBehaviour attached.
     * @var yii\base\Model
     */
    public $formModel;
    
    /**
     * Active Record model with attached GalleryBehaviour. 
     * If it is the same model as formModel, leave this field as null
     * @var yii\db\ActiveRecord
     */
    public $mainModel = null;
    
    /**
     * Image item width
     * @var integer
     */
    public $imageWidth = 200;
    
    /**
     * Image item height
     * @var integer
     */
    public $imageHeight = 200;
    
    /**
     * The maximum number of images uploaded at one form submit. 
     * 0 means unlimited.
     * @var integer
     */
    public $maxFilesUploaded = 0;
    
    /**
     * The maximum number of model images that can be riched using this behavior.
     * 0 means unlimited.
     * @var integer
     */
    public $maxFilesTotal = 0;
    
    /**
     * Error message for max files uploaded.
     * @var string
     */
    public $maxFilesUploadedErrorMessage;
    
    /**
     * Error message for max files total.
     * @var string
     */
    public $maxFilesTotalErrorMessage;
    
    /**
     * Whether render new images input
     * @var boolean
     */
    public $renderInput = true;
    
    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->i18n->translations['model-gallery'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@matvik/modelGallery/messages',
            'fileMap' => [
                'model-gallery' => 'default.php',
            ],
        ];
        if (!$this->maxFilesUploadedErrorMessage) {
            $this->maxFilesUploadedErrorMessage =  Yii::t('model-gallery', 'Error! Maximum number of files should not exceed {number}');
        }
        if (!$this->maxFilesTotalErrorMessage) {
            $this->maxFilesTotalErrorMessage =  Yii::t('model-gallery', 'Error! Maximum total number of images should not exceed {number}');
        }
        $this->maxFilesUploadedErrorMessage = str_replace('{number}', $this->maxFilesUploaded, $this->maxFilesUploadedErrorMessage);
        $this->maxFilesTotalErrorMessage = str_replace('{number}', $this->maxFilesTotal, $this->maxFilesTotalErrorMessage);
    }
    
    /**
     * @inheritDoc
     */
    public function run()
    {
        if ($this->mainModel === null) {
            $this->mainModel = $this->formModel;
        }
        GalleryFormWidgetAsset::register($this->view);
        $this->view->registerCss(
            ".form-gallery-list li {width: {$this->imageWidth}px; height: {$this->imageHeight}px;}"
        );
        
        // sortable items
        $items = [];
        foreach ($this->mainModel->galleryImages as $image) { 
            $items[] = [
                'content' => $this->render('_formWidgetItem', [
                    'image' => $image,
                    'width' => $this->imageWidth,
                    'height' => $this->imageHeight
                ]),
                'options' =>  [
                    'data-image-id' => $image->id,
                    'class' => 'ui-state-default ui-sortable-handle form-gallery-item',
                ]
            ];
        }
        
        return $this->render('formWidget', [
            'model' => $this->formModel,
            'items' => $items,
            'renderInput' => $this->renderInput,
            'itemWidth' => $this->imageWidth,
            'itemHeight' => $this->imageHeight,
            'maxFilesUploaded' => $this->maxFilesUploaded,
            'maxFilesTotal' => $this->maxFilesTotal,
            'maxFilesUploadedErrorMessage' => $this->maxFilesUploadedErrorMessage,
            'maxFilesTotalErrorMessage' => $this->maxFilesTotalErrorMessage,
        ]);
    }
}