<?php
    error_reporting(E_ALL);
    
    require_once 'vendor\autoload.php';
    use WindowsAzure\Common\ServicesBuilder;
    use WindowsAzure\Common\ServiceException;
    
    if ( array_key_exists( "testfile", $_FILES ) )
    {
        if ( $_FILES["testfile"]["error"]!=0 )
        {
            print_r($_FILES);
            exit("<br>Помилка завантаження файлу. Перевірте розмір файлу і параметри сервера.");
        }
        else
        {
            $connectionString = getenv("CUSTOMCONNSTR_blobConnection");
            $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);
            $content = fopen($_FILES["testfile"]["tmp_name"], "r");
            $blob_name = hash( "sha256", uniqid("awu4hzkf29384hf", true)."jd9hr123794hrf", false );
            $container_name= "files";
            $url = "https://symvolika.blob.core.windows.net/files/".$blob_name;
            
            try
            {
                //Upload blob
                $blobRestProxy->createBlockBlob($container_name, $blob_name, $content);
                
                exit('Uploaded as <a href="'.$url.'">file</a>');
            }
            catch(ServiceException $e){
                $code = $e->getCode();
                $error_message = $e->getMessage();
                echo $code.": ".$error_message."<br />";
            }
        }
    }
?>

<!DOCTYPE html>
<html>
	<title>Магазин військової атрибутики - Символіка</title>
	<meta charset="UTF-8"></meta>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="lib/w3.css"></link>
	<link rel="stylesheet" href="lib/w3-theme-blue.css"></link>
	<link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />

    <body>
        <header class="w3-container w3-card-4 w3-theme">
		    <title>Символіка</title>
			<h1>Магазин військової атрибутики - Символіка</h1>
        </header>
			
			<div class="w3-light-aqua w3-container w3-padding-32 w3-center">
			<h2 class="w3-jumbo">Завантаження файлу</h2>
			</div>

		<ul class="w3-navbar w3-large w3-center w3-theme">
			<li><a href="index.html">Головна</a></li>
			<li><a href="store.html">Магазин</a></li>
			<li><a href="about.html">Про</a></li>
		</ul>
			

        <div class="w3-card-4 w3-margin">

            <div class="w3-container w3-indigo">
				<center><h2>Форма для завантаження</h2></center>
            </div>

            <form class="w3-container" method="POST" action="upload.php" enctype="multipart/form-data">

            <label>Файл для завантаження</label>
            <input class="w3-input" type="file" id="testfile" name="testfile" required>

            <input class="w3-input w3-light-blue" type="submit" Value="ПІДТВЕРДИТИ"> <br>

            </form>

        </div>

		<footer class="w3-container w3-center w3-theme">
		<h5>© 2016 | Крупа Дмитро</h5>
		</footer>
    </body>
</html>