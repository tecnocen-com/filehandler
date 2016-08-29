# FileHandler Behavior

This behaviors implement events and validations needed to handle files.


## FileHandlerBehavior

Behavior to handle secure uploads and access to files. It automatically adds
a validation rule to the `owner` model and injects methods to be used by
the `tecnocen\filehandler\actions\ActiveDownloadAction` class.

### Usage

It can be used by being declared inside the `yii\base\Model::behaviors()` method

```php
public function behaviors()
{
    return [
        'fileHandler' => [
            'class' => FileHandlerBehavior::className(),
        ]
    ];
}
```

The options for this behavior are

###### rule

*FileHandlerValidator|array|boolean* : `[]`

> list of files to be received and the attributes where they
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
