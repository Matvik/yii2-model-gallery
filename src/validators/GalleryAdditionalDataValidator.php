<?php
namespace matvik\modelGallery\validators;

use yii\validators\Validator;
use yii\helpers\Json;

/*
 * Validator for oder and deleting parameters
 */

class GalleryAdditionalDataValidator extends Validator
{

    /**
     * Set true if data is in array format. In other case it will be decoded 
     * from JSON.
     * @var boolean
     */
    public $isArray = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = 'Wrong format';
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!$value) {
            return null;
        }

        $valid = true;
        if (!$this->isArray) {
            $data = Json::decode($value);
            if (!(json_last_error() == JSON_ERROR_NONE)) {
                $valid = false;
            }
        } else {
            $data = $value;
        }
        foreach ($data as $id) {
            if (!is_numeric($id) || $id < 0) {
                $valid = false;
            }
        }

        if (!$valid) {
            return [$this->message, []];
        }
        return null;
    }
}
