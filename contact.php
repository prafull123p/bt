<?php include 'header.php'; ?>
<?php include('db.php'); ?>

<?php 
$message_sent = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userName = htmlspecialchars($_POST["name"]);
    $userEmail = htmlspecialchars($_POST["email"]);
    $userMessage = htmlspecialchars($_POST["message"]);

    if (!empty($userName) && !empty($userEmail) && !empty($userMessage)) {
        $to = "your-email@example.com"; // Replace with your email
        $subject = "New Contact Message from $userName";
        $body = "Name: $userName\nEmail: $userEmail\n\nMessage:\n$userMessage";
        $headers = "From: $userEmail";

        if (mail($to, $subject, $body, $headers)) {
            $message_sent = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact Us - Batmul College</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f4f4;
          
        }

        .contact-form {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .contact-form h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .contact-form button {
            background: #2980b9;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .contact-form button:hover {
            background: #1f6391;
        }

        .success {
            background: #dff0d8;
            padding: 15px;
            border-radius: 6px;
            color: #3c763d;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="contact-form">
        <h2>Contact Us</h2>

        <?php if ($message_sent): ?>
            <div class="success">Thank you! Your message has been sent.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="name" placeholder="Your Name" required />
            <input type="email" name="email" placeholder="Your Email" required />
            <textarea name="message" rows="6" placeholder="Your Message" required></textarea>
            <button type="submit">Send Message</button>
        </form>
    </div>

    <?php include('footer.php') ?>