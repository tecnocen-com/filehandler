<?php

namespace tecnocen\filehandler\actions;

use Yii;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * CreateAction implements the API endpoint for creating a new model from the given data.
 *
 * @author Angel (Faryshta) Guevara
 */
class RestFileUploadCreateAction extends \yii\rest\CreateAction
{
    /**
     * @var array list of files to be received and the attributes where they
     * will be saved. The key is the name of the attribute and the value is the
     * file that was received. If no key is provided then the attribute and file
     * will be the same as the array value.
     *
     * Example:
     * ```php
     * 'files' => [
     *     'avatar' => 'small_img' // the small_img file will be saved as avatar
     *     'portrait' // the portrait file will be saved as portrait
     * ]
     * ```
     */
    public $files = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }
        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

        foreach ($this->files as $attribute => $target) {
            if (is_int($attribute)) {
                $attribute = $target;
            }
            $model->$attribute = UploadedFile::getInstanceByName($target);
        }
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set(
                'Location',
                Url::toRoute([$this->viewAction, 'id' => $id], true)
            );
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException(
                'Failed to create the object for unknown reason.'
            );
        }
        return $model;
    }
}
