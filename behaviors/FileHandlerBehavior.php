<?php

namespace tecnocen\filehandler\behaviors;

use Yii;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;

/**
 * Behavior to handle secure uploads and access to files. It automatically adds
 * a validation rule to the `owner` model and injects methods to be used by
 * the `tecnocen\filehandler\actions\ActiveDownloadAction` class.
 *
 * @property FileHandlerValidator|array|boolean $rule
 * @property string $fileAttribute
 * @property string $folderPath
 * @author Angel (Faryshta) Guevara <aguevara@tecnocen.com>
 */
class FileHandlerBehavior extends \yii\base\Behavior
{
    /**
     * @var FileHandlerValidator|array|boolean the rule to be attachedk to the
     * `owner`. If you want ot disable it, just set this property to `false`.
     */
    public $rule = [];

    /**
     * @var string attribute name on the `owner` to handle the file.
     */
    public $fileAttribute = 'file';

    /**
     * @var string aliased path for the files to be stored. If you want the
     * files to be stored securely, then set this path to an unpublished folder
     * like a `@common` or any of its subfolders.
     */
    public $folderPath = '@webroot/';

    /**
     * @var callable method to be used when the file name is requested.
     * it mus have the signature
     *
     * ```php
     * function (ActiveRecord $model, FileHandlerBehavior $behavior)
     * ```
     *
     * Where
     *
     * - $model is the model where this behavior was attachedk
     * - $behavior is this behavior itself.
     */
    public $fileName;

    /**
     * @var callable method to be used when the mimetype is requested.
     * it mus have the signature
     *
     * ```php
     * function (ActiveRecord $model, FileHandlerBehavior $behavior)
     * ```
     *
     * Where
     *
     * - $model is the model where this behavior was attachedk
     * - $behavior is this behavior itself.
     */
    public $mimeType;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->folderPath)) {
            throw new InvalidConfigException(
                'The `folderPath` property can not be blank.'
            );
        }

        if (empty($this->fileAttribute)) {
            throw new InvalidConfigException(
                'The `fileAttribute` property can not be blank.'
            );
        }

        if (!$this->owner->hasAttribute($this->fileAttribute)) {
            throw new InvalidConfigException(
                'The class ' . $this->owner->className()
                    . " does not have an `$this->fileAttribute` attribute."
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_INIT => 'attachValidator',
        ];
    }

    /**
     * @return string full path where the file is being stored.
     *
     * @see Yii::getAlias()
     */
    public function getFilePath()
    {
        return Yii::getAlias($this->folderPath . $this->getAttributeValue());
    }

    /**
     * @return string the value of the `fileAttribute` attribute in the `owner`.
     */
    protected function getAttributeValue()
    {
        $fileAttribute = $this->fileAttribute;
        return $this->owner->$fileAttribute;
    }

    /**
     * @return string|null
     */
    public function getFileName()
    {
        if ($this->filePath !== null) {
            return call_user_func($this->fileName, $this->owner, $this);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getMimeType()
    {
        if ($this->mimeType !== null) {
            return call_user_func($this->mimeType, $this->owner, $this);
        }

        return null;
    }

    /**
     * Method triggered when the `owner` is initialized. It attach a validators
     * to the `owner` to handle loading and validation.
     * @param $event
     */
    public function attachValidator($event)
    {
        if ($this->rule === false) {
            return ;
        }

        $$this->owner->validators[] = $this->createValidator();
    }

    /**
     * Creates a validator based on the `rule` property.
     *
     * @throws InvalidConfigException when the validator could not be created.
     * @return FileHandlerValidator
     */
    protected function createValidator()
    {
        $rule = $this->rule;
        if (is_object($rule)) {
            return $this->checkValidator($rule);
        }

        if (is_array($rule)) {
            $rule['folderPath'] = $this->folderPath;
            // the nested cass is to
            return $this->checkValidator(Validator::createValidator(
                ArrayHelper::remove(
                    $rule,
                    'class',
                    FileHandlerValidator::className()
                ),
                $this->fileAttribute,
                $rule
            ));
        }

        throw new InvalidConfigException(
            'Unrecognized type for `rule` property.'
        );
    }

    /**
     * Checks that the given validator is an instance of FileHandlerValidator.
     * @throws InvalidConfigException when the given validator is not valid.
     * @return FileHandlerValidator the given validator.
     */
    protected function checkValidator($validator)
    {
        if (!$validator instanceof FileHandlerValidator) {
            throw new InvalidConfigException('The `rule` property must be an '
                . 'instance of `'
                    . FileHandlerValidator::className()
                . '`, a configuration array or `false` to disable it.'
            );
        }
        return $validator;
    }
}
