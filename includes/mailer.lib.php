<?php
include_once(__DIR__.'/../PHPMailer/PHPMailerAutoload.php');

// 네이버 메일 전송m
// 메일 -> 환경설정 -> POP3/IMAP 설정 -> POP3/SMTP & IMAP/SMTP 중에 IMAP/SMTP 사용

// 메일 보내기 (파일 여러개 첨부 가능)
// mailer("보내는 사람 이름", "보내는 사람 메일주소", "받는 사람 메일주소", "제목", "내용", "type");
// type : text=0, html=1, text+html=2

// ex) mailer("kOO", "zzxp@naver.com", "zzxp@naver.com", "제목 테스트", "내용 테스트", 1);
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="")
{
    /**
     * [네이버] 
     * id: double255@naver.com/pw: phpmyadmin@/port: 465/app key: BVLNL2GPZ2LV
     * smtp 설정함
     * 이상민 카톡 확인 후 정리 할 것 
     * smtp.naver.com
     * [구글]
     * id: developerkimtakgu@gmail.com/pw: dltkdghrytnsla/prot: ??/app key:otigukngcxepqrdi
     * imap 설정함
     * smtp.naver.com
     */
    if ($type != 1)
        $content = nl2br($content);

    $mail = new PHPMailer(); // defaults to using php "mail()"
	
	$mail->IsSMTP(); 
	$mail->SMTPDebug = 2; 
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