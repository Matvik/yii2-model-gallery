<?php
namespace matvik\modelGallery;

use yii\base\Action;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\validators\ImageValidator;
use matvik\modelGallery\validators\GalleryAdditionalDataValidator;
use Yii;

/**
 * Action for AJAX widget
 */
class GalleryAjaxAction extends Action
{
    
    const ACTION_UPLOAD_IMAGES = 'upload';
    const ACTION_DELETE_IMAGES = 'delete';
    const ACTION_CHANGE_IMAGES_ORDER = 'order';
    
    /**
     * Classname of the Active Record model to which is gallery behavior attached
     * @var string
     */
    public $modelClass;
    
    /**
     * The maximum number of model images that can be riched using this action.
     * 0 means unlimited.
     * It will be thrown Exception when exceeded.
     * @var integer
     */
    public $maxFilesTotal = 0;
    
    /**
     * POST parameter name with data (image files, or deleting ids, or ordering ids)
     * @var string
     */
    public $dataParameter = 'galleryData';
    
    /**
     * Anonimus function for checking access to the model. If not null, will be
     * performed before doing changes. Example:
     * 
     * function ($action, $model) {
     *      if (!Yii::$app->user->can('permissionName', ['model' => $model])) {
     *          throw new \yii\web\ForbiddenHttpException();
     *      } 
     * }
     * 
     * This is alternative access checking way to the overriding beforeAction()
     * 
     * @var \Closure
     */
    public $permissionCheckCallback = null;

    /**
     * Performs uploading, deleting or ordering images for specific model
     * 
     * @param string $action    Type of the action performed (upload, delete, order)
     * @param string $modelId   Main model ID
     * @return array
     * @throws NotFoundHttpException
     */
    public function run($action, $modelId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->modelClass::findOne($modelId);
        if (!$model) {
            throw new NotFoundHttpException("Model with ID $modelId not found");
        }
        
        // access check
        if ($this->permissionCheckCallback !== null) {
            call_user_func($this->permissionCheckCallback, $action, $model);
        }
        
        switch ($action) {
            case self::ACTION_UPLOAD_IMAGES:
                return ['success' => $this->upload($model)];
            
            case self::ACTION_DELETE_IMAGES:
                return ['success' => $this->delete($model)];
                
            case self::ACTION_CHANGE_IMAGES_ORDER:
                return ['success' => $this->order($model)];

            default:
                throw new NotFoundHttpException();
        }
    }
    
    /**
     * Uploading new image to the model
     * @param yii\base\Model $model
     * @return boolean
     * @throws \Exception
     */
    protected function upload($model)
    {
        $image = UploadedFile::getInstanceByName($this->dataParameter);
        $currentCount = $model->getGalleryImages()->count();
        if ($image) {
            if ((int)$this->maxFilesTotal > 0 && ($currentCount + 1) > (int)$this->maxFilesTotal) {
                throw new \Exception('Max images count error');
            } 
        } else {
            return true;
        }
        $validator = new ImageValidator();
        if ($validator->validate($image)) {
            return $model->saveImages([$image]);
        } else {
            return false;
        }
    }
    
    /**
     * Deleting images in model
     * @param yii\base\Model $model
     * @return boolean
     */
    protected function delete($model)
    {
        $data = Yii::$app->request->post($this->dataParameter);
        $validator = new GalleryAdditionalDataValidator(['isArray' => true]);
        if ($validator->validate($data)) {
            return $model->deleteImages($data);
        } else {
            return false;
        }
    }
    
    /**
     * Ordering images in model
     * @param yii\base\Model $model
     * @return boolean
     */
    protected function order($model)
    {
        $data = Yii::$app->request->post($this->dataParameter);
        $validator = new GalleryAdditionalDataValidator(['isArray' => true]);
        if ($validator->validate($data)) {
            return $model->saveOrder($data);
        } else {
            return false;
        }
    }
}
