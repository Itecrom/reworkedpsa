<?php
require_once 'includes/public_form_security.php';
include 'includes/db.php';

$formStatus = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formStatus = validatePublicForm('contact');
    $name = trim((string)($_POST['name'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $mobile = trim((string)($_POST['mobile'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($formStatus === '' && ($name === '' || $subject === '' || $email === '' || $message === '')) {
        $formStatus = 'required';
    }
    if ($formStatus === '' && (
        mb_strlen($name) > 255 ||
        mb_strlen($subject) > 255 ||
        mb_strlen($email) > 254 ||
        mb_strlen($mobile) > 50 ||
        mb_strlen($message) > 20000 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        preg_match('/[\r\n]/', $subject)
    )) {
        $formStatus = 'invalid';
    }

    if ($formStatus === '') {
        try {
            $stmt = $conn->prepare('INSERT INTO contact_us (name, subject, email, mobile, message) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sssss', $name, $subject, $email, $mobile, $message);
            $saved = $stmt->execute();
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            error_log('Contact form database insert failed: ' . $e->getMessage());
            $saved = false;
        }

        if (!$saved) {
            $formStatus = 'error';
        } else {
            $recipient = getenv('PSA_CONTACT_EMAIL') ?: 'info@malawipsa.org';
            $safeName = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeSubject = htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeEmail = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeMobile = htmlspecialchars($mobile, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            $html = '<h2>New PSA Malawi contact message</h2>'
                . '<p><strong>Name:</strong> ' . $safeName . '</p>'
                . '<p><strong>Subject:</strong> ' . $safeSubject . '</p>'
                . '<p><strong>Email:</strong> ' . $safeEmail . '</p>'
                . '<p><strong>Phone:</strong> ' . $safeMobile . '</p>'
                . '<p><strong>Message:</strong><br>' . $safeMessage . '</p>';
            $headers = "MIME-Version: 1.0\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n"
                . "From: PSA Malawi Website <no-reply@malawipsa.org>\r\n"
                . 'Reply-To: ' . $email . "\r\n";

            if (function_exists('mail') && mail($recipient, 'Website contact: ' . $subject, $html, $headers)) {
                $formStatus = 'success';
            } else {
                error_log('Contact form email notification failed after the message was saved.');
                $formStatus = 'saved';
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="page-title overlay" style="background-image: url(images/background/page-title.jpg);">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="text-white font-weight-bold">Contact the Association</h2>
                <p class="text-white mb-0">We welcome questions, partnerships and research enquiries.</p>
            </div>
        </div>
    </div>
</section>

<section class="section contact-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="contact-information h-100">
                    <span class="section-title-sm">Get in touch</span>
                    <h2 class="section-title">We would be glad to hear from you</h2>
                    <p class="text-muted mb-4">Contact the Malawi Political Science Association for membership, events, publications and general enquiries.</p>
                    <div class="contact-detail">
                        <i class="round-icon mr-3 ti-mobile" aria-hidden="true"></i>
                        <div><strong>Phone</strong><p class="mb-0"><a href="tel:+265995623818">+265 (0) 995 623 818</a><br><a href="tel:+265888229970">+265 (0) 888 229 970</a></p></div>
                    </div>
                    <div class="contact-detail">
                        <i class="round-icon mr-3 ti-email" aria-hidden="true"></i>
                        <div><strong>Email</strong><p class="mb-0"><a href="mailto:info@malawipsa.org">info@malawipsa.org</a></p></div>
                    </div>
                    <div class="contact-detail">
                        <i class="round-icon mr-3 ti-map-alt" aria-hidden="true"></i>
                        <div><strong>Postal and office address</strong><p class="mb-0">P.O. Box 624, Zomba, Malawi<br>4 Miles, off M3 Road, next to Zipatso LTD, Zomba</p></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="contact-form-card">
                    <h3 class="mb-2">Send us a message</h3>
                    <p class="text-muted mb-4">Fields marked required help us respond to your enquiry.</p>
                    <?php if ($formStatus === 'success'): ?>
                        <div class="alert alert-success" role="status">Thank you. Your message has been sent.</div>
                    <?php elseif ($formStatus === 'saved'): ?>
                        <div class="alert alert-warning" role="status">Your message was saved, but we could not send an email notification. We will follow up as soon as possible.</div>
                    <?php elseif ($formStatus === 'error'): ?>
                        <div class="alert alert-danger" role="alert">We could not save your message. Please try again later or contact us by phone.</div>
                    <?php elseif ($formStatus === 'captcha'): ?>
                        <div class="alert alert-warning" role="alert">Please answer the security check correctly and try again.</div>
                    <?php elseif ($formStatus !== ''): ?>
                        <div class="alert alert-danger" role="alert">Please check your details and try submitting the form again.</div>
                    <?php endif; ?>

                    <form action="contact.php" method="post" class="row">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(publicFormToken('contact'), ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-honeypot" aria-hidden="true">
                            <label for="contact-website">Leave this field empty</label>
                            <input id="contact-website" type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="contact-name">Full name <span aria-hidden="true">*</span></label>
                            <input id="contact-name" type="text" name="name" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="contact-email">Email address <span aria-hidden="true">*</span></label>
                            <input id="contact-email" type="email" name="email" class="form-control" maxlength="254" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="contact-subject">Subject <span aria-hidden="true">*</span></label>
                            <input id="contact-subject" type="text" name="subject" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="contact-mobile">Phone number</label>
                            <input id="contact-mobile" type="tel" name="mobile" class="form-control" maxlength="50">
                        </div>
                        <div class="col-12 form-group">
                            <label for="contact-message">Message <span aria-hidden="true">*</span></label>
                            <textarea id="contact-message" name="message" class="form-control p-2" maxlength="20000" required rows="6"></textarea>
                        </div>
                        <div class="col-12">
                            <?= publicFormCaptchaField('contact', 'contact-captcha') ?>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">Send message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="contact-map">
    <iframe title="Map showing the PSA Malawi office in Zomba" src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d1473.4949687723984!2d35.29738457796264!3d-15.421573962320654!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e1!3m2!1sen!2smw!4v1788870252616!5m2!1sen!2smw" width="100%" height="400" style="border:0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</section>

<?php include 'includes/footer.php'; ?>
