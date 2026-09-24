<?php
require_once __DIR__ . '/db.php';

$message = '';
$editingLesson = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$lessonId = filter_input(INPUT_POST, 'lesson_id', FILTER_VALIDATE_INT);
	$student = trim($_POST['student'] ?? '');
	$teacher = trim($_POST['teacher'] ?? '');
	$subject = trim($_POST['subject'] ?? '');

	if ($student === '' || $teacher === '' || $subject == '') {
		$message = 'Please complete all fields.';
	} elseif ($lessonId) {
		$statement = $conn->prepare(
			'UPDATE lessons SET student = ?, teacher = ?, subject = ? WHERE lesson_id = ?'
		);
		$statement->bind_param('sssi', $student, $teacher, $subject, $lessonId);
		$statement->execute();
		$statement->close();
		$message = 'Lesson updated successfully.';
	} else {
		$statement = $conn->prepare(
			'INSERT INTO lessons (student, teacher, subject) VALUES (?, ?, ?)'
		);
		$statement->bind_param('sss', $student, $teacher, $subject);
		$statement->execute();
		$statement->close();
		$message = 'Lesson added successfully.';
	}
}

if (isset($_GET['edit'])) {
	$lessonId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

	if ($lessonId) {
		$statement = $conn->prepare('SELECT * FROM lessons WHERE lesson_id = ?');
		$statement->bind_param('i', $lessonId);
		$statement->execute();
		$editingLesson = $statement->get_result()->fetch_assoc();
		$statement->close();
	}
}

$lessons = $conn->query('SELECT lesson_id, student, teacher, subject FROM lessons ORDER BY lesson_id DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Lessons</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<main>
		<h1>Lesson manager</h1>

		<section class="panel">
			<h2><?= $editingLesson ? 'Update lesson' : 'Add a lesson' ?></h2>
			<?php if ($message): ?>
				<p class="message"><?= htmlspecialchars($message) ?></p>
			<?php endif; ?>
			<form method="post">
				<?php if ($editingLesson): ?>
					<input type="hidden" name="lesson_id" value="<?= (int) $editingLesson['lesson_id'] ?>">
				<?php endif; ?>
				<div class="form-grid">
					<label>Student
						<input name="student" required value="<?= htmlspecialchars($editingLesson['student'] ?? '') ?>">
					</label>
					<label>Teacher
						<input name="teacher" required value="<?= htmlspecialchars($editingLesson['teacher'] ?? '') ?>">
					</label>
					<label>Subject
						<input name="subject" required value="<?= htmlspecialchars($editingLesson['subject'] ?? '') ?>">
					</label>
				</div>
				<button type="submit"><?= $editingLesson ? 'Update lesson' : 'Add lesson' ?></button>
				<?php if ($editingLesson): ?>
					<a class="cancel" href="index.php">Cancel</a>
				<?php endif; ?>
			</form>
		</section>

		<section class="panel">
			<h2>Saved lessons</h2>
			<table>
				<thead>
					<tr><th>ID</th><th>Student</th><th>Teacher</th><th>Subject</th><th>Action</th></tr>
				</thead>
				<tbody>
					<?php while ($lesson = $lessons->fetch_assoc()): ?>
						<tr>
							<td><?= (int) $lesson['lesson_id'] ?></td>
							<td><?= htmlspecialchars($lesson['student']) ?></td>
							<td><?= htmlspecialchars($lesson['teacher']) ?></td>
							<td><?= htmlspecialchars($lesson['subject']) ?></td>
							<td><a href="?edit=<?= (int) $lesson['lesson_id'] ?>">Edit</a></td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</section>
	</main>
</body>
</html>
<?php $conn->close(); ?>
