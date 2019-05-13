<?php
require_once 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

// local..
$account_name = "bloblab1";
$account_key = "vWVF8P5khRgCM0tiojqxjBZg2Qo+o53RVD6RZ+KSIRVHMmlgUbS0dn16QSiPTGl8MdL9wCSBJy34E8b4hlmRWw==";
putenv("ACCOUNT_NAME=$account_name");
putenv("ACCOUNT_KEY=$account_key");

$connectionString = "DefaultEndpointsProtocol=https;AccountName=".getenv('ACCOUNT_NAME').";AccountKey=".getenv('ACCOUNT_KEY');

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

$containerName = "photos";
$photos = [];

if (isset($_POST['submit'])) {

    $file_upload = $_FILES['file_upload'];
    if ($file_upload["error"] == UPLOAD_ERR_OK) {

        $fileToUpload = $file_upload['name'];
        if (!move_uploaded_file($file_upload['tmp_name'], $file_upload['name'])) {
            die ("Error upload file");
        }

        try {
            $content = fopen($fileToUpload, "r");

            //Upload blob
            $blobClient->createBlockBlob($containerName, $fileToUpload, $content);            
        }
        catch (ServiceException $e) {
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
        catch (InvalidArgumentTypeException $e) {
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
         
    }
    header("location: index.php");
    
} else {
    try {
        // List blobs.
        $listBlobsOptions = new ListBlobsOptions();

        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
                $photos[] = ['name' => $blob->getName(), 'url' => $blob->getUrl()];
            }
        
            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());    
        
    }
    catch (ServiceException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch (InvalidArgumentTypeException $e) {
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}

?>
<html>
<head>
<title>Azure Cognitive Services</title>
</head>
<body>
<h1>Azure Cognitive Services</h1>
<hr>
<b>Upload Image</b>
<form method="post" enctype="multipart/form-data">
<label>Upload file image</label><br>
<input type="file" name="file_upload">
<input type="submit" name="submit" value="Upload">
</form>

<h2>List Photos</h2>
<p>Click the image to analyze</p> 
<?php foreach($photos as $photo): #print_r($photo); ?>
<a href="analisa.php?url=<?php echo $photo['url']; ?>">
<img src="<?php echo $photo['url']; ?>" style="max-width:150px;">
</a>
<?php endforeach; ?>
</body>
</html>