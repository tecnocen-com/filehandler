Tecnocen-com Yii2 File Handler
=========================

[![Latest Stable Version](https://poser.pugx.org/tecnocen-com/yii2-filehandler/v/stable)](https://packagist.org/packages/tecnocen-com/yii2-filehandler) [![Total Downloads](https://poser.pugx.org/tecnocen-com/yii2-filehandler/downloads)](https://packagist.org/packages/tecnocen-com/yii2-filehandler) [![Latest Unstable Version](https://poser.pugx.org/tecnocen-com/yii2-filehandler/v/unstable)](https://packagist.org/packages/tecnocen-com/yii2-filehandler) [![License](https://poser.pugx.org/tecnocen-com/yii2-filehandler/license)](https://packagist.org/packages/tecnocen-com/yii2-filehandler)

Library to easily handle upload, validation and storage of files using Yii2.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require --prefer-dist "tecnocen/yii2-filehandler:*"
```

or add

```
"tecnocen/yii2-filehandler": "*"
```

to the `require` section of your `composer.json` file.

## Usage

### FileHandlerValidator

`tecnocen\filehandler\validators\FileHandlerValidator` is configured with a
`folderPath`, its meant to vaildate and then save uploaded files into the
`folderPath` or receive an string which will seek as a file on the `folderPath`
and then validate the found file.

```php
use tecnocen\filehandler\validators\FileHandlerValidator;
use yii\base\Model;

class UserImage extends Model
{
    public function rules()
    {
        return [
            [
                ['route'],
                FileHandlerValidator::className(),
                'folderPath' => '@webroot/uploads'
            ]
        ];
    }
}
```

## Documentation

TODO

## License

The BSD License (BSD). Please see [License File](LICENSE.md) for more information.
