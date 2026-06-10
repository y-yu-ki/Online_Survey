<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// DB接続設定（環境に合わせて適宜変更してください）
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');

$error = "";

// --- 1. CSRFトークンの生成（画面表示用） ---
if (!isset($_SESSION['csrf_token'])) {
    // 安全なランダムトークンを生成してセッションに保存
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- 2. ログインボタンが押された時（POST処理） ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CSRFトークン検証
    $input_token = filter_input(INPUT_POST, 'csrf_token', FILTER_DEFAULT);
    if (!$input_token || $input_token !== $_SESSION['csrf_token']) {
        die("不正なリクエストです。");
    }

    // 入力値の取得
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_DEFAULT));
    $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);

    if ($username !== "" && $password !== "") {
        try {
            // DB接続
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            // ユーザー情報を取得するクエリ
            $stmt = $pdo->prepare("SELECT id, uuid, username, password FROM users WHERE username = :username");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            // ユーザーが存在し、かつパスワードのハッシュが一致するか検証
            if ($user && password_verify($password, $user['password'])) {
                
                // 【重要】セッションID再発行（ハイジャック対策）
                session_regenerate_id(true);

                // セッション領域にユーザー情報を保存
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['uuid']     = $user['uuid'];
                $_SESSION['username'] = $user['username'];

                // リダイレクト先の判定
                $redirect_url = "index.php"; // デフォルトの遷移先
                if (isset($_SESSION['redirect_url']) && $_SESSION['redirect_url'] !== "") {
                    $redirect_url = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']); // 使用した後はセッションから削除
                }

                // 対象のURLへリダイレクト
                header("Location: " . $redirect_url);
                exit;
            } else {
                $error = "ユーザー名またはパスワードが間違っています。";
            }

        } catch (PDOException $e) {
            // 本番環境ではエラー内容を直接表示せずログに出力してください
            $error = "データベースエラーが発生しました。";
        }
    } else {
        $error = "ユーザー名とパスワードを入力してください。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>サインイン</title>
    <style>
        .login-box { width: 300px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; }
        .error { color: red; margin-bottom: 15px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; }
        .input-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #007bff; color: #fff; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>サインイン</h2>
    
    <?php if ($error !== ""): ?>
        <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form action="signin.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        
        <div class="input-group">
            <label for="username">ユーザー名</label>
            <input type="text" id="username" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username, ENT_QUOTES, 'UTF-8') : ''; ?>">
        </div>
        
        <div class="input-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">ログイン</button>
    </form>
</div>

</body>
</html>