<?php
require_once 'security.php';

// POSTリクエスト以外、またはセッションデータがない場合はリダイレクト
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['signup_input'])) {
    header('Location: signup.php');
    exit;
}

// CSRFトークンの検証
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    die('不正なリクエストです。(CSRF Token Invalid)');
}

// セッションから入力データを取得
$input = $_SESSION['signup_input'];

// データベース挿入用のデータを生成
$uuid = generate_uuid();
$password_hash = password_hash($input['password'], PASSWORD_DEFAULT); // 安全なハッシュ化
$created_at = date('Y-m-d H:i:s');
$agreed_terms = $input['agreed_terms'];
$username = $input['username'];

// データベースへの書き込み処理
$pdo = get_db_connection();

try {
    // ユーザー名の重複チェック（一応の防御）
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['error'] = 'このユーザー名は既に登録されています。';
        header('Location: signup.php');
        exit;
    }

    // INSERT文の準備
    $sql = "INSERT INTO users (uuid, username, password, agreed_terms, created_at) VALUES (:uuid, :username, :password, :agreed_terms, :created_at)";
    $stmt = $pdo->prepare($sql);
    
    // データの実行
    $stmt->execute([
        ':uuid' => $uuid,
        ':username' => $username,
        ':password' => $password_hash,
        ':agreed_terms' => $agreed_terms,
        ':created_at' => $created_at
    ]);

    // 登録に成功したら、自動ログイン処理（sessionのuser_idに格納）
    $new_user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $new_user_id;

    // 使用済みの登録入力セッションとCSRFトークンをクリア（二重送信防止）
    unset($_SESSION['signup_input']);
    unset($_SESSION['csrf_token']);

} catch (PDOException $e) {
    die('登録処理中にエラーが発生しました: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規会員登録 - 完了</title>
</head>
<body>
    <h2>登録完了</h2>
    <p>ユーザーの登録が正常に完了しました！</p>
    <p>あなたの内部ユーザーIDは <strong><?php echo htmlspecialchars($new_user_id, ENT_QUOTES, 'UTF-8'); ?></strong> です。</p>
    <p>識別用UUID: <code><?php echo htmlspecialchars($uuid, ENT_QUOTES, 'UTF-8'); ?></code></p>
    <br>
    <p><a href="signup.php">トップへ戻る（続けて登録）</a></p>
</body>
</html>