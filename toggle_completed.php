<?php
/**
 * toggle_completed.php
 * PURPOSE: Mark a task complete or reopen it directly from the task list.
 * Accepts POST only, then redirects back to the list.
 */
require 'db.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('error', 'Invalid task id.');
} else {
    try {
        $task = find_task($pdo, $id);
        if ($task === null) {
            set_flash('error', 'Task not found.');
        } else {
            $completed = (int) !(bool) $task['completed'];
            $stmt = $pdo->prepare('UPDATE tasks SET completed = :completed WHERE id = :id');
            $stmt->execute([':completed' => $completed, ':id' => $id]);
            set_flash('success', $completed ? 'Task marked as completed.' : 'Task reopened.');
        }
    } catch (PDOException $e) {
        set_flash('error', 'Could not update the task status.');
    }
}

header('Location: index.php');
exit;