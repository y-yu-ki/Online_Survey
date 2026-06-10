<?php
// セッションが開始されていない場合は開始する
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログインしているか（user_idがあるか）チェック
if (!isset($_SESSION['user_id'])) {
    // 未ログインの場合、現在アクセスしようとしているURLを保存
    // クエリパラメータ（?id=1 など）も含めて保持するために REQUEST_URI を使用
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // ログイン画面へリダイレクト
    header("Location: signin.php");
    exit;
}