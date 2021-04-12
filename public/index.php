<?php
require_once __DIR__ .'/../vendor/autoload.php';
use App\Storage;

$env = new \Dotenv\Dotenv(basePath());
$env->load();

try{

    $storage = new Storage([
       'region' => getenv('AWS_REGION'),
       'version' => 'latest',
       'bucket' => getenv('AWS_BUCKET_NAME')
    ]);
    
    if(isset($_POST['submit'])){
        $name = $_FILES['file']['name'];
        if(!$name){
            $msg = 'Please select a file';
        }
       $path = $storage->store('avatars/', $name);
    }
    
    if(isset($_GET['delete'])){
        $item = $_GET['delete'];
        if(!$item){
            $msg = 'Item is required';
        }else{
            if($storage->delete($item)){
                $msg = $item . ' deleted successfully';
            }else{
                $msg = 'Item not found';
            }
        }
    }
    
    $bucket_contents = $storage->getBucketContents();
    
}catch (Exception $ex){
    die($ex->getMessage());
}

require_once __DIR__ .'/app.php';