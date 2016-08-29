# FileHandler Validators

Validators used to save and upload files

## FileHandlerValidator

Validator to validate uploaded and already existing files on the server.

### Usage

It can be used by being declared inside the `yii\base\Model::rues()` method

```php
public function behaviors()
{
    return [
        ['file', FileHandlerValidator::className()],
    ];
}
```

The options for this validator are

###### folderPath

*string* : `'@web/'`

> folder to store the files.

###### notFoundFileName

*string*

> error message

###### invalidFileName;

*string*

> error message

###### notSavedFile

*string*

> error message

###### fileName

*callable*

> a PHP callable that replaces the default implementation of
> [[fileName()]]. The signature of the callable should be
> ```php
> function (
>     UploadedFile $file,
>     Model $model,
>     FileHandlerValidator $validator
>  )
> ```
