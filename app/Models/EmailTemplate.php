<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log

use Auth;
use App\Models\User;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailTemplateDescription;
use App\Mail\Email;

class EmailTemplate extends Model
{
    //use Loggable; //for creating log
    use HasFactory;


    protected $primaryKey = 'template_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Get the description for the email template.
     */
    public function descriptions()
    {
        return $this->hasMany(EmailTemplateDescription::class, 'template_id', 'template_id');
    }

    /**
     * Get the description for the email template.
     */

    public function description()
    {
        $dlanguage = '1';
        if (defined('default_language'))
            $dlanguage = default_language;
        return $this->hasone(EmailTemplateDescription::class, 'template_id', 'template_id')->where('language_id', $dlanguage);
    }

    /**
     * forgotPasswordMail
     *
     * To                  :   Applicant
     * $email_data         :   Array
     *
     */
    public static function forgotPasswordMail($email_data)
    {

        $applicant_name = $email_data['applicant_name'];
        $email = $email_data['email'];
        $hash = $email_data['hash'];

        $templateModel = new EmailTemplate();
        $templateData = $templateModel->with('description')->where('template_id', '=', '4')->first()->toArray();
        if ($templateData) {
            $subject = $templateData['description']['subject'];
            $message = $templateData['description']['message'];
            $verification_url = url('auth/forgot-password-change/' . $hash);
            $link = '<a href="' . $verification_url . '">Click here to change your password</a>';

            $website_name = $website_url = '';
            if (defined('website_name'))
                $website_name = website_name;
            if (defined('website_url'))
                $website_url = website_url;



            $footerText = '<a style="color: #fdc12f; text-decoration: none;"  href="' . $website_url . '">' . $website_name . '</a>';
            $message = str_replace(['{firstname}', '{link}', '{WEBSITE_URL}'], [$applicant_name, $link, $footerText], $message);
            try {
                Mail::send(new Email($email,$subject,$message));
                EmailLog::EmailLog($email,$subject,$message,"Success");
            } catch(\Exception $e) {
                EmailLog::EmailLog($email,$subject,$message,"Failed", "Reason : ".$e->getMessage());
                return  false;
            }
            return true;
        }
        return false;
    }

    public static function activationMail($email_data)
    {
        $website_name = $website_url = '';
        if (defined('website_name'))
            $website_name = website_name;
        if (defined('website_url'))
            $website_url = website_url;
        $footerText = '<a style="color: #fdc12f; text-decoration: none;"  href="' . $website_url . '">' . $website_name . '</a>';

        $applicant_name = ucwords($email_data['applicant_name']);
        $email = $email_data['email'];
        $hash = $email_data['hash'];

        $templateModel = new EmailTemplate();
        $templateData = $templateModel->with('description')->where('template_id', '=', '1')->first()->toArray();
        if ($templateData) {
            $subject = $templateData['description']['subject'];
            $message = $templateData['description']['message'];
            $verificationUrl = url('verify/' . $hash);
            $link = '<a href="' . $verificationUrl . '">Click here to verify your email address</a>';
            $message = str_replace(['{firstname}', '{link}', '{WEBSITE_URL}'], [$applicant_name, $link, $footerText], $message);
            $logId = EmailLog::EmailLog($email,$subject,$message,"Pending");
            $emailQueueId = EmailQueue::queueEmail(
                $email,
                $subject,
                $message,
                null,
                $logId
            );            
            
            return true;
        }
        return false;
    }
}

?>