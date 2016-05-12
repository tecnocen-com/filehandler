<?php

namespace tecnocen\filehandler\tests\data;

use tecnocen\filehandler\validators\FileHandlerValidator;

class FileModel extends \yii\base\Model
{
    public $route;

    public function rules()
    {
        return [
            [
                'route',
                FileHandlerValidator::className(),
                'folderPath' => '@testFilePath/',
            ]
        ];
    }
}
