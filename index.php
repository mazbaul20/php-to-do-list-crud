<?php

// TODO: Define a constant for the tasks file (tasks.json)
define("TASKS_FILE", "tasks.json");

// TODO: Create a function to load tasks from the tasks.json file
// This function should read the JSON file and return the decoded array
function loadTasks(): array {
    if(!file_exists(TASKS_FILE)){
        return [];
    }

    $data = file_get_contents(TASKS_FILE);

    return $data ? json_decode($data, true) : [];
}

// TODO: Create a function to save tasks to the tasks.json file
// This function should take an array of tasks and save it back to the JSON file
function saveTasks(array $tasks) : void {
    file_put_contents(TASKS_FILE, json_encode($tasks, JSON_PRETTY_PRINT));
}

// Load tasks from the tasks.json file
$tasks = loadTasks();

// TODO: Check if the form has been submitted using POST request
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['task']) && !empty(trim($_POST['task']))) {
        if(isset($_POST['id']) && $_POST['id'] !== "") {
            // Update existing task
            foreach ($tasks as &$task) {
                if ($task['id'] == $_POST['id']) {
                    $task['task'] = htmlspecialchars(trim($_POST['task']));
                    break;
                }
            }
        } else {
            // Add a new task with an auto-generated ID
            $newId = count($tasks) > 0 ? max(array_column($tasks, 'id')) + 1 : 1;
            $tasks[] = [
                // 'id' => count($tasks)+1,
                'id' => $newId,
                'task' => htmlspecialchars(trim($_POST['task'])),
                'done' => false
            ];
        }
        saveTasks($tasks);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif(isset($_POST['delete'])) {
        // Delete a task
        $tasks = array_filter($tasks, fn($task) => $task['id'] != $_POST['delete']);
        saveTasks(array_values($tasks));
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif(isset($_POST['toggle'])) {
        // Toggle task completion
        foreach ($tasks as &$task) {
            if ($task['id'] == $_POST['toggle']) {
                $task['done'] = !$task['done'];
                break;
            }
        }
        saveTasks($tasks);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>

<!-- UI -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/milligram/1.4.1/milligram.min.css">
    <style>
        body {
            margin-top: 20px;
        }
        .task-card {
            border: 1px solid #ececec; 
            padding: 20px;
            border-radius: 5px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }
        .task{
            color: #888;
        }
        .task-done {
            text-decoration: line-through;
            color: #888;
        }
        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        ul {
            padding-left: 20px;
        }
        button {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="task-card">
            <h1>To-Do App</h1>

            <!-- Add/Update Task Form -->
            <form method="POST">
                <input type="hidden" name="id" id="task-id">
                <div class="row">
                    <div class="column column-75">
                        <input type="text" name="task" id="task-input" placeholder="Enter a new task" required>
                    </div>
                    <div class="column column-25">
                        <button type="submit" class="button-primary">Save Task</button>
                    </div>
                </div>
            </form>

            <!-- Task List -->
            <h2>Task List</h2>
            <ul style="list-style: none; padding: 0;">
                <?php if(empty($tasks)): ?>
                    <li>No tasks yet. Add one above!</li>
                <?php else: ?>
                    <?php foreach($tasks as $task): ?>
                        <li class="task-item">
                            <form method="POST" style="flex-grow: 1;">
                                <input type="hidden" name="toggle" value="<?= $task['id'] ?>">
                                <button type="submit" style="border: none; background: none; cursor: pointer; text-align: left; width: 100%;">
                                    <span class="task <?= $task['done'] ? 'task-done' : '' ?>">
                                        <?= htmlspecialchars($task['task']) ?>
                                    </span>
                                </button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="delete" value="<?= $task['id'] ?>">
                                <button type="submit" class="button button-outline" style="margin-left: 10px;">Delete</button>
                            </form>
                            <button class="button button-outline" style="margin-left: 10px;" onclick="editTask(<?= $task['id'] ?>, '<?= htmlspecialchars($task['task']) ?>')">Update</button>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>

        </div>
    </div>

    <script>
        function editTask(id, task) {
            document.getElementById('task-id').value = id;
            document.getElementById('task-input').value = task;
            document.getElementById('task-input').focus();
        }
    </script>
</body>
</html>
