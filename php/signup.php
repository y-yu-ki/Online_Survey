<?php
require_once 'security.php';

// CSRFトークンの取得
$csrf_token = generate_csrf_token();

// エラーメッセージの取得（遷移元から戻ってきた場合など）
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // 一度表示したら消去
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規会員登録 - 入力</title>
</head>
<body>
    <h2>新規会員登録</h2>
    
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form action="confirm_signup.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

        <div>
            <label for="username">ユーザー名：</label>
            <input type="text" id="username" name="username" required>
        </div>
        <br>
        <div>
            <label for="password">パスワード：</label>
            <input type="password" id="password" name="password" required>
        </div>
        <br>
        <div>
            <label for="password_confirm">パスワード（確認）：</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <br>
        <div>
            <label>
                <input type="checkbox" name="agree" value="1" required> 利用規約に同意する
            </label>
        </div>
        <br>
        <button type="submit">確認画面へ</button>
    </form>
</body>
</html>