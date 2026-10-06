<?php
require 'admin_check.php';

$quizzes = $pdo->query('SELECT q.id, q.title, q.type, COUNT(qs.id) AS questions_count FROM quizzes q LEFT JOIN questions qs ON qs.quiz_id = q.id GROUP BY q.id ORDER BY q.id DESC ')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Admin Panel</h1>
<p><a href="../index.php">Back to main page</a> | <a href="../quiz_create.php">Create quiz</a></p>

<table>
  <tr>
    <th>ID</th>
    <th>Title</th>
    <th>Type</th>
    <th>Questions</th>
    <th>Actions</th>
  </tr>

  <?php foreach ($quizzes as $q): ?>
    <tr>
      <td><?= $q['id'] ?></td>
      <td><?= htmlspecialchars($q['title']) ?></td>
      <td><?= htmlspecialchars($q['type']) ?></td>
      <td><?= $q['questions_count'] ?></td>
      <td>
        <a href="quiz.php?id=<?= $q['id'] ?>">Play</a> |
        <a href="quiz_edit.php?id=<?= $q['id'] ?>">Edit</a> |
        <a href="quiz_delete.php?id=<?= $q['id'] ?>">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

</body>
</html>