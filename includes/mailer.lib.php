<?php
include_once(__DIR__.'/../PHPMailer/PHPMailerAutoload.php');

/**
 * id: developerkimtakgu@gmail.com/pw: dltkdghrytnsla//app key:otigukngcxepqrdi
 * imap 설정함
 * smtp.naver.com
 */
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="")
{
    if ($type != 1)
        $content = nl2br($content);

    $mail = new PHPMailer(); // defaults to using php "mail()"
	
	$mail->IsSMTP(); 
	//$mail->SMTPDebug = 2; 
	$mail->SMTPSecure = "ssl";
	//$mail->Encoding = 'base64';

	$mail->Host = "smtp.gmail.com"; 
	$mail->Port = 465; 
	$mail->Username = "developerkimtakgu@gmail.com";
	$mail->Password = "otigukngcxepqrdi"; 
	
	$mail->SMTPAuth = true;
	
	$fmail = 'developerkimtakgu@gmail.com';
    $mail->CharSet = 'UTF-8';
    $mail->From = $fmail;
    $mail->FromName = $fname;
    $mail->Subject = $subject;
    $mail->AltBody = ""; // optional, comment out and test
    $mail->msgHTML($content);
    $mail->addAddress($to);
	
	$mail->SetFrom('developerkimtakgu@gmail.com', $fname);
	$mail->AddReplyTo('developerkimtakgu@gmail.com', $fname);


    if ($cc)
        $mail->addCC($cc);
    if ($bcc)
        $mail->addBCC($bcc);

    if ($file != "") {
        foreach ($file as $f) {
            $mail->addAttachment($f['path'], $f['name']);
        }
    }
    return $mail->send();
}

// 파일을 첨부함
function attach_file($filename, $tmp_name)
{
    // 서버에 업로드 되는 파일은 확장자를 주지 않는다. (보안 취약점)
    $dest_file = '경로지정/tmp/'.str_replace('/', '_', $tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    $tmpfile = array("name" => $filename, "path" => $dest_file);
    return $tmpfile;
}
?>