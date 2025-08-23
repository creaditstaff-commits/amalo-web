<?php
// submit.php

function clean($v) {
  return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

// Honeypot
if (!empty($_POST['company'])) {
  header('Location: thankyou.html');
  exit();
}

// Collect
$name     = clean($_POST['name'] ?? '');
$phone    = clean($_POST['phone'] ?? '');
$email    = clean($_POST['email'] ?? '');
$business = clean($_POST['business'] ?? '');
$revenue  = clean($_POST['revenue'] ?? '');
$loan     = clean($_POST['loan'] ?? '');
$message  = clean($_POST['message'] ?? '');

$errors = [];
if ($name === '')                               { $errors[] = 'Name is required.'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
if (!preg_match('/^[0-9+\s]{9,20}$/', $phone))  { $errors[] = 'Valid phone is required.'; }
if ($business === '')                           { $errors[] = 'Business name is required.'; }
if ($revenue === '' || !is_numeric($revenue))   { $errors[] = 'Monthly revenue is required.'; }
if ($loan === '' || !is_numeric($loan))         { $errors[] = 'Desired loan amount is required.'; }
if ($loan > 200000)                             { $errors[] = 'Maximum loan amount is UGX 200,000.'; }

if (!empty($errors)) {
  http_response_code(422);
  echo "<h2>There were errors with your submission:</h2><ul>";
  foreach ($errors as $e) echo "<li>". $e ."</li>";
  echo "</ul><p><a href='javascript:history.back()'>Go back</a></p>";
  exit();
}

// Configure recipient
$to = 'amalocredit@gmail.com'; // change this to your real inbox
$subject = "New Loan Application: {$name}";

// Email body
$body = "You have received a new loan application:\n\n" .
        "Name: {$name}\n" .
        "Email: {$email}\n" .
        "Phone: {$phone}\n" .
        "Business: {$business}\n" .
        "Monthly Revenue (UGX): {$revenue}\n" .
        "Desired Loan (UGX): {$loan}\n" .
        "Message: {$message}\n" .
        "Submitted: " . date('Y-m-d H:i:s') . "\n";

$fromAddress = 'no-reply@amalocredit.com'; // must match your domain
$headers  = "From: Amalo Credit <{$fromAddress}>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = @mail($to, $subject, $body, $headers);

if ($sent) {
  header('Location: thankyou.html');
  exit();
} else {
  http_response_code(500);
  echo "<h2>Sorry, something went wrong sending your application.</h2>";
  echo "<p>Please email us directly at <a href='mailto:amalocredit@gmail.com'>amalocredit@gmail.com</a>.</p>";
  exit();
}
