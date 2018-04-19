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
        $data = Json::decode($value);
        if (!(json_last_error() == JSON_ERROR_NONE)) {
            $valid = false;
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
