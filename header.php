<?php
/**
 * header.php
 * PURPOSE: Shared top part of every page (HTML head, title, navigation, flash message).
 * Included with `require 'header.php';` so the layout is written only once.
 * Expects the including page to set $pageTitle (falls back to a default).
 */
require_once __DIR__ . '/functions.php';
$flash = get_flash(); // read (and clear) any success/error message from the session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Student Task Manager') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header class="topbar">
        <h1>Student Task Manager</h1>
        <a class="btn" href="create.php">+ New Task</a>
    </header>

    <?php if ($flash): ?>
        <!-- Flash message: class "success" or "error" controls the colour -->
        <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
