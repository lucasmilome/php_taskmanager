<?php

declare(strict_types=1);

require 'task.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

}

$erros = [];
$sucess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            http_response_code(403);
            die('Erro de validação do CSRF');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST ['password'] ?? '');
    $passwordConfirm = trim($_POST['password_confirm'] ?? '');

    if ($name === '') $erros[] = 'Nome é obrigatório';
    if ($email === '') $erros[] = 'E-mail é obrigatório';
    if ($password === '') $erros[] = 'Senha é obrigatória';
    if ($password !== $passwordConfirm) $erros[] = 'Confirmação de senha não confere';

    if (!$erros) {
        $user = registerUser($name, $email, $password);

        if ($user === false) {
            $erros[] = 'E-mail já em uso';
        } else {
            logedUser($user);
            header('Location: index.php');
            exit;
        }
    }

}

$csrfToken = getCsrfToken();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de usúario</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-container">
        <h1>Criar conta</h1>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input 
                type="text" 
                name="name" 
                placeholder="Seu nome" 
                value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>" 
                required
            >
            <input 
                type="email" 
                name="email"
                placeholder="Seu e-mail"
                value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?>" 
                required
            >
            <input type="password" name="password" placeholder="Senha" required>
            <input type="password" name="password_confirm" placeholder="Confirme a senha" required>
            <button type="submit">Cadastrar</button>
        </form>
        <div class="link">
            Jã tem cadastro? <a href="login.php">Entrar</a>
        </div>
    </main>
</body>
</html>