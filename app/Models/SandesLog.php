<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 

/**
 * Sandes log
 *
 * @mixin Builder
 */
class SandesLog extends Model
{
    //use Loggable; //for creating log
    use HasFactory;
    
    /**
        * Remove the specified resource from storage.
        *
        * @param  int  $id
        * @return \Illuminate\Http\RedirectResponse
    */ 
    protected $fillable = [    
        'sandes_no',
        'purpose',
        'message',
        'otp',
        'is_used',
        'status',
        'response',
    ]; 


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public static function createSandesLog($sandes_no,$purpose,$message,$response,$otp = null,$is_used = null){
        $model              =   new SandesLog();
        $model->sandes_no   =   $sandes_no; 
        $model->purpose     =   $purpose; 
        $model->message     =   $message; 
        $model->otp         =   $otp; 
        $model->is_used     =   $is_used; 
        if (isset($response['success'])) {
            $model->status     =   "success"; 
            $model->response = json_encode($response);
        } else {
            $model->status     =   "failed"; 
            $model->response = json_encode($response);
        }
        
        if ($model->save()) {
            return true;
        } else {
            return false;
        }
    }
    public static function SandesLog($sandes_data){
        $model              =   new SandesLog();
        $model->sandes_no   =   $sandes_data['number']; 
        $model->purpose     =   $sandes_data['purpose']; 
        $model->message     =   $sandes_data['message']; 
        $model->status      =   $sandes_data['status'];
        $model->response    =   $sandes_data['response'];

        if ($model->save()) {
            return true;
        } else {
            return false;
        }
    }
}