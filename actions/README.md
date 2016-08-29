# FileHandler Actions

This are MVC actions that are used to handle files easily.

Their purpose is to create a way to download and upload files allowing
authentification and checking credentials such as RBAC.

## ActiveDownloadAction

This action can be used to download files in a secure way, getting the files
from non-public folders and asking credentials before allowing you to download
them.

### Usage

It can be used by being declared inside the `yii\base\Controller::actions()` method

```php
public function actions()
{
    return [
        'download' => [
            'class' => ActiveDownloadAction::className(),
            'modelClass' => 'app\\models\\UserFile'
        ]
    ];
}
```

The options for this action are

###### forceDownload

*boolean* : `false`

> if you want the file to be downloaded directly without asking.

###### forceDownloadParam

*string* : `'forceDownload'`

> parameter in the url that decides if the file must be downloaded without
> asking.
>
> if `forceDownload` is set to `true` then this parameter will be ignored.

###### findModel

*callable*

> callable function to find the model
>
> It must have the signature
>
> ```php
> function (mixed $id): BaseActiveRecord
> ```
>
> Where:
>
> - $id is a mixed parameter
>
> and must return an instance of BaseActiveRecord

###### checkAccess

*callable*

> checks if the logged or anonymous user can access the
> model or throws exceptions otherwise.
>
> It must have the signature
>
> ```php
> function (BaseActiveRecord $model)
> ```
>
> Where:
>
> - $model is an instance of `BaseActiveRecord`

## RestFileUploadCreateAction

Action for yii2 rest that allows you to configure files to be uploaded.

It is very similar to `yii\rest\CreateAction` only one parameter is added

###### files

*array*

> array list of files to be received and the attributes where they
> will be saved. The key is the name of the attribute and the value is the
> file that was received. If no key is provided then the attribute and file
> will be the same as the array value.
>
> Example:
> ```php
> 'files' => [
>     'avatar' => 'small_img' // the small_img file will be saved as avatar
>     'portrait' // the portrait file will be saved as portrait
> ]
> ```
