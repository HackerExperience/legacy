<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/PC.class.php';
require '/var/www/classes/Versioning.class.php';

$session = new Session();
$system = new System();

?>

<html>
<?php require 'template/templateTop.php';

if($session->issetLogin()){


    $player = new Player($_SESSION['id']);

    $hardware = new HardwareVPC($_SESSION['id']);

    $gotPage = '0';
    $gotID = '0';
    if($system->issetGet('page')){

        $pageInfo = $system->verifyStringGet('page');

        if($pageInfo['GET_VALUE'] == 'changelog'){

            $gotPage = '1';

        }

        if($system->issetGet('id')){

            $idInfo = $system->verifyNumericGet('id');

            if($idInfo['IS_NUMERIC'] == '1'){

                $gotID = '1';

            }

        }

    }

    $versioning = new Versioning();

    if($gotPage == '1' && $gotID == '0'){

        $versioning->listChanges();

    } elseif($gotPage == '1' && $gotID == '1'){

        $versioning->showChange($idInfo['GET_VALUE']);

    }else {

    ?>

        Current version: <?php echo $version.$versionStatus; ?>
        <br/><br/>
        <a href="about.php?page=changelog">View changelog</a>


    <?php

    }

    ?>

</table>
</body>
        </html> 

                <?php

} else {

    header("Location:index.php");

}
?>