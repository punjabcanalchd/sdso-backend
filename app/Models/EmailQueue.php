<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable;

/**
 * Email Queue
 *
 * @mixin Builder
 */
class EmailQueue extends Model
{
    use HasFactory;
    //use Loggable;

    protected $fillable = [
        'email_id',
        'subject',
        'message',
        'email_response',
        'sent',
        'file_path',
        'log_id',
        'bcc_emails',
        'cc_emails',
    ];

    protected $casts = [
        'bcc_emails' => 'array',
        'cc_emails'  => 'array',
        'sent'       => 'boolean',
    ];

    /**
     * Add email to queue.
     */
    public static function queueEmail(
        string $emailId,
        string $subject,
        string $message,
        ?string $filePath = null,
        ?int $logId = null,
        ?array $bccEmails = null,
        ?array $ccEmails = null
    ): ?int {
        $email = self::create([
            'email_id'   => $emailId,
            'subject'    => $subject,
            'message'    => $message,
            'file_path'  => $filePath,
            'log_id'     => $logId,
            'bcc_emails' => $bccEmails,
            'cc_emails'  => $ccEmails,
            'sent'       => false,
        ]);

        return $email?->id;
    }
}