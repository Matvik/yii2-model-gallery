<?php

namespace matvik\modelGallery;

use yii\web\AssetBundle;

class JqueryAjaxFileUploaderAsset extends AssetBundle
{
    public $sourcePath = '@bower/dm-file-uploader/src';
    public $css = [
    ];
    
    public $js = [
        'js/jquery.dm-uploader.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}