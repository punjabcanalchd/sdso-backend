<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, HasPublicId, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'role_id',
        'officelevelcode',
        'circle_id',
        'division_id',
        'subdivision_id',
        'mobile_number',
        'current_user_role',
        'current_circle_id',
        'current_division_id',
        'current_subdivision_id',
        'officecode',
        'current_officecode',
        'hrmscode',
        'retirementdate',
        'is_password_updated',
        'login_attempts',
        'locked_at',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    protected $hidden = [
        'id', // added to hide the actual ID in revamp
        'password',
        'remember_token',
        'apitoken',
        'mobile_password',
    ];

    /**
     * Main role relationship (belongsTo Role via role_id)
     */
    public function mainRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Additional role relationship (hasOne AdditionalRole)
     */
    public function additionalRole(): HasOne
    {
        return $this->hasOne(AdditionalRole::class);
    }

    protected $appends = [
        'public_id',
    ];

    public function scopeWherePublicId($query, string $publicId)
    {
        $id = Crypt::decryptString(
            urldecode($publicId)
        );

        return $query->where('id', $id);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role_id' => $this->role_id,
        ];
    }

    public function role()
    {
        return $this->hasOne(UserRole::class, 'role_id', 'role_id');
    }

    public static function passPhrase()
    {
        return '!@#$%12345!@#$%';
    }

    public static function cryptoJsAesEncrypt($passphrase, $value)
    {
        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx.$passphrase.$salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);
        $encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
        $data = ['ct' => base64_encode($encrypted_data), 'iv' => bin2hex($iv), 's' => bin2hex($salt)];

        return json_encode($data);
    }

    public static function cryptoJsAesDecrypt($passphrase, $jsonString)
    {
        $jsondata = json_decode($jsonString, true);

        if (isset($jsondata) && ! empty($jsondata) && ! is_int($jsondata)) {

            $salt = hex2bin($jsondata['s']);

            $ct = base64_decode($jsondata['ct']);

            $iv = hex2bin($jsondata['iv']);
            $concatedPassphrase = $passphrase.$salt;
            $md5 = [];
            $md5[0] = md5($concatedPassphrase, true);
            $result = $md5[0];
            for ($i = 1; $i < 3; $i++) {
                $md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
                $result .= $md5[$i];
            }
            $key = substr($result, 0, 32);
            $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);

            return json_decode($data, true);
        } else {
            return $jsonString;
        }
    }

    public function division()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
        return $this->hasOne(DivisionsDescription::class,'division_id','division_id')->where('language_id','=',default_language);
    }
    public function subdivision()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
        return $this->hasOne(SubDivisionsDescription::class,'subdivision_id','subdivision_id')->where('language_id','=',default_language);
    }
    public function office()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
        return $this->hasOne(OfficeDescription::class,'officecode','officecode')->where('language_id','=',default_language);
    }
    public function circle()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
        return $this->hasOne(CirclesDescription::class,'circle_id','circle_id')->where('language_id','=',default_language);
    }
    public function officelevel()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
        return $this->hasOne(OfficeHierarchiesDescription::class,'officelevelcode','officelevelcode')->where('language_id','=',default_language);
    }
}
