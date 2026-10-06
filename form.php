<?php
/**
 * form.php
 * PURPOSE: The HTML form shared by create.php and edit.php (avoids duplicated markup).
 * Variables provided by the including page:
 *   $task        - array of current field values (empty defaults or DB row)
 *   $errors      - array of validation error messages
 *   $formAction  - URL the form posts to
 *   $submitLabel - text on the submit button
 */
?>
<?php if (!empty($errors)): ?>
    <!-- Show every validation error in a list (loop) -->
    <div class="flash error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- method="post": data goes in the request body, not the URL -->
<form method="post" action="<?= e($formAction) ?>" class="card">

    <label for="title">Title *</label>
    <input type="text" id="title" name="title" maxlength="150"
           value="<?= e($task['title']) ?>">

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4"><?= e($task['description']) ?></textarea>

    <label for="category">Category *</label>
    <select id="category" name="category">
        <?php foreach (CATEGORIES as $cat): ?>
            <!-- "selected" keeps the previously chosen value after an error or on edit -->
            <option value="<?= e($cat) ?>" <?= $task['category'] === $cat ? 'selected' : '' ?>>
                <?= e($cat) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="priority">Priority *</label>
    <select id="priority" name="priority">
        <?php foreach (PRIORITIES as $p): ?>
            <option value="<?= e($p) ?>" <?= $task['priority'] === $p ? 'selected' : '' ?>>
                <?= e($p) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="due_date">Due date *</label>
    <input type="date" id="due_date" name="due_date" value="<?= e($task['due_date']) ?>">

    <div class="actions">
        <button type="submit" class="btn"><?= e($submitLabel) ?></button>
        <a class="btn secondary" href="index.php">Cancel</a>
    </div>
</form>
