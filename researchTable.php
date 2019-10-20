<?php

// 2019: This was a tool to list software version/price/research time and iron out the overall game design

$multiplier = Array(
    
    '0' => array( //cracker
        
        'price' => 25,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '1' => array( //enc
        
        'price' => 23,
        'hd' => 15,
        'ram' => 15
        
    ),    
    
    '2' => array( //ssh exploit
        
        'price' => 19,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '3' => array( //ftp exploit
        
        'price' => 19,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '4' => array( //firewall
        
        'price' => 21,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '5' => array( //hider
        
        'price' => 15,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '6' => array( //seek
        
        'price' => 15,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '7' => array( //antivirus
        
        'price' => 16,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '8' => array( //vspam
        
        'price' => 12,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '9' => array( //vwarez
        
        'price' => 12,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '10' => array( //vddos
        
        'price' => 13,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '11' => array( //vbrk
        
        'price' => 14,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '12' => array( //vcol
        
        'price' => 14,
        'hd' => 15,
        'ram' => 15
        
    ),      
    
);



?>
<strong><center>Price</center></strong>
<table border="1">

    <th></th>
    <th> Cracker </th>
    <th> Enc </th>
    <th> SSH Exploit </th>
    <th> FTP Exploit </th>
    <th> Firewall </th>
    <th> Hidder </th>
    <th> Seek </th>
    <th> Antivirus </th>
    <th> Vspam </th>
    <th> Vwarez </th>
    <th> Vddos </th>
    <th> Vbrk </th>
    <th> Vcol </th>    
    
    <?php
    
    for($i=10;$i<201;$i++){
        

        
        ?>
    
    
        <tr>
            <td><b><?php echo $i; ?></b></td>
            
            <?php
            
            for($g=0;$g<13;$g++){
                
                if($i >= 100){
                    $valor = ceil($i*($multiplier[$g]['price']*(($i-90)/100)-10)+3000-(25-$multiplier[$g]['price'])*80);
                } else {
                    $price = (pow($i, 2)-9*$i)/100;

                    $valor = ceil($price*$multiplier[$g]['price']);
                }                
                
                ?>
            
                <td>$<?php echo number_format($valor); ?></td>
            
                <?php
                
            }
            
            ?>
            
            
        </tr>    
    
        <?php
        
    }
    
    ?>

    
</table>

<?php

$multiplier = Array(
    
    '0' => array( //cracker
        
        'price' => 25,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '1' => array( //enc
        
        'price' => 23,
        'hd' => 15,
        'ram' => 15
        
    ),    
    
    '2' => array( //ssh exploit
        
        'price' => 19,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '3' => array( //ftp exploit
        
        'price' => 19,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '4' => array( //firewall
        
        'price' => 21,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '5' => array( //hider
        
        'price' => 15,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '6' => array( //seek
        
        'price' => 15,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '7' => array( //antivirus
        
        'price' => 16,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '8' => array( //vspam
        
        'price' => 12,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '9' => array( //vwarez
        
        'price' => 12,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '10' => array( //vddos
        
        'price' => 13,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '11' => array( //vbrk
        
        'price' => 14,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '12' => array( //vcol
        
        'price' => 14,
        'hd' => 15,
        'ram' => 15
        
    ),      
    
);



?>
<strong><center>Software Size</center></strong>
<table border="1">

    <th></th>
    <th> Cracker </th>
    <th> Enc </th>
    <th> SSH Exploit </th>
    <th> FTP Exploit </th>
    <th> Firewall </th>
    <th> Hidder </th>
    <th> Seek </th>
    <th> Antivirus </th>
    <th> Vspam </th>
    <th> Vwarez </th>
    <th> Vddos </th>
    <th> Vbrk </th>
    <th> Vcol </th>    
    
    <?php
    
    $values = Array();
    
    for($i=10;$i<201;$i++){
        

        
        ?>
    
    
<!--        <tr>
            <td><b><?php echo $i; ?></b></td>
            -->
            <?php
            
            for($g=0;$g<13;$g++){
                
                if($i >= 100){
                    $valor = ceil($i*($multiplier[$g]['price']*(($i-100)/100)-9.5)+3000-(25-$multiplier[$g]['price'])*80);
                } else {
                    $price = (pow($i, 2)-9*$i)/110+1;
                    
                    $valor = ceil($price*$multiplier[$g]['price']);
                }                
                
                $values[$g][$i] = $valor;
                
                ?>
            
<!--                <td><?php //echo $valor; ?> MB</td>-->
            
                <?php
                
            }
            
            ?>
            
            
<!--        </tr>    -->
    
        <?php
        
    }
    
    echo 'dict_size = {';
    
    foreach($values as $type => $v){

        if($type == 0){
            $ext = '1';
        } elseif($type == 1){
            $ext = '2';
        } elseif($type == 2){
            $ext = '13';
        } elseif($type == 3){
            $ext = '14';
        } else {
            $ext = $type;
        }
        
        
        
            echo '\''.$ext.'\':{';
        
        foreach($v as $a => $b){
            echo '\''.$a.'\':\''.$b.'\'';
            if($a != 200){
                echo ',';
            }
        }
        
        echo '},';
        
        
        
    }
    
    echo '}'."<br/>";
    
    ?>

    
</table>

<?php

$multiplier = Array(
    
    '0' => array( //cracker
        
        'price' => 25,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '1' => array( //enc
        
        'price' => 23,
        'hd' => 15,
        'ram' => 15
        
    ),    
    
    '2' => array( //ssh exploit
        
        'price' => 19,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '3' => array( //ftp exploit
        
        'price' => 19,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '4' => array( //firewall
        
        'price' => 21,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '5' => array( //hider
        
        'price' => 15,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '6' => array( //seek
        
        'price' => 15,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '7' => array( //antivirus
        
        'price' => 16,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '8' => array( //vspam
        
        'price' => 12,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '9' => array( //vwarez
        
        'price' => 12,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '10' => array( //vddos
        
        'price' => 13,
        'hd' => 15,
        'ram' => 15
        
    ),  
    
    '11' => array( //vbrk
        
        'price' => 14,
        'hd' => 15,
        'ram' => 15
        
    ),
    
    '12' => array( //vcol
        
        'price' => 14,
        'hd' => 15,
        'ram' => 15
        
    ),      
    
);



?>

<strong><center>RAM</center></strong>

<table border="1">

    <th></th>
    <th> Cracker </th>
    <th> Enc </th>
    <th> SSH Exploit </th>
    <th> FTP Exploit </th>
    <th> Firewall </th>
    <th> Hidder </th>
    <th> Seek </th>
    <th> Antivirus </th>
    <th> Vspam </th>
    <th> Vwarez </th>
    <th> Vddos </th>
    <th> Vbrk </th>
    <th> Vcol </th>    
    
    <?php
    
    for($i=10;$i<201;$i++){
        

        
        ?>
        
<!--        <tr>
            <td><b><?php //echo $i; ?></b></td>
            -->
            <?php
            
            for($g=0;$g<13;$g++){
                
                if($i >= 100){
                    if($i >= 150){
                        $valor = ceil($i*($multiplier[$g]['price']*(($i-110)/100)-15)+3000-(25-$multiplier[$g]['price'])*80);
                    } else {
                        $valor = ceil($i*($multiplier[$g]['price']*(($i-110)/100)-15)+3000-(25-$multiplier[$g]['price'])*80);
                    }
                } else {
                    if($i >= 50){
                        $price = (pow($i*0.9, 2)-9*$i)/145+0.3;
                    } else {
                        $price = (pow($i*0.9, 2)-9*$i)/140+0.4;
                    }
                    
                    $valor = ceil($price*$multiplier[$g]['price']);
                }                
                
                $values[$g][$i] = $valor;
                
                ?>
            
<!--                <td><?php //echo $valor; ?> MB</td>-->
            
                <?php
                
            }
            
            ?>
            
            
<!--        </tr>    -->
    
        <?php
        
    }
    
    echo 'dict_ram = {';
    
    foreach($values as $type => $v){

        if($type == 0){
            $ext = '1';
        } elseif($type == 1){
            $ext = '2';
        } elseif($type == 2){
            $ext = '13';
        } elseif($type == 3){
            $ext = '14';
        } else {
            $ext = $type;
        }
        
        
        
            echo '\''.$ext.'\':{';
        
        foreach($v as $a => $b){
            echo '\''.$a.'\':\''.$b.'\'';
            if($a != 200){
                echo ',';
            }
        }
        
        echo '},';
        
        
        
    }
    
    echo '}'."<br/>";
    
    ?>

    
</table>
