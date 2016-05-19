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
				<center>
					<span id="registration"></span>
					<button id="login" class="w3-btn w3-theme-l3">Увійти</button>				
					<p id="log"><p>
				</center>
        </header>
			
			<div class="w3-light-aqua w3-container w3-padding-32 w3-center">
			<h2 class="w3-jumbo">Завантаження файлу</h2>
			</div>
		<center>
		<ul class="w3-navbar w3-large w3-center w3-theme">
		<center>
			<li><a href="index.html">Головна</a></li>
			<li><a href="store.html">Магазин</a></li>
			<li><a href="about.html">Про</a></li>
		</center>
		</ul>
		</center>
		<!-- The Modal Dialog for authorization -->
        <form id="logindialog" class="w3-modal">
            <div class="w3-modal-content w3-animate-top w3-card-8">
                <header class="w3-container w3-green">
						<span onclick="document.getElementById('logindialog').style.display='none'"
                        class="w3-closebtn">&times;</span>
                    <p>Будь ласка, введіть свої облікові дані нижче:</p>
                </header>
				<div class="w3-container">
                    <br><label class="w3-label">ЕЛ. ПОШТА</label>
                    <input class="w3-input" type="email" id="log_email" required>
                     
                    <br><label class="w3-label">ПАРОЛЬ</label>
                    <input class="w3-input" type="password" id="log_pass" required>
                    
                    <p><button id="loginbutton" class="w3-btn w3-theme-l3">УВІЙТИ</button>
                    
                    <p>Ще не зареєстровані?
                    <button id="showregisterdialog" class="w3-btn w3-theme-l3">ЗАРЕЄСТРУВАТИСЯ</button>      
                </div>
            </div>
        </form>
		
		<!-- The Modal Dialog for registration -->
        <form id="registerdialog" class="w3-modal">
            <div class="w3-modal-content w3-animate-top w3-card-8">
                <header class="w3-container w3-green">
						<span onclick="document.getElementById('registerdialog').style.display='none'"
                        class="w3-closebtn">&times;</span>
                    
                    <p>Будь ласка, надайте таку інформацію для реєстрації:</p>
                </header>
					<div class="w3-container">
                    <br><label class="w3-label">ІМ'Я*</label>
                    <input class="w3-input" type="text" id="reg_first" required>
    
                    <label class="w3-label">ПРІЗВИЩЕ*</label>
                    <input class="w3-input" type="text" id="reg_last" required>
                        
                    <label class="w3-label">ЕЛЕКТРОННА ПОШТА*</label>
                    <input class="w3-input" type="email" id="reg_email" required>
                     
                    <label class="w3-label">ПАРОЛЬ*</label>
                    <input class="w3-input" type="password" id="reg_pass" required>
                     
                    <label class="w3-label">ПІДТВЕРДЬТЕ ПАРОЛЬ*</label>
                    <input class="w3-input" type="password" id="reg_verpass" required>
                        
                    <p><button id="register" class="w3-btn w3-theme-l3 w3-btn w3-btn-block w3-section">ЗАРЕЄСТРУВАТИСЯ</button>
                        
                    </table>
                </div>
            </div>
        </form>
			

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