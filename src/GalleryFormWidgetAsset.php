<?php

namespace matvik\modelGallery;

use yii\web\AssetBundle;

class GalleryFormWidgetAsset extends AssetBundle
{
    public $sourcePath = '@matvik/modelGallery/assets';
    public $css = [
        'css/form-widget.css'
    ];
    
    public $js = [
        'js/form-widget.js'
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
    ];
}