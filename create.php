<?php
/**
 * create.php
 * PURPOSE: CREATE part of CRUD.
 *   GET  request -> show an empty form.
 *   POST request -> validate, INSERT into the database, redirect to the list.
 */
require 'db.php';          // gives us $pdo
require 'functions.php';   // gives us validate_task(), set_flash(), etc.

$errors = [];

// Default values shown in the empty form.
$task = [
    'title'       => '',
    'description' => '',
    'category'    => 'Assignment',
    'priority'    => 'Medium',
    'due_date'    => '',
];

// Only run when the form was submitted (POST), not when the page is just opened (GET).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate and clean the submitted data. [ $errors, $task ] unpacks the returned array.
    [$errors, $task] = validate_task($_POST, true);

    // Save only when there are no validation errors.
    if (empty($errors)) {
        try {
            // Prepared statement: :placeholders are filled safely -> prevents SQL injection.
                $sql = 'INSERT INTO tasks (title, description, category, priority, due_date, completed)
                    VALUES (:title, :description, :category, :priority, :due_date, 0)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title'       => $task['title'],
                ':description' => $task['description'],
                ':category'    => $task['category'],
                ':priority'    => $task['priority'],
                ':due_date'    => $task['due_date'],
            ]);

            set_flash('success', 'Task created successfully.'); // message stored in session
            header('Location: index.php');                      // redirect to the list
            exit;                                               // stop the script after redirect
        } catch (PDOException $e) {
            // Database problem: show a generic message instead of crashing.
            $errors[] = 'Could not save the task. Please try again.';
        }
    }
}

// Settings for the shared form + header.
$pageTitle   = 'Add Task';
$formAction  = 'create.php';
$submitLabel = 'Save Task';

require 'header.php';
?>
<h2>Add a New Task</h2>
<?php require 'form.php'; ?>
<?php require 'footer.php'; ?>
