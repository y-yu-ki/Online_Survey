<?php
require_once 'security.php';

// POSTリクエスト以外は入力画面へ強制リダイレクト
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit;
}

// CSRFトークンの検証
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    die('不正なリクエストです。(CSRF Token Invalid)');
}

// パラメータの受け取りとバリデーション
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
$agree = isset($_POST['agree']) ? $_POST['agree'] : '';

// 簡易バリデーション
if ($username === '' || $password === '') {
    $_SESSION['error'] = 'ユーザー名とパスワードは必須入力です。';
    header('Location: signup.php');
    exit;
}

if ($password !== $password_confirm) {
    $_SESSION['error'] = 'パスワードが一致しません。';
    header('Location: signup.php');
    exit;
}

if ($agree !== '1') {
    $_SESSION['error'] = '利用規約への同意が必要です。';
    header('Location: signup.php');
    exit;
}

// 入力データをセッション（signup_input）に一時保存
$_SESSION['signup_input'] = [
    'username' => $username,
    'password' => $password, // 本番環境では一時保存のパスワードの扱いにも注意（今回はシンプル実装）
    'agreed_terms' => 1
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規会員登録 - 確認</title>
</head>
<body>
    <h2>登録内容の確認</h2>
    <p>以下の内容で登録します。よろしければ「登録する」ボタンを押してください。</p>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ユーザー名</th>
            <td><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <th>パスワード</th>
            <td>******** （セキュリティのため非表示）</td>
        </tr>
    </table>

    <br>
    
    <form action="signup_complete.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        
        <button type="button" onclick="history.back();">戻って修正する</button>
        <button type="submit">登録する</button>
    </form>
</body>
</html>