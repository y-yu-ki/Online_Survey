<?php
require_once "db.php";
require_once "auth.php";
function unsubscription(){
    delete_user($_SESSION["user_id"]);
    del_sess();
    header("Location: ./php/index.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["back"])){
        header("Location:./php/index.php");
    }elseif(isset($_POST["unsubscripte"])){
        unsubscription();
    }
}
?>

<p>本当に退会しますか？</p>
<form method="post" action="">
    <button type="submit" name="back">戻る</button>
    <button type="submit" name="unsubscripte">退会</button>
</form>