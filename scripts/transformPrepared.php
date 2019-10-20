<?php

if(isset($_POST['sql'])){
    
    //untaint($_POST['sql']);
    //untaint($_POST['type']);
    //untaint($_POST['queryvar']);
    
    $sql = $_POST['sql'];
    $varType = $_POST['type'];
    
    if($varType == 1){
        $type = '$this->pdo->';
    } else {
        $type = '$pdo->';
    }
    
    $totalVar = substr_count($sql, '$');
    $strlen = strlen($sql);
    
    $tmpSql = $sql;
    $variables = Array();

    $sqlPart = Array();
    $sqlPart['TOTAL'] = 0;
    
    for($i=0;$i<$totalVar;$i++){

        $varLocation = strpos($tmpSql, '$');
        
        $variables[$i+1] = '';
                
        if($tmpSql[$varLocation-1] == '.'){

            //concatenado
            $sqlPart[$sqlPart['TOTAL']+1] = substr($tmpSql, 0, $varLocation-3);
            $sqlPart['TOTAL']++;
            
            for($g=$varLocation+1; strpos($tmpSql[$g], '.') === FALSE; $g++){
                
                $variables[$i+1] .= $tmpSql[$g];
                
            }
            
            $tmpSql = substr($tmpSql, $g+3, -1);
                        
            if($i+1 == $totalVar){
                
                if($sql[$strlen-2] != '"'){
                    $final = $sql[$strlen-2].$sql[$strlen-1];
                } else {
                    $final = '';
                }
                
                $sqlPart[$sqlPart['TOTAL']+1] = $tmpSql.$final;
                $sqlPart['TOTAL']++;
                
            }
            
        } else {
            //free to go

            $sqlPart[$sqlPart['TOTAL']+1] = substr($tmpSql, 0, $varLocation);
            $sqlPart['TOTAL']++;            
                  var_dump($sqlPart);

            $tmpSql[$varLocation] = ':';
            for($g=$varLocation+1; strpos($tmpSql[$g], ' ') === FALSE && $g < strlen($tmpSql) -1; $g++){
                
                $variables[$i+1] .= $tmpSql[$g];
                
            }
            
            $tmpSql = substr($tmpSql, $g, -1);

            if($i+1 == $totalVar){

                $sqlPart[$sqlPart['TOTAL']+1] = $tmpSql.$sql[$strlen-2].$sql[$strlen-1];
                $sqlPart['TOTAL']++;

            }       
        
            
        }
        
    }
    
    $tmp = Array();
    for($i=1;$i<$sqlPart['TOTAL']+1;$i++){
        
        $tmp[$i] = str_replace("'", "\'", $sqlPart[$i]);
        
    }
    
    for($i=1;$i<$sqlPart['TOTAL']+1;$i++){
        
        $sqlPart[$i] = $tmp[$i];
        
    }
    
    if(strlen($_POST['queryvar']) == 0){
        $var = '$stmt';
    } else {
        $var = '$'.$_POST['queryvar'].'';
    }

    $var = '$stmt';
    
    $line2 = $var.' = '.$type.'prepare($sql);';
    $line3 = $var.'->execute(array('; 
    
    switch($_POST['querytype']){
        
        case 1:
            
            $ending = ';';
            
            break;
        case 2:
            
            $ending = '->fetchAll();';
            
            break;
        case 3:
            
            $ending = '->fetch(PDO::FETCH_OBJ);';
            
            break;
        case 4:
            
            $ending = ';';            
            
            break;
        case 5:
            
            $ending = '->fetchAll();'; 
            
            break;
        case 6:
            
            $ending = '->fetch(PDO::FETCH_OBJ);';             
            
            break;
        
        
    }
        
    $sql = '$sql = \'';
    for($i=1;array_key_exists($i, $variables);$i++){
        
        if(!array_key_exists($i+1, $variables)){
            $str = '));';
        } else {
            $str = ', ';
        }
        
        $sql .= $sqlPart[$i].':'.$variables[$i];
        $line3 .= '\':'.$variables[$i].'\' => $'.$variables[$i].$str;
        
    }
    
    if(array_key_exists($i, $sqlPart)){
        $sql .= $sqlPart[$i];
    }
    
    $sql .= '\';';
    
    $tab = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
 
    if($_POST['querytype'] != 1 && $_POST['querytype'] != 4){
        $line4 = $tab;
        if($_POST['querytype'] > 4){
            $before = 'return $stmt';
        } else {
            $before = '$'.$_POST['queryvar'].' = $stmt';
        }
        $line4 .= $before.$ending;
    } else {
        $line4 = '';
    }
    
    echo "<br/>$tab$sql<br/>$tab$line2<br/>$tab$line3<br/>$line4";
    
    if($_POST['type'] == 1){
        $checkType1 = 'CHECKED';
        $checkType2 = '';
    } else {
        $checkType2 = 'CHECKED';
        $checkType1 = '';
    }
    
    switch($_POST['querytype']){
        
        case 1:
            $txt = 'Simple query';
            break;
        case 2:
            $txt = 'fetch all';
            break;
        case 3:
            $txt = 'fetch object';
            break;
        case 4:
            $txt = 'return';
            break;
        case 5:
            $txt = 'return fetch all';
            break;
        case 6:
            $txt = 'return fetch object';
            break;
        
    }

    $option = "<option value=\"$_POST[querytype];\" selec=\"selected\">$txt</option>";
    
    ?>
    
    <br/><br/><br/>

    <form action="" method="POST">

        SQL: <input type="text" name="sql" size="100"<br/><br/>
        Query: 
        <select name="querytype">

            <?php echo $option; ?>
            <option value="1">Simple query</option>
            <option value="2">fetch all</option>
            <option value="3">fetch object</option>
            <option value="4">return</option>
            <option value="5">return fetch all</option>
            <option value="6">return fetch object</option>

        </select>
        Nome $query: $<input type="text" name="queryvar" size="40" value="<?php echo $_POST['queryvar']; ?>">
        <br/>
        Tipo: <br/>
        $this->pdo-><input type="radio" name="type" value="1" <?php echo $checkType1; ?>><br/>
        $pdo-><input type="radio" name="type" value="2" <?php echo $checkType2; ?>>
        <Br/><br/><input type="submit" value="Transform">

    </form>    
    
    <?php
    

} else {
    
    ?>
    
<form action="" method="POST">
    
    SQL: <input type="text" name="sql" size="100"<br/><br/>
    Query: 
    <select name="querytype">
        
        <option value="1">Simple query</option>
        <option value="2">fetch all</option>
        <option value="3">fetch object</option>
        <option value="4">return</option>
        <option value="5">return fetch all</option>
        <option value="6">return fetch object</option>
        
    </select>
    Nome $query: $<input type="text" name="queryvar" size="40">
    <br/>
    Tipo: <br/>
    $this->pdo-><input type="radio" name="type" value="1" CHECKED><br/>
    $pdo-><input type="radio" name="type" value="2">
    <Br/><br/><input type="submit" value="Transform">
    
</form>

    <?php
    
}

?>
