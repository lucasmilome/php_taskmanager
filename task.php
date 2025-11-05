<?php

declare(strict_types=1);

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

const DB_PATH = __DIR__ . '/task.db';

/** @return PDO */
function getConnection(): PDO {
    static $pdo = null;


    if ($pdo == null) {
        $pdo = new PDO('sqlite:' .DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } 

    return $pdo;
}

function initializeDatabase(): void {
    $pdo = getConnection();
    $pdo->exec(
         'CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT, 
            text TEXT NOT NULL,
            completed INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
}

initializeDatabase();

function getTasks(): array {
    $pdo = getConnection();
    $stmt = $pdo->query('SELECT id, text, completed FROM tasks ORDER BY created_at DESC, id DESC');

    return $stmt->fetchAll() ?: [];
}


function getTaskById(int $taskId): ?array {
    $pdo = getConnection(); 
    $stmt = $pdo->prepare(
        'SELECT id, text, completed FROM tasks WHERE id = :id');
    $stmt->execute(['id' => $taskId]);
    $task = $stmt->fetch();

    return $task ?: null;        
}

function addTask(string $taskText): void {
    $text = trim($taskText);

    if ($text === '') {
        return;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare('INSERT INTO tasks (text) VALUES (:text)');
    $stmt->execute([':text' => htmlspecialchars(
        $text, ENT_QUOTES,'UTF-8')]);
}

function updateTask(int $taskId, string $taskText): void {
    $text = trim($taskText);

    if ($text === '') {
        return;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare('UPDATE tasks SET text = :text WHERE id = :id');
    $stmt->execute([
        ':text' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
        ':id' => $taskId,
    ]);
}

function deleteTask(int $taskId): void {
    $pdo = getConnection();
    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
    $stmt->execute([':id' => $taskId]);
}

function setTaskCompletion(int $taskId, bool $completed): void {
    $pdo = getConnection();
    $stmt = $pdo->prepare('UPDATE tasks SET completed = :completed WHERE id = :id');
    $stmt->execute([
        ':completed' => $completed ? 1 : 0,
        ':id'=> $taskId,
    ]);
}

function redirectToIndex(): void {
    $target = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    header('Location: ' . ($target ?: 'index.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die('Erro de validação do CSRF');
    }

    $action = $_POST['action'] ?? '';
    $taskId = isset($_POST['task_id']) ? (int) $_POST['task_id'] : 0;

    switch ($action) {
        case 'add':
            addTask($_POST['task_text']);
            break;
        case 'edit':
            if ($taskId > 0) {
                updateTask($taskId, $_POST['task_text'] ?? '');
            }
            break;
        case 'delete':
            if ($taskId > 0) {
                deleteTask($taskId);
            }   
            break;
        case 'complete':
            if ($taskId > 0) {
                setTaskCompletion($taskId, true);
            }
            break;
        case 'uncomplete':
            if ($taskId > 0) {
                setTaskCompletion($taskId, false);
            }
            break;
    }

    redirectToIndex();
}