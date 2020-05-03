<?php
namespace bahirul\yii2;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidArgumentException;
use yii\web\NotFoundHttpException;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class SimpleS3 extends Component
{
	public $endpoint;

	public $key;

	public $secret;

	public $region = 'ID'; // Default value is `ID`

	public $version = 'latest' ; // Default value is `latest`

	public $bucket;

	private $filesystem;

	
	public function init()
	{
		if($this->endpoint === null){
			throw new InvalidConfigException('"endpoint" property must be set.');
		}

		if($this->key === null){
			throw new InvalidConfigException('"key" property must be set.');
		}

		if($this->secret === null){
			throw new InvalidConfigException('"secret" property must be set.');
		}

		if($this->region === null){
			throw new InvalidConfigException('"region" property must be set.');
		}

		if($this->version === null){
			throw new InvalidConfigException('"version" property must be set.');
		}

		if($this->bucket === null){
			throw new InvalidConfigException('"bucket" property must be set.');
		}

		// build S3 Configuration
		$s3Config = [
			'endpoint' => $this->endpoint,
			'credentials' => [
				'key' => $this->key,
				'secret' => $this->secret,
			],
			'region' => $this->region,
			'version' => $this->version,
		];


		// init Filesystem Adapter
		$s3Client = new S3Client($s3Config);
		$adapter = new AwsS3Adapter($s3Client, $this->bucket);
		$this->filesystem = new Filesystem($adapter);

		parent::init();
	}


	public function readFile($filename)
	{
		if($filename === null){
			throw new InvalidArgumentException('filename argument must be passed.');
		}

		if(!$this->filesystem->has($filename)){
			throw new NotFoundHttpException("file not found.");
		}else{
			$stream = $this->filesystem->readStream($filename);
			$mimetype = $this->filesystem->getMimetype($filename);

			return [
				'mimetype' => $mimetype,
				'stream' => $stream
			];
		}
	}


	public function writeFile($fileInstance, $filename='')
	{
		if($fileInstance === null){
			throw new InvalidArgumentException('fileInstance argument must be passed.');
		}

		if(($fileInstance instanceof \yii\web\UploadedFile) == false){
			throw new InvalidArgumentException('fileInstance argument must be instance of yii\web\UploadedFile.');
		}

		$writeFilename = $filename ?? $file->name;

		if($fileInstance->error === UPLOAD_ERR_OK && !$this->filesystem->has($writeFilename)){

			$stream = fopen($fileInstance->tempName, 'r+');
			$this->filesystem->writeStream($writeFilename, $stream);
			fclose($stream);

			return true;
		}else{
			return false;
		}
	}


	public function deleteFile($filename)
	{
		if($filename === null){
			throw new InvalidArgumentException('filename argument must be passed.');
		}

		if(!$this->filesystem->has($filename)){
			throw new NotFoundHttpException("file not found.");
		}else{
			return $this->filesystem->delete($filename);
		}
	}
}