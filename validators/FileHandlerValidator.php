<?php

namespace tecnocen\filehandler\validators;

use SPLFileInfo;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use yii\base\Model;

/**
 * @author Angel (tecnocen-com) Guevara <aguevara@tecnocen.com>
 */
class FileHandlerValidator extends \yii\validators\FileValidator
{
    /**
     * @var string folder to store the files.
     */
    public $folderPath = '@web/';

    /**
     * @var string error message
     */
    public $notFoundFileName;

    /**
     * @var string error message
     */
    public $invalidFileName;

    /**
     * @var string error message
     */
    public $notSavedFile;

    /**
     * @var callable a PHP callable that replaces the default implementation of
     * [[fileName()]]. The signature of the callable should be
     * ```php
     * function (
     *     UploadedFile $file,
     *     Model $model,
     *     FileHandlerValidator $validator
     *  )
     * ```
     */
    public $fileName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->notFoundFileName === null) {
            $this->notFoundFileName = 'The file "{fileName}" was not found on folder "{folderPath}".';
        }

        if ($this->invalidFileName === null) {
            $this->invalidFileName = 'The file name "{fileName}" is not valid.';
        }

        if ($this->notSavedFile === null) {
            $this->notSavedFile = 'The file could not be saved to "{folderPath}" error: "{error}".';
        }
    }

    /**
     * @inheritdoc
     */
    public function isEmpty($value, $trim = false)
    {
        return in_array($value, [null, false, ''], true)
            || ($value instanceof UploadedFile
                && $value->error == UPLOAD_ERR_NO_FILE
            );
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if (!$model->hasErrors($attribute)
            && $model->$attribute instanceof UploadedFile
        ) {
            $file = $model->$attribute;
            $model->$attribute = $this->fileName($file, $model);
            if (!$file->saveAs(Yii::getAlias(
                "{$this->folderPath}/{$model->$attribute}"
            ))) {
                $this->addError($model, $attribute, $this->notSavedFile, [
                    'folderPath' => $this->folderPath,
                    'error' => $file->error,
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function validateValue($file)
    {
        if (is_string($file) && !empty($file)) {
            if (in_array($file[0], ['/', '.'])) {
                return [$this->invalidFileName, ['fileName' => $file]];
            }

            $fileInfo = new SPLFileInfo(
                Yii::getAlias($this->folderPath . $file)
            );

            if (!$fileInfo->isFile()) {
                return [$this->notFoundFileName, [
                    'folderPath' => $this->folderPath,
                    'fileName' => $file,
                ]];
            }

            if (!empty($this->extensions)
                && !$this->validateFileInfoExtension($fileInfo)
            ) {
                return [$this->wrongExtension, [
                    'file' => $file,
                    'extensions' => implode(', ', $this->extensions)
                ]];
            }

            return null;
        } elseif ($file instanceof UploadedFile) {
            // execute previos funcionality
            return parent::validateValue($file);
        }
        return [$this->invalidFileName, ['fileName' => '']];
    }

    /**
     * Generates the name which will be used to store
     * @param UploadedFile $file
     * @return string;
     */
    public function fileName(UploadedFile $file, Model $model)
    {
        if (!empty($this->fileName)) {
            return call_user_func($this->fileName, $file, $model, $this);
        }

        return $file->name;
    }

    /**
     * Validates the extension of an a file already inside our server,
     * theorically no file should be saved if its corrupted this is meant for
     * files loaded from other sources such as git or when the validation rule
     * has been changed.
     * @param SPLFileInfo $fileInfo
     * @return boolean if the validation passed.
     */
    public function validateFileInfoExtension(SPLFileInfo $fileInfo)
    {
        if ($this->checkExtensionByMimeType) {
            $mimeType = FileHelper::getMimeType(
                $fileInfo->getRealPath(),
                null,
                false
            );

            if ($mimeType === null) {
                return false;
            }

            if (!in_array(
                $fileInfo->getExtension(),
                FileHelper::getExtensionsByMimeType($mimeType),
                true
            )) {
                return false;
            }
        }

        if (!in_array($fileInfo->getExtension(), $this->extensions, true)) {
            return false;
        }

        return true;
    }
}
