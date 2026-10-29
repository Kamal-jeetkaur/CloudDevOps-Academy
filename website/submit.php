<?php
require_once __DIR__ . '/db.php';

/** Strip tags, trim whitespace, collapse control characters */
function clean(string $value): string
{
    $value = trim($value);
    $value = strip_tags($value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);
    return $value;
}

function renderPage(string $title, string $message, bool $success): void
{
    $class = $success ? 'alert-success' : 'alert-error';
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMsg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>$safeTitle | Cloud Training Portal</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="site-header">
    <div class="container nav-wrap">
      <a href="index.html" class="logo">☁ CloudDevOps Academy</a>
      <nav>
        <a href="index.html">Home</a>
        <a href="courses.html">Courses</a>
        <a href="register.html">Register</a>
        <a href="feedback.html">Feedback</a>
      </nav>
    </div>
  </header>
  <main class="container narrow">
    <div class="card result-card">
      <h1>$safeTitle</h1>
      <div class="alert $class">$safeMsg</div>
      <p><a class="btn" href="index.html">Back to Home</a></p>
    </div>
  </main>
  <footer class="site-footer"><div class="container">&copy; 2026 CloudDevOps Academy</div></footer>
</body>
</html>
HTML;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    renderPage('Method Not Allowed', 'Please submit the form from the website.', false);
    exit;
}

$formType = clean($_POST['form_type'] ?? '');

try {
    $pdo = getConnection();

    if ($formType === 'register') {
        $name       = clean($_POST['full_name'] ?? '');
        $email      = filter_var(clean($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $phone      = clean($_POST['phone'] ?? '');
        $track      = clean($_POST['track'] ?? '');
        $experience = clean($_POST['experience'] ?? '');

        $allowedTracks = ['Linux & Cloud Fundamentals', 'DevOps & CI/CD', 'Docker & Containers', 'Full Cloud DevOps Bundle'];
        $allowedExp    = ['Beginner', 'Intermediate', 'Advanced'];

        if ($name === '' || strlen($name) < 2 || !$email
            || !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)
            || !in_array($track, $allowedTracks, true)
            || !in_array($experience, $allowedExp, true)) {
            http_response_code(422);
            renderPage('Enrollment Failed', 'Some fields were missing or invalid. Please go back and try again.', false);
            exit;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO enrollments (full_name, email, phone, track, experience)
             VALUES (:name, :email, :phone, :track, :exp)'
        );
        $stmt->execute([
            ':name'  => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':track' => $track,
            ':exp'   => $experience,
        ]);

        renderPage('Enrollment Successful', "Thank you, $name! You are enrolled in \"$track\". We'll email you at $email with next steps.", true);

    } elseif ($formType === 'feedback') {
        $name     = clean($_POST['student_name'] ?? '');
        $email    = filter_var(clean($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $rating   = filter_var($_POST['rating'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        $comments = clean($_POST['comments'] ?? '');

        if ($name === '' || !$email || $rating === false || strlen($comments) < 10) {
            http_response_code(422);
            renderPage('Feedback Failed', 'Some fields were missing or invalid. Comments must be at least 10 characters.', false);
            exit;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO feedback (student_name, email, rating, comments)
             VALUES (:name, :email, :rating, :comments)'
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':rating'   => $rating,
            ':comments' => $comments,
        ]);

        renderPage('Feedback Received', "Thank you, $name! Your feedback helps us improve the training.", true);

    } else {
        http_response_code(400);
        renderPage('Invalid Submission', 'Unknown form type.', false);
    }

} catch (Throwable $e) {
    error_log('Portal DB error: ' . $e->getMessage());
    http_response_code(500);
    renderPage('Server Error', 'Something went wrong while saving your data. Please try again later.', false);
}