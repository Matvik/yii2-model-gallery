<?php

namespace matvik\modelGallery;

use yii\web\AssetBundle;

class JqueryConfirmAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-confirm2/dist';
    public $css = [
        'jquery-confirm.min.css',
    ];
    
    public $js = [
        'jquery-confirm.min.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}