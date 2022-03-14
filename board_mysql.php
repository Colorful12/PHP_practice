<!DOCTYPE html>
<head>
    <meta chartype="utf-8"/>
    <title>mission5-01</title>
    <style>
        body{
            margin:10% 20%;
        }
    </style>
</head>
<body>
    <?php 
    $filename = "mission5-01.txt";
    
    $pdo = new PDO("mysql:dbname=tb231074db; host=localhost", "tb-231074", "rUrp8BEX63", array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    $sql ="CREATE TABLE IF NOT EXISTS posts(
    num INT UNSIGNED PRIMARY KEY,
    name VARCHAR(32) NOT NULL,
    comment TEXT NOT NULL,
    date TEXT NOT NULL,
    password TEXT
    );";

    $pdo -> query($sql);
    
    #ok
    function post_to_table($num, $name, $text, $time, $pass){
        global $pdo;
        $save = $pdo -> prepare("INSERT INTO posts (num, name, comment, date, password) VALUES (:num, :name, :comment, :date, :password)");
        $save -> bindParam(":num", $num);
        $save -> bindParam(":name", $name);
        $save -> bindParam(":comment", $text);
        $save -> bindParam(":date", $time);
        $save -> bindParam(":password", $pass);
        $save -> execute();
    }
       
    #ok 
    function preparetable(){
        global $pdo;
        $sql ="CREATE TABLE IF NOT EXISTS posts(
        num INT UNSIGNED PRIMARY KEY,
        name VARCHAR(32) NOT NULL,
        comment TEXT NOT NULL,
        date TEXT NOT NULL,
        password TEXT
        );";
        $pdo -> query($sql);
    }
    
    //ファイルを読み込む関数
    function readfiles_before($filename, $needbr){
        global $pdo;
        
        $fp = fopen($filename, "a");
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        if($needbr) echo "<br><br><br>";
        return $lines;
    }
    
    function readfiles($pdo, $needbr){
        $stmt = $pdo -> query("SELECT * FROM posts");
        $lines = $stmt -> fetchAll();
        if($needbr) echo "<br><br><br>";
        return $lines;
    }
    
    // 編集対象のデータ一式の取得
    //多分ok...?
    function geteditline($editnum){
        global $filename, $pdo;
        $stmt = $pdo -> prepare("SELECT * FROM posts WHERE num=:num");
        $stmt -> bindParam(":num", $editnum);
        $stmt -> execute();
        $editline = $stmt -> fetchAll();
        //$lines = file($filename, FILE_IGNORE_NEW_LINES);
        //if(empty($lines)|| count($lines)<$editnum || $editnum<=0) $editline = "";
        //else{        
         //   foreach($lines as $line){
          //      $linenum = explode("<>", $line)[0];
        //        if($linenum==$editnum){
         //           $editline = $line;
          //      }
        //    }
        //}
        return $editline[0];
    }
    
    // 新規投稿の追加
    // ok
    function addnewtext($name, $text, $time, $pass){
        global $filename, $pdo;
        //$fp = fopen($filename, "a");
        //$lines = file($filename, FILE_IGNORE_NEW_LINES);
        // DONE2
        preparetable();
        $sql = "SELECT * FROM posts";
        $lines = $pdo -> query($sql);
        $postlist = $lines -> fetchAll(PDO::FETCH_ASSOC);
        
        if(empty($postlist)) $nownum=1;
        else{
            
            $lastpost = $postlist[count($postlist)-1];
            //$nownum = explode("<>", $lastpost)[0] + 1;
            $nownum = $lastpost["num"] + 1;
        }
        //$post = $nownum."<>".$name."<>".$text."<>".$time."<>".$pass."<>".PHP_EOL;
        //$check = fwrite($fp, $post);
        post_to_table($nownum, $name, $text, $time, $pass);
        //$save -> execute();
        echo "投稿しました.<br><br><br>";
        //fclose($fp);
        
    }
    
    // 投稿の編集
    function edittext_before($name, $text, $time, $editnum, $pass){
        global $filename, $save;
        $newpost = array();
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        foreach($lines as $line){
            $nowline = $line;
            $explode = explode("<>", $line);
            $linenum = $explode[0];
            if($linenum==$editnum){
                $newpost[$linenum] = $linenum."<>".$name."<>".$text."<>".$time."<>".$pass."<>".PHP_EOL;
            }else {
                $newpost[$linenum]=$nowline.PHP_EOL;
        }
        }
        echo "<br><br><br>";
        $fp = fopen($filename, "w");
        foreach($newpost as $line){
            $check = fwrite($fp, $line);
            if(!$check) echo "書き込み失敗.<br>";
        }
    }
    function insert($num, $name, $text, $time, $pass){
        global $pdo;
        
        $sql = $pdo -> prepare("INSERT INTO posts (num, name, comment, date, password) VALUES (:num, :name, :comment, :date, :password)");
        $sql -> bindParam(":num", $num);
        $sql -> bindParam(":name", $name);
        $sql -> bindParam(":comment", $text);
        $sql -> bindParam(":date", $time);
        $sql -> bindParam(":password", $pass);
        $sql -> execute();
    }
    
    function edittext($name, $text, $time, $editnum, $pass){
        global $pdo;
        $stmt = $pdo -> query("SELECT * FROM posts");
        $lines = $stmt -> fetchAll();
        $stmt = $pdo->query("DROP TABLE posts");
        preparetable();
        
        foreach($lines as $line){
            $linenum = $line["num"];
            if($linenum==$editnum) insert($linenum, $name, $text, $time, $pass);
            else insert($line["num"], $line["name"], $line["comment"], $line["date"], $line["password"]);
        }
        echo "<br><br><br>";
    }
    
    function treatform(){
        global $filename, $pdo;
        $nownum=0;
        
        if(isset($_POST["text"])){
            $text = $_POST["text"];
            $name = $_POST["name"];
            $pass = $_POST["pass"];
            
            // ifeditのセット
            if(isset($_POST["ifedit"])){
                $ifedit = $_POST["ifedit"];
            }else $ifedit = "";
            
            if(empty($text)&&empty($pass)) echo "テキストとパスワードを入力してください"."<br>"."<br>"."<br>";
            else if(empty($text)) echo "テキストを入力してください"."<br>"."<br>"."<br>";
            else if(empty($pass)) echo "パスワードを入力してください"."<br>"."<br>"."<br>";
            else{
                $time = date("Y/m/d H:i:s");
                
                // 追加か編集の分岐
                if(empty($ifedit)) addnewtext($name, $text, $time, $pass);
                else edittext($name, $text, $time, $ifedit, $pass);
            }
            
            // DONE 1
            $sql = "SELECT * FROM posts";
            $lines = $pdo -> query($sql);
            //$lines = file($filename, FILE_IGNORE_NEW_LINES);
            foreach($lines as $line) echo $line["num"]."<>".$line["name"]."<>".$line["comment"]."<>".$line["date"]."<br>";
            
        }elseif(isset($_POST["dnum"])){
            $delnum = $_POST["dnum"];
            $dpass = $_POST["dpass"];
            
            $lines = readfiles($filename, 0);
            if(empty($lines)) echo "掲示板は既に空です. <br>";
            elseif(count($lines)<$delnum || $delnum<=0) {
                echo "その番号は存在しません.";
                $lines = readfiles($filename, 1);
                foreach($lines as $line) echo $line."<br>";
            }elseif(empty($dpass)){
                echo "パスワードを入力してください.";
                $lines = readfiles($filename, 1);
                foreach($lines as $line) echo $line."<br>";
            }else{
                $newpost = array();
                $i = 0;
                $deldone = false;
                foreach($lines as $line){
                    $explode = explode("<>", $line);
                    $linenum = $explode[0];
                    if($linenum!=$delnum){
                        if($deldone){
                            $line = explode("<>", $line);
                            $line[0] = $i;
                            $newpost[$i] = $line[0]."<>".$line[1]."<>".$line[2]."<>".$line[3]."<>".$line[4]."<>".PHP_EOL;
                        }else{
                            $newpost[$i] = $line.PHP_EOL;
                        }
                        $i++;
                    }else {
                        $truepass = $explode[count($explode)-2];
                        if($truepass!=$dpass){
                            echo "パスワードが違います. やりなおしてください.>";
                            $newpost[$i] = $line.PHP_EOL;
                        }else $deldone = true;
                        $i++;
                    }
                }
                //ファイルに書き込む
                $fp = fopen($filename, "w");
                foreach($newpost as $line){
                    $check = fwrite($fp, $line);
                    if(!$check) echo "書き込み失敗.<br>";
                }
                $lines = readfiles($filename, 1);
                foreach($lines as $line) echo $line."<br>";
                
            }
                
        }elseif(isset($_POST["enum"])){
            $editnum=$_POST["enum"];
            preparetable();
            //$fp = fopen($filename,"a");
            $stmt = $pdo -> query("SELECT * FROM posts");
            $lines = $stmt -> fetchAll();
            //$lines = file($filename, FILE_IGNORE_NEW_LINES);
            //fclose($fp);
            if(empty($editnum)||empty($lines)|| count($lines)<$editnum || $editnum<=0) echo "その番号は存在しません.";
            else{
               //パスワードが違ったらそれを画面にだすとかの処理
            }
            $lines = readfiles($pdo, 1);
            foreach($lines as $line) echo $line["num"]."<>".$line["name"]."<>".$line["comment"]."<>".$line["date"]."<br>";
            }
        else{
            $lines = readfiles($pdo, 1);
            foreach($lines as $line) echo $line["num"]."<>".$line["name"]."<>".$line["comment"]."<>".$line["date"]."<br>";
        }
    }
        
        if(isset($_POST["enum"])){
            //入力されたパスワード
            $epass = $_POST["epass"];
            $checkform = 0;
            if(!empty($epass)&&!empty($_POST["enum"])){
                preparetable();
                $lines = readfiles($pdo, 0);
                //fclose($fp);
                
                $checkform = 1;
                $editline = geteditline($_POST["enum"]);
                //$explode = explode("<>", $editline);
                //$truepass = $explode[count($explode)-2];
                $truepass = $editline["password"];
                if(empty($epass)) $checkform = 0;
                else if($truepass!=$epass) $checkform = 0;
                else{
                    if(empty($editline)) $checkform = 0;
                    else{
                        //$editnum =explode("<>", $editline)[0];
                        //$ename = explode("<>", $editline)[1];
                        //$etext = explode("<>", $editline)[2];
                        $editnum =$editline["num"];
                        $ename = $editline["name"];
                        $etext = $editline["comment"];                    
                    }
                }
            }    
        }
        
    ?>
            
    ↓以下から投稿 または 番号を指定して編集↓
    <form action="" method="post">
        <input type="text" name="name" placeholder="ユーザー名" value=<?php if(isset($_POST["enum"])&&$checkform)echo $ename; else echo "匿名さん"; ?>>
        <input type="text" name="text" placeholder="コメント" value=<?php if(isset($_POST["enum"])&&$checkform)echo $etext;?>>
        <input type="text" name="pass" placeholder="パスワード" value=<?php if(isset($_POST["enum"])&&$checkform)echo $epass;?>>
        <input type="hidden" name="ifedit" value=<?php if(isset($_POST["enum"])&&$checkform) echo $editnum; ?>>
        <input type="submit">
    </form>
    <form action="" method="post" style="margin-top:10px;">
        <input type="number" name = "enum" style="width:30px;">
        <input type="text" name="epass" placeholder="パスワード">
        <input type="submit" value="編集">
    </form>
    
    <form style="margin-top:30px;" action="" method="post">
         ↓投稿の削除はこちら↓<br>
        <input type="number" name="dnum" style="width:30px;">
        <input type="text" name="dpass" placeholder="パスワード">
        <input type="submit" value="削除">
    </form>
    
    <?php 
    treatform();
    ?>
    
</body>