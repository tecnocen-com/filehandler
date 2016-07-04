<?php

namespace tecnocen\filehandler\actions;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;

/**
 * Handles the download of a file obtained from an active record.
 *
 * It must be configured on the `\yii\web\Controller::actions()` method.
 *
 * ```php
 * public function actions()
 * {
 *     return [
 *         'download' => [
 *             'class' => ActiveDownloadAction::className(),
 *             // Optionals.
 *             'modelClass' => $this->modelClass,
 *             'findModel' => [$this, 'findModel'],
 *             'checkAccess' => [$this, 'checkAccess']
 *         ]
 *     ];
 * }
 * ```
 *
 * @param string $modelClass
 * @param boolean $forceDownload
 * @param string $forceDownloadParam
 * @param callable $findModel
 * @param callable $checkAccess
 * @author Angel (Faryshta) Guevara <aguevara@tecnocen.com>
 */
class ActiveDownloadAction extends \yii\web\Action
{
    /**
     * @var string
     */
    public $modelClass;

    /**
     * @var boolean if
     */
    public $forceDownload = false;

    /**
     * @var string parameter in the url that decides if the file must be
     * downloaded without asking.
     *
     * if `forceDownload` is set to `true` then this parameter will be ignored.
     */
    public $forceDownloadParam = 'forceDownload';

    /**
     * @var callable function to find the model
     *
     * It must have the signature
     *
     * ```php
     * function (mixed $id): BaseActiveRecord
     * ```
     *
     * Where:
     *
     * - $id is a mixed parameter
     *
     * and must return an instance of BaseActiveRecord
     */
    public $findModel;

    /**
     * @var callable checks if the logged or anonymous user can access the
     * model or throws exceptions otherwise.
     *
     * It must have the signature
     *
     * ```php
     * function (BaseActiveRecord $model)
     * ```
     *
     * Where:
     *
     * - $model is an instance of `BaseActiveRecord`
     */
    public $checkAccess;

    /**
     * @inheritdoc
     */
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

    /**
     * Finds the model to be operated base on the id.
     *
     * @param mixed $id
     * @return BaseActiveRecord
     * @see $modelClass
     * @see $findModel
     */
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

    /**
     * Checks if the user has the permissions to acces the model.
     *
     * @param BaseActiveRecord $model
     * @see $checkAccess
     */
    protected function checkAccess(BaseActiveRecord $model)
    {
        if ($this->checkAccess !== null) {
            call_user_func($this->checkAccess, $model);
        }
    }

    /**
     * @return boolean if the file will be downloaded directly without asking
     */
    protected function forceDownload()
    {
        return $this->forceDownload
            || (bool) Yii::$app->request->get(
                $this->forceDownloadParam,
                false
            );
    }

    /**
     * Find the model and returns the file using the
     * `\yii\web\Response::sendFile()` method
     *
     * @param mixed $id
     * @return \yii\web\Response
     */
    public function run($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess($model);

        return Yii::$app->response->sendFile(
            $model->getFilePath(),
            $model->getFileName(),
            [
                'mimeType' => $model->getMimeType(),
                'inline' => !$this->forceDownload()
            ]
        );
    }
}
