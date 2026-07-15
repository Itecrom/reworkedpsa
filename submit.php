<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('display_errors', 1);
error_reporting(E_ALL);

include('database.php');

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name    = $_POST['name'] ?? '';
    $subject = $_POST['subject'] ?? 'Contact Form Message';
    $email   = $_POST['email'] ?? '';
    $mobile  = $_POST['mobile'] ?? '';
    $comment = $_POST['message'] ?? '';

    // DB insert (safe version)
    mysqli_query($con, "INSERT INTO contact_us(name,subject,email,mobile,message)
    VALUES(
        '".mysqli_real_escape_string($con,$name)."',
        '".mysqli_real_escape_string($con,$subject)."',
        '".mysqli_real_escape_string($con,$email)."',
        '".mysqli_real_escape_string($con,$mobile)."',
        '".mysqli_real_escape_string($con,$comment)."'
    )");

    // PSA EMAIL TEMPLATE
    $html = "
    <div style='font-family:Arial;background:#f4f4f4;padding:20px'>
      <div style='max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden'>

        <div style='background:#000;padding:15px;text-align:center'>
          <h2 style='color:#e31b23;margin:0'>PSA Malawi</h2>
          <p style='color:#4CAF50;margin:5px 0'>New Contact Message</p>
        </div>

        <div style='padding:20px'>
          <p><b>Name:</b> $name</p>
          <p><b>Subject:</b> $subject</p>
          <p><b>Email:</b> $email</p>
          <p><b>Mobile:</b> $mobile</p>
          <p><b>Message:</b> $comment</p>
        </div>

        <div style='background:#000;color:#fff;text-align:center;padding:10px;font-size:12px'>
          © ".date('Y')." PSA Malawi
        </div>

      </div>
    </div>";

    // PHPMailer (MODERN - PHP 8 READY)
    require 'phpmailer/src/Exception.php';
    require 'phpmailer/src/PHPMailer.php';
    require 'phpmailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host = 'mail.malawipsa.org';
        $mail->SMTPAuth = true;
        $mail->Username = 'admin@malawipsa.org';
        $mail->Password = '@PSA2025';

        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('admin@malawipsa.org', 'PSA Malawi');
        $mail->addAddress('admin@malawipsa.org');

        $mail->addReplyTo($email, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;

        $mail->send();

        echo "Message sent successfully";

    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
?>
