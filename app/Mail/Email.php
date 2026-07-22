<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Email extends Mailable
{
    use Queueable, SerializesModels;
    public $message;
    public $subject;
    public $email;
    public $file;
    public $bcc_emails;
    public $cc_emails;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $subject, $message, $file=null, $bcc_emails = null, $cc_emails = null)
    {
        $this->email            =   $email;
        $this->subject          =   $subject;
        $this->message          =   $message;
        $this->bcc_emails       =   $bcc_emails;
        $this->cc_emails        =   $cc_emails;
        $this->file = $file;

        // BCC to manaeger IT's email ID,  payement module launch notifications : Aman)
        if($subject == "Important Notice: Regarding Online Monthly Payments")
        {
            $this->bcc_emails   =   "support.pwrda@punjab.gov.in";
        }

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $return         =   false;
        $email          =   $this->email;
        $subject        =   $this->subject;
        $message        =   $this->message;
        $file           =   $this->file;
        $bcc            =   $this->bcc_emails;
        $cc            =   $this->cc_emails;
            
        $mail = $this->to($email)->subject($subject)->html($message);

        // Attach file if provided
        if (!is_null($file)) {
            $mail->attach($file);
        }

        // Add BCC if provided
        if (!is_null($bcc)) {
            $mail->bcc($bcc);
        }

        // Add CC if provided
        if (!is_null($cc)) {
            $mail->cc($cc);
        }
        return $mail;
    }
}

