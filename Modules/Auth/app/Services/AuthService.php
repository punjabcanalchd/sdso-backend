<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PasswordHistory;
use App\Models\EmailTemplate;
use App\Models\SandesTemplate;
use App\Models\SmsTemplate;
use App\Models\SandesLog;
use App\Models\NotificationToken;
use App\Services\PushNotificationService;
use App\Services\SandesService;
use App\Helpers\RSAHelper;
use Modules\Admin\Services\AdminPagePermissionService;


class AuthService
{
    public function __construct(
        private PushNotificationService $pushNotificationService,
        private SandesService $sandesService
    ) {
    }
    /**
     * Login User
     */
    public function login(array $credentials): array
    {
        // Find user by email first
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Invalid credentials',
            ];
        }

        if ($user->locked) {
            return [
                'success' => false,
                'status' => 403,
                'message' => 'Account locked',
            ];
        }

        if (!$user->status) {
            return [
                'success' => false,
                'status' => 403,
                'message' => 'The user account is inactive. Please contact the support team.',
            ];
        }

        // Only attempt JWT auth after all checks pass
        $guard = Auth::guard('api');
        if (!$accessToken = $guard->attempt($credentials)) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Invalid credentials',
            ];
        }

        $user->load('role');

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Login successful',
            'data' => [
                'accessToken' => $accessToken,
                'tokenType' => 'Bearer',
                'expiresIn' => config('jwt.ttl') * 60,
                'cookiePath' => '/',
                'user' => [
                    'id' =>  $user->public_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobileNumber' => $user->mobile_number,
                    'roleId' => $user->role?->public_id,
                    'roleName' => $user->role?->name,
                    'roleSlug' => $user->role?->slug,
                    'currentUserRole' => $user->current_user_role,
                    'permissionMap' => app(AdminPagePermissionService::class)->getPermissionMapForUser($user),
                ],
            ]
        ];
    }

    /**
     * Get Authenticated User
     */
    public function user(): ?array
    {
        $user = auth('api')->user();
        if (!$user) {
            return null;
        }
        $user->load('role');
        return [
            'id' =>  $user->public_id,
            'name' => $user->name,
            'email' => $user->email,
            'mobileNumber' => $user->mobile_number,
            'roleId' => $user->role?->public_id,
            'roleName' => $user->role?->name,
            'roleSlug' => $user->role?->slug,
            'currentUserRole' => $user->current_user_role,
            'permissionMap' => app(AdminPagePermissionService::class)->getPermissionMapForUser($user),
        ];
    }

    /**
     * Refresh JWT Token
     */
    public function refresh(): array
    {
        try {
            $guard = auth('api');
            $newAccessToken = $guard->refresh();
            $user = $guard->user();
            $user?->load('role');
            return [
                'success' => true,
                'status' => 200,
                'message' => 'Token refreshed',
                'data' => [
                    'accessToken' => $newAccessToken,
                    'tokenType' => 'Bearer',
                    'expiresIn' => config('jwt.ttl') * 60,
                    'cookiePath' => '/' //. $user?->role?->slug, //temporarily set to root path for testing
                ]
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Session expired',
            ];
        }
    }

    /**
     * Logout User
     */
    public function logout(): bool
    {
        auth('api')->logout();
        return true;
    }
    
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        try {
            $passPhrase = User::passPhrase();
            $id_proof_number = $data['id_proof_number'];
            $password = $data['password'];
            $confirm_password = $data['confirm_password'];
            $data = array_merge($data, ['id_proof_number' => $id_proof_number, 'password' => $password, 'confirm_password' => $confirm_password]);
            // Create user
            $user = new User();
            $email = $data['email'];
            $user->mobile_number = $data['mobile_number'] ?? null;
            $user->applicant_type = $data['applicant_type'] ?? null;
            $user->name = $data['applicant_first_name'];
            $user->applicant_first_name = $data['applicant_first_name'] ?? null;
            $user->applicant_middle_name = $data['applicant_middle_name'] ?? null;
            $user->applicant_last_name = $data['applicant_last_name'] ?? null;
            $user->father_name = $data['father_name'] ?? null;
            $user->designation = $data['designation'] ?? null;
            $user->applicant_relation = $data['applicant_relation'] ?? 0;

			$encryptNumber = User::cryptoJsAesEncrypt($passPhrase,$data['id_proof_number']);
            $allIdProofNo = User::select('id_proof_number','email')->get()->toArray();
			$arrayDecrypted = 	$arrayEmail = [];

			foreach($allIdProofNo as $proof) {
				$id_proof_number_decrypted = User::cryptoJsAesDecrypt($passPhrase,$proof['id_proof_number']);
				$arrayDecrypted[$id_proof_number_decrypted] = $id_proof_number_decrypted;
				$arrayEmail[$id_proof_number_decrypted] = $proof['email'];
			}
            $check_field = in_array($id_proof_number,$arrayDecrypted);

			if($check_field) {
				$PreviousEmail  = $arrayEmail[$id_proof_number];
				$email = $this->encryptEmailStar($PreviousEmail);
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'This id proof '.$id_proof_number.' is already used for '.$email,
                    'data' => []
                ];				
			}

			$passwordHash = Hash::make($data['password']);
	        $user->id_proof_number  = 	$encryptNumber;
	        $user->id_proof 	    = 	$data['id_proof'];
            $user->status 			= 	0;
	        $user->password 		= 	$passwordHash;
            $user->email            = strtolower($email);
	        $user->password_updated_at = now();
            if(isset($data['upload_copy_of_id_proof'])  && !empty($data['upload_copy_of_id_proof'])) {
	            $filename 	= 	time().'.'.$data['upload_copy_of_id_proof']->getClientOriginalExtension();
	            $data['upload_copy_of_id_proof']->move(public_path('attachments/copy_of_id_proof/'),$filename);
	            $user->upload_copy_of_id_proof	= 	$filename;
	        }
            if ($user->save()) {
                PasswordHistory::create([
                    'user_id' => $user->id,
                    'password' => $passwordHash,

                ]);
	            $encrypted_id = md5($user->id).time();
	            $user->verificationHash = $encrypted_id;
                $email_data['applicant_name'] 	= 	$user->name;
                $email_data['hash'] = 	$encrypted_id;
                $email_data['email'] = 	$user->email;
                $user->save();

                EmailTemplate::activationMail($email_data);
                $SMS_data['user_mobile'] 	= 	$user->mobile_number;
                $SMS_data['user_name'] 		= 	$user->applicant_first_name;
                $request_uri = $_SERVER['SERVER_ADDR'] ?? '';
                if(strstr($request_uri,'pwrda.punjab.gov.in') || strstr($request_uri,'10.43.250.89')) {
                    SmsTemplate::ApplicantRegistration($SMS_data);
                }
                return [
                    'success' => true,
                    'status' => 201,
                    'message' => 'Registration successful. Please check your email to activate your account.',
                    'data' => []
                ];
            } else {
                return [
                    'success' => false,
                    'status' => 500,
                    'message' => 'Failed to create user',
                    'data' => []
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Registration failed: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    function encryptEmailStar($em) {
		$stars = 4; // Min Stars to use
		$at = strpos($em,'@');
		if($at - 2 > $stars) $stars = $at - 2;
		return substr($em,0,1) . str_repeat('*',$stars) . substr($em,$at - 1);
	}

    public function forgotPasswordEmail(string $email): array
    {
        $email = strtolower($email);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Email not registered.',
            ];
        }

        $hash = base64_encode($user->id . time());

        $user->forgot_password_hash = $hash;
        $user->save();

        // Sandes Message
        $verificationUrl = url(
            'auth/forgot-password-change/' . $hash
        );
        $link = '<a href="' . $verificationUrl . '">
                Click here to change your password
            </a>';

        $sandesResult = $this->sandesService
            ->sendForgotPasswordMessage(
                $user->mobile_number,
                $link
            );      
        

        $this->pushNotificationService->sendNotification(
            $user->id,
            $sandesResult['message']
        );
           
        $email_data['applicant_name'] 	= 	$user->name;
        $email_data['hash'] 			= 	$user->forgot_password_hash;
        $email_data['email'] 			= 	$user->email;
        EmailTemplate::forgotPasswordMail($email_data);

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Password reset instructions have been sent.',
        ];
    }

    public function validateForgotPasswordHash(string $hash): array
    {
        $captchaValidation = false;

        if (defined('captcha_validation')) {
            $captchaValidation = captcha_validation;
        }

        $user = User::where('forgot_password_hash', $hash)->first();

        if (!$user) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Not a valid request.',
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Valid request.',
            'data' => [
                'hash' => $hash,
                'captcha_validation' => $captchaValidation,
            ],
        ];
    }

    public function changeForgotPassword(
        string $hash,
        string $password,
        string $confirmPassword
    ): array {

        if ($password !== $confirmPassword) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Password and confirm password do not match.',
            ];
        }

        $user = User::where('forgot_password_hash', $hash)->first();

        if (!$user) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Not a valid request.',
            ];
        }

        $lastThreePasswords = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('changed_at')
            ->limit(3)
            ->pluck('password');

        foreach ($lastThreePasswords as $oldPassword) {

            $passwordValue = is_object($oldPassword)
                ? $oldPassword->password
                : $oldPassword;

            if (Hash::check($password, $passwordValue)) {

                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'You cannot use any of your last three passwords.',
                ];
            }
        }

        $hashedPassword = Hash::make($password);

        if ($user->is_ip_caf_user) {
            $user->mobile_password = $hashedPassword;
        } else {
            $user->password = $hashedPassword;
        }

        $user->forgot_password_hash = null;
        $user->password_updated_at = now();

        if (!$user->save()) {

            return [
                'success' => false,
                'status' => 500,
                'message' => 'There was some issue please try again.',
            ];
        }

        // Sandes Message
        $this->sandesService->sendPasswordChangedMessage(
            $user->mobile_number
        );        

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Password changed successfully. Please login.',
        ];
    }

    public function validateVerificationHash(string $hash): array
    {
        $user = User::where('verificationHash', $hash)->where('status', 0)->first();

        if (!$user) {
            return [
                'success' => false,
                'status' => 404,
                'message' => 'Invalid or expired verification link.',
                'data' => []
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Valid verification link.',
            'data' => ['hash' => $hash]
        ];
    }

    public function verifyAccount(string $hash): array
    {
        try {
            $user = User::where('verificationHash', $hash)
                ->where('status', 0)
                ->first();

            if (!$user) {
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Invalid or expired verification link.',
                    'data' => []
                ];
            }

            $user->status = 1;
            $user->verificationHash = null; // prevent reuse

            if ($user->save()) {
                return [
                    'success' => true,
                    'status' => 200,
                    'message' => 'Account verified successfully.',
                    'data' => []
                ];
            }

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Failed to verify account.',
                'data' => []
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Verification failed: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function updateProfile(array $data): array
    {
        try {
            $user = Auth::user();

            $updateData = [
                'name' => $data['first_name'] ?? $user->applicant_first_name,
                'applicant_first_name' => $data['first_name'] ?? $user->applicant_first_name,
                'applicant_middle_name' => $data['middle_name'] ?? $user->applicant_middle_name,
                'applicant_last_name' => $data['last_name'] ?? $user->applicant_last_name,
            ];

            if (isset($data['mobile_number'])) {
                $updateData['mobile_number'] = $data['mobile_number'];
            }

            $user->update($updateData);

            // Consume OTP verification after a successful phone update
            if (isset($data['mobile_number'])) {
                Cache::forget('otp_verified:' . $user->id);
            }

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Profile updated successfully',
                'data' => [
                    'first_name' => $user->applicant_first_name,
                    'middle_name' => $user->applicant_middle_name,
                    'last_name' => $user->applicant_last_name,
                    'mobile_number' => $user->mobile_number,
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Profile update failed: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function getProfile(): array
    {
        $user = Auth::user();

        $idProofTypeMap = [
            1 => 'PAN Card',
            2 => 'Driving License',
        ];

        $idProofType = $user->id_proof;
        if (is_numeric($idProofType) && isset($idProofTypeMap[$idProofType])) {
            $idProofType = $idProofTypeMap[$idProofType];
        }

        $idProofNumber = $user->id_proof_number;
        if (!empty($idProofNumber)) {
            $passPhrase = User::passPhrase();
            $decryptedNumber = User::cryptoJsAesDecrypt($passPhrase, $idProofNumber);
               
            $length = strlen($decryptedNumber);
            if ($length > 4) {
                $idProofNumber = str_repeat('X', $length - 4) . substr($decryptedNumber, -4);
            } else {
                $idProofNumber = $decryptedNumber;
            }
        }

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Profile fetched successfully',
            'data' => [
                'applicant_type' => $user->applicant_type,
                'first_name' => $user->applicant_first_name,
                'middle_name' => $user->applicant_middle_name,
                'last_name' => $user->applicant_last_name,
                'email' => $user->email,
                'mobile_number' => $user->mobile_number,
                'designation' => $user->designation,
                'id_proof_type' => $idProofType,
                'id_proof_number' => $idProofNumber,
            ]
        ];
    }

    public function changePassword(string $oldPassword, string $newPassword): array
    {
        try {
            $user = Auth::user();

            if (!Hash::check($oldPassword, $user->password)) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'The old password you entered is incorrect.',
                    'data' => []
                ];
            }

            $lastThreePasswords = PasswordHistory::query()
                ->where('user_id', $user->id)
                ->latest('changed_at')
                ->limit(3)
                ->pluck('password');

            foreach ($lastThreePasswords as $historyPassword) {
                $passwordValue = is_object($historyPassword)
                    ? $historyPassword->password
                    : $historyPassword;

                if (Hash::check($newPassword, $passwordValue)) {
                    return [
                        'success' => false,
                        'status' => 422,
                        'message' => 'You cannot use any of your last three passwords.',
                        'data' => []
                    ];
                }
            }

            $hashedPassword = Hash::make($newPassword);

            if ($user->is_ip_caf_user) {
                $user->mobile_password = $hashedPassword;
            } else {
                $user->password = $hashedPassword;
            }

            $user->save();

            PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $hashedPassword,
                'changed_at' => now(),
            ]);

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Password changed successfully',
                'data' => []
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Failed to change password: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

}