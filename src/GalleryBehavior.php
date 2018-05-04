<?php

namespace matvik\modelGallery;

use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\imagine\Image as ImagineImage;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use matvik\modelGallery\Image;
use matvik\modelGallery\GalleryDefaultImageAsset;
use Yii;

class GalleryBehavior extends Behavior {

    /**
     * Category of images (post, user, product, etc)
     * @var string
     */
    public $category;
    
    /**
     * Base directory of images
     * @var string
     */
    public $extension = 'jpg';
    
    /**
     * @var     array Functions to generate image sizes
     * @note    'preview' & 'original' versions names are reserved for image preview in widgets
     *          and original image files, if it is required - you can override them.
     * @example
     * [
    *  'small' => function ($img) {
    *      return $img->thumbnail(new \Imagine\Image\Box(200, 200));
    *  },
     *  'medium' => function ($img) {
     *      $dstSize = $img->getSize();
     *      $maxWidth = 800;
     *      if ($dstSize->getWidth() > $maxWidth) {
     *          $dstSize = $dstSize->widen($maxWidth);
     *      }
     *      return $img->resize($dstSize);
     * ]
     */
    public $sizes;
    
    /**
     * Base directory of images for this category
     * @var string
     */
    public $basePath;
    
    /**
     * Base URL of images for this category
     * @var string
     */
    public $baseUrl;
    
    /**
     * Temporary directory for original uploaded image files. 
     * Defaults to 'runtime/gallery'.
     * @var string
     */
    public $tempDir;
    
    /**
     * Default images in terms of 'size' => 'url' array
     * @var array
     */
    public $defaultImages = [];

    /**
     * @param ActiveRecord $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);
        
        // default temp dir
        if (!$this->tempDir) {
            $this->tempDir = Yii::getAlias('@runtime') . '/gallery';
        }
        
        // normalization
        $this->basePath = FileHelper::normalizePath($this->basePath, '/'); // directory separator is changed to straight slash for avoiding wrong result in relations query
        $this->baseUrl = rtrim($this->baseUrl, '/\\');
        $this->tempDir = FileHelper::normalizePath($this->tempDir, '/');
        
        // default sizes
        if (!isset($this->sizes['original'])) {
            $this->sizes['original'] = function ($image) {
                return $image;
            };
        }
        if (!isset($this->sizes['preview'])) {
            $this->sizes['preview'] = function ($image) {
                /** @var ImageInterface $image */
                return $image->thumbnail(new Box(200, 200));
            };
        }
    }
    
    //=========================== RELATIONS ====================================
    
    /**
     * Image models relation
     * 
     * @return \matvik\modelGallery\ImageQuery
     */
    public function getGalleryImages() {
        return $this->owner->hasMany(Image::class, ['item_id' => 'id'])
            ->where(['category' => $this->category])
            ->orderBy(['priority' => SORT_ASC])
            ->select([
                '*', 
                'basePath' => new Expression("'" . $this->basePath . "'"),
                'baseUrl' => new Expression("'" . $this->baseUrl . "'"),
                'extension' => new Expression("'" . $this->extension . "'")
            ]);
    }

    /**
     * First image relation. May be useful to show main image of the model.
     * 
     * @return \matvik\modelGallery\ImageQuery
     */
    public function getGalleryImageFirst() {
        return $this->owner->hasOne(Image::class, ['item_id' => 'id'])
            ->where(['category' => $this->category, 'priority' => 0])
            ->select([
                '*', 
                'basePath' => new Expression("'" . $this->basePath . "'"),
                'baseUrl' => new Expression("'" . $this->baseUrl . "'"),
                'extension' => new Expression("'" . $this->extension . "'")
            ]);
    }
    
    //============================= EVENTS =====================================
    
    /**
     * @inheritdoc
     */
    public function events() {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }
    
    /**
     * Delete all images when deleting parent model
     */
    public function beforeDelete() {
        foreach($this->owner->galleryImages as $image) {
            $image->delete();
        }
    }
    
    //========================= PUBLIC METHODS =================================
    
    /**
     * Returns first model image URL for size. 
     * If no images found for this model - returns default image, defined in 
     * defaultImages array. 
     * If no default image defined for this size - returns default image from 
     * extension folder.
     * 
     * @param string $size
     * @return string
     */
    public function getGalleryImageFirstUrl($size = 'preview')
    {
        $firstImage = $this->owner->galleryImageFirst;
        if ($firstImage) {
            return $firstImage->getUrl($size);
        }
        
        // default image
        if (array_key_exists($size, $this->defaultImages)) {
            return $this->defaultImages[$size];
        }
        
        // default image from extension folder
        $bundle = GalleryDefaultImageAsset::register(Yii::$app->view);
        return $bundle->baseUrl . '/default.png';
    }
    
    /**
     * Saves new images from UploadedFile or existed files sources.
     * Priority is set after existed images
     * 
     * @param \yii\web\UploadedFile[]|string[] $files
     * @return boolean
     */
    public function saveImages($files)
    {
        // getting current max priority
        $maxPriority = -1;
        foreach($this->owner->galleryImages as $image) {
            $maxPriority = $image->priority > $maxPriority ? $image->priority : $maxPriority;
        }
        // saving files
        $success = true;
        foreach ($files as $key => $file) {
            if (!$this->saveNewImage($file, $maxPriority + $key + 1)) {
                $success = false;
            }
        }
        return $success;
    }
    
    /**
     * Delete images with specific ids
     * @param array $ids 
     * @return boolean
     */
    public function deleteImages(array $ids) {
        $images = $this->owner->galleryImages;
        $images = ArrayHelper::index($images, 'id');
        $success = true;
        foreach ($ids as $id) {
            if (array_key_exists($id, $images)) {
                if (!$images[$id]->delete()) {
                    $success = false;
                }
            }
        }
        $this->normalizePriority();
        return $success;
    }
    
    /**
     * Save new images order
     * @param array $ids    Images IDs in new order 
     *                      (for example, [12, 45, 789, 23])
     * @return boolean
     */
    public function saveOrder(array $ids)
    {
        $images = $this->owner->galleryImages;
        $images = ArrayHelper::index($images, 'id');
        $success = true;
        foreach ($ids as $key => $id) {
            if (array_key_exists($id, $images)) {
                $images[$id]->priority = $key;
                if (!$images[$id]->save()) {
                    $success = false;
                }
            }
        }
        $this->normalizePriority();
        return $success;
    }
    
    /**
     * Regenerates images for this model using one of existing sizes as source. 
     * May be useful after changing behavior parameters like path, sizes, etc.
     * 
     * ATTENTION! It is strongly recommended to make images and database backup
     * before doing this!
     *  
     * @param string $sourceSize    Sizename that will be used as source for
     *                              newly generated images
     * @param string $oldBasePath   Old base bath for behavior. Set this param 
     *                              after changing basePath parameter of this behavior.
     * @param string $oldExtension  Old extension for behavior. Set this param 
     *                              after changing extension parameter of this behavior.
     * @return boolean
     */
    public function regenerateImages($sourceSize = 'original', $oldBasePath = null, $oldExtension = null)
    {
        $success = true;
        $oldImages = $this->owner->galleryImages;
        if ($oldBasePath === null) {
            $oldBasePath = $this->basePath;
        }
        if ($oldExtension === null) {
            $oldExtension = $this->extension;
        }
        // generating new images
        foreach ($oldImages as $oldImage) {
            if (
                !$this->saveNewImage(
                    $oldImage->getFilePath($sourceSize, false, $oldBasePath, $oldExtension), 
                    $oldImage->priority
                )) {
                $success = false;
            }
        }
        // deleting old images
        if (!$this->deleteImages(ArrayHelper::getColumn($oldImages, 'id'))) {
            $success = false;
        }
        return $success;
    }
    
    //========================= PROTECTED METHODS ==============================
    
    /**
     * Saves new image from uploaded file or existing one
     * 
     * @param \yii\web\UploadedFile|string $file    
     * @param int $priority                         This image priority
     * @return boolean
     */
    protected function saveNewImage($file, $priority)
    {
        // saving image model to database
        $imageModel = new Image([
            'basePath' => $this->basePath, 
            'baseUrl' => $this->baseUrl,
            'extension' => $this->extension,
            'item_id' => $this->owner->id,
            'category' => $this->category,
            'priority' => $priority
        ]);
        if (!$imageModel->save()) {
            return false;
        }
        
        // save original image, if it is an instance of yii\web\UploadedFile
        if ($file instanceof yii\web\UploadedFile) {
            FileHelper::createDirectory($this->tempDir, 0777);
            $originalFilePath = $this->tempDir . '/' . md5(uniqid(rand(), true)) . '.' . $file->extension;
            $file->saveAs($originalFilePath);
        } else {
            $originalFilePath = $file;
        }
        
        // get Imagine object
        /** @var ImageInterface $originalImage */
        $originalImage = ImagineImage::getImagine()->open($originalFilePath);
        
        // save sizes
        foreach ($this->sizes as $size => $callback) {
            /** @var ImageInterface $img */
            $img = call_user_func($callback, $originalImage->copy());
            $path = $imageModel->getFilePath($size, true);
            $img->save($path);
        }
        
        // delete original image from temp folder
        if ($file instanceof yii\web\UploadedFile && file_exists($originalFilePath)) {
            chmod($originalFilePath, 0777);
            unlink($originalFilePath);
        }
        
        return true;
    }
    
    /**
     * Makes images priority normalized.
     * For example: [1, 2, 5, 6] => [0, 1, 2, 3].
     */
    protected function normalizePriority()
    {
        unset($this->owner->galleryImages);
        $images = $this->owner->galleryImages;
        foreach ($images as $key => $image) {
            $image->priority = $key;
            $image->save();
        }
    }
}
