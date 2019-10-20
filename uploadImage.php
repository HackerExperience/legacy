<?php

// 2019: This contains a bug that allows RCE via EXIF headers. 
// 2019: Consider rolling your own, secure image upload solution
exit();

if(!isset($_SESSION)){
    session_start();
}

if(!isset($_SESSION['id'])){
    header("Location:index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $allowedMimes = array('image/jpg', 'image/jpeg', 'image/png', 'image/gif');

    if(in_array($_FILES['image_upload']['type'], $allowedMimes)){

        if(getimagesize($_FILES['image_upload']['tmp_name']) !== FALSE){

            require_once '/var/www/classes/PDO.class.php';
            $pdo = PDO_DB::factory();

            if(!isset($_POST['t'])){
                exit();
            }
            
            $t = $_POST['t'];
            if($t == 1){
                $type = 'profile';
            } elseif($t == 2){
                $type = 'clan';
            } else {
                exit();
            }
            
            if($type == 'profile'){
                
                $imgType = 'user';
                $dir = 'images/profile/';
                $dir2 = 'images/profile/thumbnail/';
                $redirect = 'profile.php';
                $dir3 = 'images/profile/x60/';

                $sql = "SELECT login FROM users WHERE id = '".$_SESSION['id']."' LIMIT 1";
                $data = $pdo->query($sql)->fetchAll();

                if(count($data) == 1){

                    $filename = md5($data['0']['login'].$_SESSION['id']);

                } else {
                    die("Bad, bad error. LOL");
                }

            } elseif($type == 'clan'){
                
                $imgType = 'admin';
                $dir = 'images/clan/';
                $dir2 = '';
                $redirect = 'clan.php';

                $sql = "SELECT clanID FROM clan_users WHERE userID = '".$_SESSION['id']."' LIMIT 1";
                $data = $pdo->query($sql)->fetchAll();

                if(count($data) == 1){

                    $cid = $data['0']['clanid'];
                    
                    $sql = "SELECT name FROM clan WHERE clanID = '".$cid."'";
                    $data = $pdo->query($sql)->fetchAll();

                    $filename = md5($data['0']['name'].$cid);

                } else {
                    die("You are not a clan member");
                }
                
            }
//
//            require '/var/www/classes/Images.class.php';
//            $images = new Images();
//
//            $images->load($_FILES['image_upload']['tmp_name']);
//            $images->save('/var/www/'.$dir.$filename.'.jpg');
//
//            if($dir2 != ''){
//
//                $images->load($_FILES['image_upload']['tmp_name']);
//                $images->resize(38, 38);
//                $images->save('/var/www/'.$dir2.$filename.'.jpg');
//
//                $images->load($_FILES['image_upload']['tmp_name']);
//                $images->resize(60, 60);
//                $images->save('/var/www/'.$dir3.$filename.'.jpg');
//
//            }
////            
            $tmpFile = $_FILES['image_upload']['tmp_name'];
            

            function autoRotateImage($image) {
                $orientation = $image->getImageOrientation();

                if($orientation == imagick::ORIENTATION_UNDEFINED){
                    return;
                }

                switch($orientation) {
                    case imagick::ORIENTATION_BOTTOMRIGHT:
                        $image->rotateimage("#000", 180); // rotate 180 degrees
                    break;

                    case imagick::ORIENTATION_RIGHTTOP:
                        $image->rotateimage("#000", 90); // rotate 90 degrees CW
                    break;

                    case imagick::ORIENTATION_LEFTBOTTOM:
                        $image->rotateimage("#000", -90); // rotate 90 degrees CCW
                    break;
                }

                // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
                $image->setImageOrientation($orientation);
                
            } 
            
            $imagick = new Imagick($tmpFile);
            autoRotateImage($imagick);
            $imagick->writeImage('/var/www/'.$dir.$filename.'.jpg'); 

            if($dir2 != ''){
                
                $imagick = new Imagick('/var/www/'.$dir.$filename.'.jpg');
                $imagick->cropthumbnailimage(60, 60);
                $imagick->writeImage('/var/www/'.$dir2.$filename.'.jpg');

            }
                        
            header("Location:$redirect");

        } else {
            die("Cant upload this image");
        }

    } else {
        echo 'Please dont';
    }
    
} else {
    echo 'Not a post req';
}


?>
