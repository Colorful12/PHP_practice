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
    function readfiles($pdo, $needbr){
        preparetable();
        $stmt = $pdo -> query("SELECT * FROM posts");
        $lines = $stmt -> fetchAll();
        if($needbr) echo "<br><br><br>";
        return $lines;
    }
    
    // 編集対象のデータ一式の取得
    function geteditline($editnum){
        global $filename, $pdo;
        $stmt = $pdo -> prepare("SELECT * FROM posts WHERE num=:num");
        $stmt -> bindParam(":num", $editnum);
        $stmt -> execute();
        $editline = $stmt -> fetchAll();
        return $editline[0];
    }
    
    // 新規投稿の追加
    function addnewtext($name, $text, $time, $pass){
        global $filename, $pdo;
        preparetable();
        $sql = "SELECT * FROM posts";
        $lines = $pdo -> query($sql);
        $postlist = $lines -> fetchAll(PDO::FETCH_ASSOC);
        
        if(empty($postlist)) $nownum=1;
        else{
            
            $lastpost = $postlist[count($postlist)-1];
            $nownum = $lastpost["num"] + 1;
        }
        post_to_table($nownum, $name, $text, $time, $pass);
        echo "投稿しました.<br><br><br>";
    }
    
    // コメントの挿入
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
            
            $sql = "SELECT * FROM posts";
            $lines = $pdo -> query($sql);
            foreach($lines as $line) echo $line["num"]."<>".$line["name"]."<>".$line["comment"]."<>".$line["date"]."<br>";
            
        }elseif(isset($_POST["dnum"])){
            $delnum = $_POST["dnum"];
            $dpass = $_POST["dpass"];
            
            $lines = readfiles($pdo, 0);
            if(empty($lines)) echo "掲示板は既に空です. <br>";
            elseif(count($lines)<$delnum || $delnum<=0) {
                echo "その番号は存在しません.";
                $lines = readfiles($pdo, 1);
                foreach($lines as $line) echo $line["num"]."<>".$line["name"]."<>".$line["comment"]."<>".$line["date"]."<br>";
            }elseif(empty($dpass)){
                echo "パスワードを入力してください.";
                $lines = readfiles($pdo, 1);
                foreach($lines as $line) $line["num"]."<>".$line["name"]."<>".$line["comment"]."<>".$line["date"]."<br>";
            }else{
                $stmt = $pdo->query("DROP TABLE posts");
                preparetable();
                $i = 0;
                $deldone = false;
                foreach($lines as $line){
                    $linenum = $line["num"];
                    if($linenum!=$delnum){
                        if($deldone){
                            insert($i, $line["name"], $line["comment"], $line["date"], $line["password"]);
                        }else{
                            insert($line["num"], $line["name"], $line["comment"], $line["date"], $line["password"]);
                        }
                        $i++;
                    }else {
                        $truepass = $line["password"];
                        if($truepass!=$dpass){
                            echo "パスワードが違います. やりなおしてください.>";
                            insert($line["num"], $line["name"], $line["comment"], $line["date"], $line["password"]);
                        }else $deldone = true;
                        $i++;
                    }
                }
                $lines = readfiles($pdo, 1);
                foreach($lines as $line) echo $line["num"]."<>".$line["name"]."<>".$line["comment"]."<>".$line["date"]."<br>";
                
            }
                
        }elseif(isset($_POST["enum"])){
            $editnum=$_POST["enum"];
            preparetable();
            $stmt = $pdo -> query("SELECT * FROM posts");
            $lines = $stmt -> fetchAll();
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
             
                $checkform = 1;
                $editline = geteditline($_POST["enum"]);
                $truepass = $editline["password"];
                if(empty($epass)) $checkform = 0;
                else if($truepass!=$epass) $checkform = 0;
                else{
                    if(empty($editline)) $checkform = 0;
                    else{
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