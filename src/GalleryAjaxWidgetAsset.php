<?php

namespace matvik\modelGallery;

use yii\web\AssetBundle;

class GalleryAjaxWidgetAsset extends AssetBundle
{
    public $sourcePath = '@matvik/modelGallery/assets';
    public $css = [
        'css/ajax-widget.css'
    ];
    
    public $js = [
        'js/ajax-widget.js'
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
        'matvik\modelGallery\JqueryConfirmAsset',
        'matvik\modelGallery\JqueryAjaxFileUploaderAsset',
    ];
}