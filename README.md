# Simple Custom S3 for Yii2

Simple extension for custom S3 filesystem with basic functionality : read, write, delete.  
This extension use [Flysystem](http://flysystem.thephpleague.com/) as core filesystem.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require bahirul/yii2-simple-s3
```

or add

```
"bahirul/yii2-simple-s3": "~0.0.1"
```

to the `require` section of your `composer.json` file.

## Configuring

Configure application `components` as follows

```php
return [
    //...
    'components' => [
        //...
        's3' => [
            'class' => 'bahirul\s3\Yii2SimpleS3',
            'endpoint' => 'YOUR_S3_URL',
            'key'    => 'YOUR_S3_KEY',
            'secret' => 'YOUR_S3_SECRET',
            'region' => 'YOUR_S3_REGION',
            'version' => 'YOUR_S3_VERSION',
            'bucket' => 'YOUR_S3_BUCKET',
        ],
    ],
];
```

## Usage

### Read file from S3

`readFile()` method will return array with key 'stream' and 'mimetype'.

send as response in controller

```php
// s3 custom init
$s3 = Yii::$app->s3;

// your requested file
$fileRequest = 'example_file_image.jpg';

// read file on S3
$readFile = $s3->readFile($fileRequest);

// init web response
$response = Yii::$app->response;
$response->headers->set('Content-Type', $readFile['mimetype']);
$response->format = \yii\web\Response::FORMAT_RAW;
$response->stream = $readFile['stream'];

return $response->send();
```

or send as file in controller

```php
// s3 custom init
$s3 = Yii::$app->s3;

// your requested file
$fileRequest = 'example_file_image.jpg';

// read file on S3
$readFile = $s3->readFile($fileRequest);

// init web response
$response = Yii::$app->response;

return $response->sendStreamAsFile($readFile['stream'], $fileRequest ,['mimeType' => $readFile['mimetype']]);
```

### Write file to S3

`writeFile()` method will return boolean.

write (upload) usage on controller

```php
// your file upload instance
$fileModel = UploadedFile::getInstance($model, 'attributeName');

$s3 = Yii::$app->s3;

// generate random filename
$filename = time() . '.jpg';

if($model->load($post) && $model->validate()){
    // upload file to s3
    $s3->writeFile($fileModel, $filename);
}
```

### Delete file on S3

`deleteFile()` method will return boolean.

delete usage on controller

```php
$s3 = Yii::$app->s3;

$fileRequest = 'example_file_image.jpg';

$s3->deleteFile($fileRequest);
```