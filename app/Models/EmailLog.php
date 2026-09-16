<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;



/**
 * Email Log
 *
 * @mixin Builder
 */
class EmailLog extends Model
{
    use HasFactory, LogsActivity;
    protected $table =  'email_logs';
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */ 
    protected $fillable = [

    // Common
        'email_id',
        'purpose',
        'message',
        'hash',
        'is_used',
    ]; 

    public static function EmailLog($email_id,$subject,$message,$status,$response=null,$file=null,$hash=null){
        $model                  =   new EmailLog();
        $model->email_id        =   $email_id; 
        $model->purpose         =   $subject; 
        $model->message         =   $message; 
        $model->status          =   $status; 

        if(!is_null($hash) ) {
            $model->hash            =   $hash;
        }
        if(!is_null($response)){
            $model->email_responce  =   $response;
        }

        if(!is_null($file)){
            $model->file            =   $file; 
        }
        
        if($model->save()) {
            return $model->id;
        }else{
            return null;
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
?>