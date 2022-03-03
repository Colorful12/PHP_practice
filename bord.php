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
    ↓以下から投稿↓
    <form action="" method="post">
        <input type="text" name="name" value="匿名さん" placeholder="ユーザー名">
        <input type="text" name="text" placeholder="コメント">
        <input type="submit">
    </form>
    
    <form style="margin-top:30px;" action="" method="post">
         ↓投稿の削除はこちら↓<br>
        <input type="number" name="num" style="width:30px;">
        <input type="submit" value="削除する">
    </form>
    
    <?php 
    $filename = "mission3-03.txt";
    $nownum=0;
    
    
    if(isset($_POST["text"])){
        $text = $_POST["text"];
        $name = $_POST["name"];
        if(!empty($text)){
            $time = date("Y/m/d H:i:s");
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
            
            $lines = file($filename, FILE_IGNORE_NEW_LINES);
            foreach($lines as $line) echo $line."<br>";
        }
    }elseif(isset($_POST["num"])){
        $delnum = $_POST["num"];
        
        $fp = fopen($filename,"a");
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        fclose($fp);
        if(empty($lines)) echo "掲示板は既に空です. <br>";
        elseif(count($lines)<$delnum || count($lines)<0) echo "その番号は存在しません.";
        else{
            $newpost = array();
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
            
        }else{
        $fp = fopen($filename, "a");
        $lines = file($filename, FILE_IGNORE_NEW_LINES);
        echo "<br><br><br>";
        foreach($lines as $line) echo $line."<br>";
    }
    
    ?>
    
</body>
