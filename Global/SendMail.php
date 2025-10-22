<?php

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Creating a class
class SendMail {
    public function Send_Mail($conf, $mailCnt) {
        //Load Composer's autoloader (created by composer, not included with PHPMailer)
        require __DIR__ . '/../vendor/autoload.php';
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = $conf['smtp_host'];                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = $conf['smtp_user'];                     //SMTP username
    $mail->Password   = $conf['smtp_pass'];                     //SMTP password
    // Configure encryption based on port
    if ($conf['smtp_port'] == 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        // SSL for port 465
    } elseif ($conf['smtp_port'] == 587) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // STARTTLS for port 587
    }
    $mail->Port = $conf['smtp_port'];                           // Set the port

    //Recipients
    $mail->setFrom($mailCnt['mail_from'], $mailCnt['name_from']);
    $mail->addAddress($mailCnt['mail_to'], $mailCnt['name_to']);     //Add a recipient

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $mailCnt['subject'];
    $mail->Body    = $mailCnt['body'];

    $mail->send();
    return true; // Return success
} catch (Exception $e) {
    error_log("Email sending failed: " . $e->getMessage()); // Log detailed error
    error_log("SMTP Debug Info: " . $mail->ErrorInfo); // Log SMTP debug info
    return false; // Return failure status
}
    }
}