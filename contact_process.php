<?php
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer composer autoload

// Database config
$host = 'localhost';
$db   = 'pritam_portfolio';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// PDO connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Get POST data
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$subject = trim($_POST['subject']);
$message = trim($_POST['message']);

// Basic validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

// Save to database
$stmt = $pdo->prepare("INSERT INTO contact_messages (name,email,subject,message,created_at) VALUES (?,?,?,?,NOW())");
$stmt->execute([$name, $email, $subject, $message]);

// Send Email using PHPMailer
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'pritammakwana17561@gmail.com'; // your Gmail
    $mail->Password   = 'ssbd dykn ctct xwzf';    // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('pritammakwana17561@gmail.com', 'Portfolio Contact');
    $mail->addAddress('pritammakwana17561@gmail.com', 'Pritam Makwana'); // receive email

    // Content
    $mail->isHTML(true);
    $mail->Subject = "New Contact Message: $subject";
    $mail->Body = "
    <b>Name:</b> $name <br>
    <b>Email:</b> $email <br>
    <b>Subject:</b> $subject <br>
    <b>Message:</b><br>$message
    ";

    $mail->send();

    // Send confirmation to user
    $mail->clearAddresses(); // clear previous recipient
    $mail->addAddress($email, $name); // user email

    $mail->Subject = "Thank you for contacting me!";
    $mail->Body = "
    Hi $name 😊,<br><br>
    Thank you for reaching out. I have received your message:<br><br>
    <b>Subject:</b> $subject <br>
    <b>Message:</b> $message <br><br>
    I will get back to you as soon as possible.<br><br>
    You can also reach me directly via <a href='https://wa.me/919714217561' target='_blank'>WhatsApp</a> if needed.<br><br>
    Best regards,<br>
    <b>Pritam Makwana</b> 🎉
    ";


    $mail->send();

    echo json_encode([
        'status' => 'success',
        'message' => 'Thank you for reaching out, ' . htmlspecialchars($name) . '! 😊 Your message has been successfully sent. I will review it and get back to you as soon as possible. Have a great day! 🎉'
    ]);
} catch (Exception $e) {
    // echo json_encode(['status' => 'error', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    echo json_encode([
        'status' => 'error',
        'message' => "Message could not be sent via email. 
        You can alternatively contact me directly: 
        WhatsApp: +91 97142 17561, 
        Email: pritammakwana17561@gmail.com",
        'error_info' => $mail->ErrorInfo // optional, for debugging
    ]);
}
