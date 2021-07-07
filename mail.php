<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'php/Exception.php';
require 'php/PHPMailer.php';
require 'php/SMTP.php';

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST"
    && isset( $_POST[ "name" ] )
    && isset( $_POST[ "email" ] )
    && isset( $_POST[ "id" ] )
    && isset( $_POST[ "message" ] )
    && isset( $_POST[ "captcha" ] ) )
{
    $name = test_input( $_POST[ "name" ] );
    $email = test_input( $_POST[ "email" ] );
    $id = test_input( $_POST[ "id" ] );
    $message = test_input( $_POST[ "message" ] );
    $secret = "6Le3--IZAAAAACpmcaVvo2QmsovRLC3NHPwP_LxX";
    $response = $_POST[ "captcha" ];
    $verify = file_get_contents( "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}" );

    $captcha_success = json_decode( $verify );

    if ( $captcha_success -> success == false )
    {
        echo 'nok';
    }
    else if ( $captcha_success -> success == true )
    {
        $mail = new PHPMailer(true);

        $mailSentBy = 'no-reply@univerzal-djakovo.hr';
        $nameSentBy = 'E-Groblje Prijave';
        $mailReplyTo = $email;
        $nameReplyTo = $name;
        $mailRecipient = "groblje@univerzal-djakovo.hr";

        $mail->IsSMTP();
        $mail->Host = "mail.univerzal-djakovo.hr";
        $mail->SMTPSecure = 'ssl';
        $mail->SMTPAuth = true;
        $mail->Port = 465;
        $mail->Username = 'egroblje@univerzal-djakovo.hr';
        $mail->Password = '3YpB;la%elUI';

        $mail->CharSet = 'UTF-8';
        $mail->From = $mailSentBy;
        $mail->FromName = $nameSentBy;
        $mail->addAddress($mailRecipient);
        $mail->addReplyTo($mailReplyTo, $nameReplyTo);
        $mail->isHTML(true);
        $mail->Subject = "Prijava e-Groblje Đakovo za " . $id;

        $messageBody = '<p>Sustav za prijavu pogrešnih podatka na e-Groblje Đakovo je primio novu poruku korisnika ' . date("d.m.Y h:i:s") . '.</p>';
        $messageBody .= '<p><strong>ID grobnog mjesta</strong>: ' . $id . '</p>';
        $messageBody .= '<p><strong>Ime korisnika</strong>: ' . $name . '</p>';
        $messageBody .= '<p><strong>E-mail korisnika</strong>: ' . $email . '</p>';
        $messageBody .= '<p><strong>Uneseno zapažanje korisnika</strong>:</p>';
        $messageBody .= '<p>"<i>' . $message . '</i>"</p>';

        $mail->Body = $messageBody;
        $mail->AltBody = strip_tags($messageBody);

        try
        {
            $mail->send();
            echo "Poruka uspješno poslana!";
        }
        catch (Exception $e)
        {
            echo "Došlo je do greške prilikom slanja poruke. Molimo pokušajte kasnije!";
            file_put_contents('php/log.log', time() . ' - ' . $mail->ErrorInfo , FILE_APPEND | LOCK_EX);
        }
    }
}

function test_input( $data )
{
    $data = trim( $data );
    $data = stripslashes( $data );
    $data = htmlspecialchars( $data );
    return $data;
}