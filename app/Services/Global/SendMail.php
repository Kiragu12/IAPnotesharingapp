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
        require __DIR__ . '/../../../vendor/autoload.php';
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
    $debug_log = __DIR__ . '/../../../debug.log';
    error_log("SENDMAIL ERROR: " . $e->getMessage(), 3, $debug_log); 
    error_log("SENDMAIL SMTP DEBUG: " . $mail->ErrorInfo, 3, $debug_log); 
    
    // Log the full configuration (without password) for debugging
    error_log("SENDMAIL CONFIG - Host: " . $conf['smtp_host'] . ", Port: " . $conf['smtp_port'] . ", User: " . $conf['smtp_user'], 3, $debug_log);
    
    return false; // Return failure status
}
    }
}