<?php

namespace tecnocen\filehandler\actions;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;

class ActiveDownloadAction extends \yii\web\Action
{
    public $modelClass;

    public $forceDownload = false;

    public $forceDownloadParam = 'forceDownload';

    public $findModel;

    public $checkAccess;

    public function init()
    {
        if ($this->findModel !== null && !is_callable($this->findModel)) {
            throw new InvalidConfigException(
                'The `findModel` parameter must be callable'
            );
        }

        if ($this->checkAccess !== null && !is_callable($this->checkAccess)) {
            throw new InvalidConfigException(
                'The `checkAccess` parameter must be callable.'
            );
        }

        if ($this->modelClass !== null && !is_subclass_of(
            $this->modelClass,
            BaseActiveRecord::className()
        )) {
            throw new InvalidConfigException(
                'The `modelClass` parameter must extends the `BaseActiveRecord` class.'
            );
        }

        if ($this->modelClass === null && $this->findModel === null) {
            throw new InvalidConfigException(
                'At least one of `modelClass` or `findModel` parameters must be defined.'
            );
        }
    }

    protected function findModel($id)
    {
        if ($this->findModel !== null) {
            return call_user_func(
                $this->findModel,
                $id
            );
        }

        $modelClass = $this->modelClass;
        return $modelClass::findOne($id);
    }

    protected function checkAccess($model)
    {
        if ($this->checkAccess !== null) {
            call_user_func($this->checkAccess, $model);
        }
    }

    public function run($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($model);

        return Yii::$app->response->sendFile(
            $model->getFilePath(),
            $model->getFileName(),
            [
                'mimeType' => $model->mimeType(),
                'inline' => !$this->forceDownload
                    || !Yii::$app->request->get(
                        $this->forceDownloadParam,
                        false
                    )
            ]
        );
    }
}
