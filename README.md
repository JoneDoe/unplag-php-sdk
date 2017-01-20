# unplag-php-sdk

**Stability: beta**

PHP SDK for Unplag.com API 2.0+.  
SDK implements API methods in PHP OOP way.

## Installation
#### Using composer
Package is not submitted to packagist.org, so you should add github repo to composer config
```bash
#Add unplag sdk repository
php composer.phar config repositories.unplag-sdk git https://github.com/Unplag/unplag-php-sdk

#Require unplag sdk
php composer.phar require unplag/unplag-php-sdk
```

## Usage
```php
require_once 'vendor/autoload.php';


//create unplag client
$unplag = new Unplag\Unplag('YOUR-API-KEY', 'YOUR-API-SECRET');

//upload file
$file = $unplag->fileUpload(\Unplag\PayloadFile::bin($testText), 'txt');

//start check
$checkParam = new \Unplag\Check\CheckParam($file['id']);
$checkParam->setType(\Unplag\Check\CheckParam::TYPE_WEB);
$check = $unplag->checkCreate($checkParam);

var_dump($check);
```

## Help and docs

- [Unpalg API Documentation](https://unplag.com/api/doc)