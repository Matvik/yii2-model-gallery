<?php
namespace matvik\modelGallery;

use yii\base\Action;
use yii\web\Response;
use Yii;

/**
 * Action for AJAX widget
 */
class GalleryAjaxAction extends Action
{
    
    /**
     * @inheritdoc
     */
    public function run($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $data = (int)$request->post('modelId');
        $deactivate = $request->post('deactivate');

        
    }
}
