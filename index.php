<?php
require __DIR__ . '/task.php';

$currentUser = getCurrentUser();

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editingTask = null;

if ($editId !== null && $editId !== false) {
    $editingTask = getTaskById($editId);
}

$tasks = getTasks();
$formAction = htmlspecialchars($_SERVER['PHP_SELF'] ?? 'index.php', ENT_QUOTES | ENT_SUBSTITUTE);
$requestPath = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: 'index.php';
$requestPathEscaped = htmlspecialchars($requestPath, ENT_QUOTES | ENT_SUBSTITUTE);
$csrfToken = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Tarefas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class= "container">
        <h1>Gerenciador de Tarefas</h1>

        <div>
            <div style="font-size: 0.9rem, color: #372151;">
                Logado como: <strong><?php echo htmlspecialchars($currentUser['name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE); ?></strong>
            </div>
            <div>
                <form action="logout.php"></form>
            </div>
        </div>

        <form action="<?php echo $formAction; ?>" method="POST" id="task-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="<?php echo $editingTask ? 'edit' : 'add'; ?>">
            <?php if ($editingTask): ?>
                <input type="hidden" name="task_id" value="<?php echo (int) $editingTask['id']; ?>">
            <?php endif; ?>
            <input type="text" name="task_text" id="task-input" placeholder="Adicionar nova tarefa..."
                value="<?php echo $editingTask ? htmlspecialchars($editingTask['text'], ENT_QUOTES | ENT_SUBSTITUTE) : ''; ?>"
                required>
            <button type="submit" class="add-button"><?php echo $editingTask ? 'Atualizar' : 'Adicionar'; ?></button>
            <?php if ($editingTask): ?>
                <a href="<?php echo $requestPathEscaped; ?>" class="cancel-edit">Cancelar</a>
            <?php endif; ?>
        </form>
        <div class="task-list">
            <?php if (empty($tasks)): ?>
                <p style="text-align: center; color: #6b7280">
                    Nenhuma tarefa adicionada ainda.
                </p>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <?php $taskId = (int) $task['id']; ?>
                    <?php $isCompleted = !empty($task['completed']); ?>
                    <div class="task-item <?php echo $isCompleted ? 'completed' : ''?>">
                        <span class="task-text">
                            <?php echo htmlspecialchars($task['text'], ENT_QUOTES | ENT_SUBSTITUTE); ?>
                        </span>
                        <div class="task-actions">
                            <form action="<?php echo $formAction; ?>" method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                <input type="hidden" name="action" value="<?php echo $isCompleted ? 'uncomplete' : 'complete'; ?>">
                                <input type="hidden" name="task_id" value="<?php echo $taskId; ?>">
                                <button type="submit" class="complete-button <?php echo $isCompleted ? 'completed' : ''?>">
                                    &#10003;
                                </button>
                            </form>
                            <a href="<?php echo htmlspecialchars($requestPath . '?edit=' . $taskId, ENT_QUOTES | ENT_SUBSTITUTE); ?>" class="edit-button" title="Editar">&#9998;</a>
                            <form action="<?php echo $formAction; ?>" method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="task_id" value="<?php echo $taskId; ?>">
                                <button type="submit" class="delete-button" title="Excluir">
                                    &#x2715;
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>