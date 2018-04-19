<?php
namespace matvik\modelGallery;

use yii\helpers\FileHelper;

/**
 * This is the model class for images table.
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $priority
 * @property string $category
 */
class Image extends \yii\db\ActiveRecord
{

    /**
     * Base path for this image category. It will
     * be extended by main model ID, image ID and size filename.
     * @var string
     */
    public $basePath;

    /**
     * Base URL for this images category. It will
     * be extended by main model ID, image ID and size filename.
     * @var string
     */
    public $baseUrl;

    /**
     * Files extension
     * @var string
     */
    public $extension;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gallery_images}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'item_id', 'priority'], 'required'],
            [['category'], 'string', 'max' => 255],
            [['item_id'], 'integer'],
            [['priority'], 'integer', 'min' => 0],
        ];
    }

    /**
     * URL of specific image size
     * @param string $size Image size ("thumb", "medium", "large", etc)
     * @return string
     */
    public function getUrl($size)
    {
        return $this->baseUrl . '/' . $this->item_id . '/' . $this->id . '/' . $size . '.' . $this->extension;
    }

    /**
     * Path of specific image size
     * @param string $size Image size ("thumb", "medium", "large", etc)
     * @param boolean $makeDir      Whatever to make directory if it not exists
     * @param string $basePath      Custom base path
     * @param string $extension     Custom extension
     * @return string
     */
    public function getFilePath($size, $makeDir = false, $basePath = null, $extension =  null)
    {
        $dir = ($basePath !== null ? $basePath : $this->basePath) . '/' . $this->item_id . '/' . $this->id;
        if ($makeDir) {
            FileHelper::createDirectory($dir, 0777);
        }
        return $dir . '/' . $size . '.' . ($extension !== null ? $extension : $this->extension);
    }
    
    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $path = $this->basePath . '/' . $this->item_id . '/' . $this->id;
            FileHelper::removeDirectory($path);
            return true;
        } else {
            return false;
        }
    }
}
