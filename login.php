<?php
declare(strict_types=1);

require __DIR__ . '/task.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erros = [];
$redirect = $_POST['redirect'] ?? ($_GET['redirect'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Erro de validação de CSRF');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email === '' || $password === '') {
        $erros[] = 'E-mail e senha são obrigatórios';
    } else {
        $user = authenticateUser($email, $password);    
        if (!$user) {
            $erros[] = 'Credenciais inválidas';
        } else {
            logedUser($user);
            header('Location: ' . ($redirect ?: 'index.php'));
            exit;
        }
    }
}

$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="register.css">
    <title>Document</title>
</head>
<body>
    <main class="auth-container">
        <h1>Entrar</h1>
        <?php foreach ($erros as $err): ?>
            <div class="error">
                <?php echo htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE); ?>
            </div>
        <?php endforeach; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect, ENT_QUOTES | ENT_SUBSTITUTE); ?>">
            <input 
                type="email" 
                name="email"
                placeholder="Seu e-mail"
                value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>" 
                required
            >
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
        <div class="link">Não tem conta? <a href="register.php">Criar</a></div>
        
    </main>  
</body>
</html>