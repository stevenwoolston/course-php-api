<?php
include_once '../../inc/class.phpmailer.php';

class Config{
 
    private $email_host = "mail.woolston.com.au";
    private $email_username = "accounts@woolston.com.au";
    private $email_password = "H@nnahN0ah";
    private $email_from = "accounts@woolston.com.au";
    private $email_from_name = "Woolston Web Design Accounts";

    public $email_to_address;
    public $email_to_name;
    public $email_subject;
    public $email_body;
    public $smtp_debug = 0;
    public $smtp_attachment = null;
    public $smtp_attachment_name;
 
    public function send_email(){

        $mail = new PHPMailer();
 
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPDebug = $this->smtp_debug;

        $mail->Host = $this->email_host;
        $mail->Username = $this->email_username;
        $mail->Password = $this->email_password;
         
        $mail->setFrom($this->email_from, $this->email_from_name);
         
        $mail->AddAddress($this->email_to_address, $this->email_to_name);
        $mail->Subject = $this->email_subject;
        $mail->Body = $this->email_body;
        $mail->WordWrap = 50;
        $mail->IsHTML(true);

        if ($this->smtp_attachment != null) {
            $mail->addStringAttachment($this->smtp_attachment, $this->smtp_attachment_name);
        }

        if ($mail->Send()) {
            return true;
        }
        
        return(array("message" => "Unable to send email for Invoice Id " . $invoiceId, "data" => $mail));
    }
}
?>