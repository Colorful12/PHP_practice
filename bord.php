<!DOCTYPE html>
<head>
    <meta chartype="utf-8"/>
    <title>mission3-03</title>
    <style>
        body{
            margin:10% 20%;
        }
    </style>
</head>
<body>
    <?php 
    $filename = "mission3-04.txt";
    
    function geteditline($editnum){
        global $filename;
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        if(empty($lines)|| count($lines)<$editnum || $editnum<=0) $editline = "";
        else{        
            foreach($lines as $line){
                $linenum = explode("<>", $line)[0];
                if($linenum==$editnum){
                    $editline = $line;
                }
            }
        }
        return $editline;
        
        
    }
    function addnewtext($name, $text, $time){
        global $filename;
        $fp = fopen($filename, "a");
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        if(empty($lines)) $nownum=1;
        else{
            $lastpost = $lines[count($lines)-1];
            $nownum = explode("<>", $lastpost)[0] + 1;
        }
        $post = $nownum."<>".$name."<>".$text."<>".$time.PHP_EOL;
        $check = fwrite($fp, $post);
        if($check) echo "投稿しました.<br><br><br>";
        fclose($fp);
        
    }
    
    function edittext($name, $text, $time, $editnum){
        global $filename;
        $newpost = array();
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        foreach($lines as $line){
            $nowline = $line;
            $linenum = explode("<>", $line)[0];
            if($linenum==$editnum){
                $newpost[$linenum] = $linenum."<>".$name."<>".$text."<>".$time.PHP_EOL;
            }else {
                $newpost[$linenum]=$nowline.PHP_EOL;
        }
        }
        $fp = fopen($filename, "w");
        foreach($newpost as $line){
            $check = fwrite($fp, $line);
            if(!$check) echo "書き込み失敗.<br>";
        }
        
        
    }
    
    function treatform(){
        global $filename;
        $nownum=0;
        
        if(isset($_POST["text"])){
            $text = $_POST["text"];
            $name = $_POST["name"];
            if(isset($_POST["ifedit"])){
                $ifedit = $_POST["ifedit"];
            }else $ifedit = "";
            
            if(!empty($text)){
                $time = date("Y/m/d H:i:s");
                
                // 追加か編集の分岐
                if(empty($ifedit)) addnewtext($name, $text, $time);
                else edittext($name, $text, $time, $ifedit);
            }else echo "テキストを入力してください"."<br>"."<br>"."<br>";
            $lines = file($filename, FILE_IGNORE_NEW_LINES);
            foreach($lines as $line) echo $line."<br>";
        }elseif(isset($_POST["dnum"])){
            $delnum = $_POST["dnum"];
            
            $fp = fopen($filename,"a");
            $lines = file($filename, FILE_IGNORE_NEW_LINES);
            fclose($fp);
            if(empty($lines)) echo "掲示板は既に空です. <br>";
            elseif(count($lines)<$delnum || $delnum<=0) {
                echo "その番号は存在しません.";
                $fp = fopen($filename, "a");
                $lines = file($filename, FILE_IGNORE_NEW_LINES);
                echo "<br><br><br>";
                foreach($lines as $line) echo $line."<br>";
            }else{$newpost = array();
                $i = 0;
                $deldone = false;
                foreach($lines as $line){
                    $linenum = explode("<>", $line)[0];
                    if($linenum!=$delnum){
                        if($deldone){
                            $line = explode("<>", $line);
                            $line[0] = $i;
                            $newpost[$i] = $line[0]."<>".$line[1]."<>".$line[2]."<>".$line[3].PHP_EOL;
                        }else{
                            $newpost[$i] = $line.PHP_EOL;
                        }
                        $i++;
                    }else {
                        $deldone = true;
                        $i++;
                    }
                }
                //ファイルに書き込む
                $fp = fopen($filename, "w");
                foreach($newpost as $line){
                    $check = fwrite($fp, $line);
                    if(!$check) echo "書き込み失敗.<br>";
                }
                $fp = fopen($filename, "a");
                $lines = file($filename, FILE_IGNORE_NEW_LINES);
                echo "<br><br><br>";
                foreach($lines as $line) echo $line."<br>";
                
            }
                
            }elseif(isset($_POST["enum"])){
                $editnum=$_POST["enum"];
                $fp = fopen($filename,"a");
                $lines = file($filename, FILE_IGNORE_NEW_LINES);
                fclose($fp);
                if(empty($lines)|| count($lines)<$editnum || $editnum<=0) echo "その番号は存在しません.";
                else{
                    $editline = geteditline($editnum);
                    $ename = explode("<>", $editline)[1];
                    $etext = explode("<>", $editline)[2];
                }
                 $fp = fopen($filename, "a");
            $lines = file($filename, FILE_IGNORE_NEW_LINES);
            echo "<br><br><br>";
            foreach($lines as $line) echo $line."<br>";
                
            }
            else{
            $fp = fopen($filename, "a");
            $lines = file($filename, FILE_IGNORE_NEW_LINES);
            echo "<br><br><br>";
            foreach($lines as $line) echo $line."<br>";
        }
        }
        
        if(isset($_POST["enum"])){
            $fp = fopen($filename,"a");
            $lines = file($filename, FILE_IGNORE_NEW_LINES);
            $checkform = 1;
            fclose($fp);
         
            $editline = geteditline($_POST["enum"]);
            if(empty($editline)) $checkform = 0;
            else{
                $editnum =explode("<>", $editline)[0];
                $ename = explode("<>", $editline)[1];
                $etext = explode("<>", $editline)[2];                    
            }
            
        }
    ?>
            
    ↓以下から投稿 または 番号を指定して編集↓
    <form action="" method="post">
        <input type="text" name="name" placeholder="ユーザー名" value=<?php if(isset($_POST["enum"])&&$checkform)echo $ename; else echo "匿名さん"; ?>>
        <input type="text" name="text" placeholder="コメント" value=<?php if(isset($_POST["enum"])&&$checkform)echo $etext;?>>
        <input type="hidden" name="ifedit" value=<?php if(isset($_POST["enum"])&&$checkform) echo $editnum; ?>>
        <input type="submit">
    </form>
    <form action="" method="post" style="margin-top:10px;">
        <input type="number" name = "enum" style="width:30px;">
        <input type="submit" value="編集">
    </form>
    
    <form style="margin-top:30px;" action="" method="post">
         ↓投稿の削除はこちら↓<br>
        <input type="number" name="dnum" style="width:30px;">
        <input type="submit" value="削除">
    </form>
    
    <?php 
    treatform();
    ?>
    
</body>