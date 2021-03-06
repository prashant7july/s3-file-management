<?php

namespace App;
use Aws\Sdk;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

class Storage
{
    private $config, $client, $setUpData;
    
    public function __construct(array $config)
    {
        if(!count($config)){
            throw new \Exception('config is required');
        }
        $this->config = $config;
        $this->setUpData = [
            'region' => $this->config['region'],
            'version' => $this->config['version'],
        ];
        
        $this->client = $this->createS3Client();
    }
    
    public function createS3Client()
    {
        $sdk = new Sdk($this->setUpData);
        return $sdk->createS3();
    }
    
    /**
     * @param $bucket_name
     * @return \Aws\Result|string
     */
    public function createBucket($bucket_name)
    {
    
        if(!$bucket_name){
            return 'Bucket name is required';
        }
        try{
            return $this->client->createBucket([
               'Bucket' => $bucket_name
            ]);
        }catch (AwsException $ex){
            die($ex->getMessage());
        }
    }
    
    /**
     * Save file to s3
     * @param null $folder
     * @param null $filename
     * @return null|string
     */
    public function store($folder = null, $filename = null)
    {
        if($filename === null){
            $filename = md5(microtime());
            $fileExt = guestFileExtension();
            $filename = $filename .'.'.$fileExt;
        }
        
        if($folder === null){
            $path = $filename;
        }else{
            $path = $folder.$filename;
        }
        try{
            $this->client->putObject([
                'Bucket' => $this->config['bucket'],
                'Key' => $path,
                'SourceFile' => getFileTmpLocation(),
                'StorageClass' => 'STANDARD',
                'CacheControl' => 'max-age=86400',
                'ACL' => 'public-read'
            ]);
        }catch (AwsException $ex){
            die($ex->getMessage());
        }
        
        return $path;
    }
    
    /**
     * Get items in a bucket
     * @param null $bucket_name
     * @param string $folder
     * @return \Iterator
     */
    public function getBucketContents($bucket_name = null, $folder = '')
    {
        if($bucket_name === null){
            $bucket = $this->config['bucket'];
        }else{
            $bucket = $bucket_name;
        }
        try{
            return $this->client->getIterator('ListObjects', [
                'Bucket' => $bucket,
                'Prefix' => $folder
            ]);
        }catch (AwsException $ex){
            die($ex->getMessage());
        }
    }
    
    /**
     * Get a list of buckets
     * @return mixed
     */
    public function getBuckets()
    {
        return $this->client->listBuckets()['Buckets'];
    }
    
    /**
     * Get the url for the specified object
     * @param $key
     * @param null $bucket_name
     * @return string
     */
    public function getUrl($key, $bucket_name = null)
    {
        if($bucket_name === null){
            $bucket = $this->config['bucket'];
        }else{
            $bucket = $bucket_name;
        }
        
        try{
            return $this->client->getObjectUrl($bucket, $key);
        }catch (AwsException $ex){
            die($ex->getMessage());
        }
        
    }
    
    /**
     * Get the specified object
     * @param $key
     * @param null $bucket_name
     * @return \Aws\Result
     */
    public function getOneObject($key, $bucket_name = null)
    {
        if($bucket_name === null){
            $bucket = $this->config['bucket'];
        }else{
            $bucket = $bucket_name;
        }
        try{
            return $this->client->getObject([
               'Bucket' => $bucket,
               'Key' => $key
            ]);
        }catch (AwsException $ex){
            die($ex->getMessage());
        }
    }
    
    /**
     * Delete the specified resource or object
     * @param $key
     * @param null $bucket_name
     * @return bool
     */
    public function delete($key, $bucket_name = null)
    {
        if($bucket_name === null){
            $bucket = $this->config['bucket'];
        }else{
            $bucket = $bucket_name;
        }
        
        if(!$key){
            return false;
        }
        
        try{
            $this->client->deleteObject([
               'Bucket' => $bucket,
               'Key' => $key
            ]);
        }catch (AwsException $ex){
            die($ex->getMessage());
        }
        
        return true;
    }
}