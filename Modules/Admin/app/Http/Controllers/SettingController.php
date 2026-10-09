<?php


namespace Modules\Admin\Http\Controllers;


use App\Http\Controllers\Controller;
use Modules\Admin\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    protected SettingService $service;

    public function __construct(SettingService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all settings.
     */
    // public function index(): JsonResponse
    // {
    //     $settings = $this->service->getAll();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Settings fetched successfully.',
    //         'data' => $settings,
    //     ]);
    // }

    /**
     * Get one setting.
     */
    // public function show(string $key): JsonResponse
    // {
    //     $value = $this->service->get($key);

    //     if ($value === null) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Setting not found.',
    //             'data' => null,
    //         ], 404);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Setting fetched successfully.',
    //         'data' => [
    //             'config_key' => $key,
    //             'config_value' => $value,
    //         ],
    //     ]);
    // }

    /**
     * Update settings.
     */
    // public function update(Request $request): JsonResponse
    // {
    //     $this->validateRequest($request);

    //     $data = $request->except([
    //         '_token',
    //         '_method',
    //     ]);

    //     $settings = $this->service->update($data);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Settings saved successfully.',
    //         'data' => $settings,
    //     ]);
    // }

    // /**
    //  * Update a single setting.
    //  */
    // public function updateSingle(
    //     Request $request,
    //     string $key
    // ): JsonResponse {

    //     $value = $request->input('value');

    //     if ($value === null) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Value is required.',
    //         ], 422);
    //     }

    //     $settings = $this->service->update([
    //         $key => $value,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Setting updated successfully.',
    //         'data' => [
    //             'config_key' => $key,
    //             'config_value' => $settings[$key] ?? $value,
    //         ],
    //     ]);
    // }

    /**
     * Delete setting.
     */
    // public function destroy(string $key): JsonResponse
    // {
    //     $deleted = $this->service->delete($key);

    //     if (!$deleted) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Setting not found.',
    //         ], 404);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Setting deleted successfully.',
    //     ]);
    // }


     /**
     * Get General Settings
     */
    public function general()
    {
        return $this->getGeneral();
    }

    public function getGeneral()
    {
        try {

            $data = $this->service->getGeneral();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update General Settings
     */
    public function updateGeneral(Request $request)
    {
        try {

            $data = $request->validate([
                'website_name'           => ['required', 'string', 'max:150'],
                'website_url'            => ['nullable', 'url', 'max:255'],
                'website_status'         => ['required', 'in:live,maintenance,offline,1,2,3'],
                'contact_support_email'  => ['nullable', 'email', 'max:255'],
                'website_support_email'  => ['nullable', 'email', 'max:255'],
                'site_icon'              => ['nullable'],
                'website_icon'           => ['nullable'],
                'default_language'       => ['nullable', 'string', 'max:10'],
                'website_language'       => ['nullable', 'string', 'max:10'],
                'facebook'               => ['nullable', 'string', 'max:255'],
                'website_facebook'       => ['nullable', 'string', 'max:255'],
                'twitter'                => ['nullable', 'string', 'max:255'],
                'website_twitter'        => ['nullable', 'string', 'max:255'],
                'youtube'                => ['nullable', 'string', 'max:255'],
                'website_youtube'        => ['nullable', 'string', 'max:255'],
                'instagram'              => ['nullable', 'string', 'max:255'],
                'website_instagram'      => ['nullable', 'string', 'max:255'],
                'linkedin'               => ['nullable', 'string', 'max:255'],
                'website_linkedin'       => ['nullable', 'string', 'max:255'],
                'twitter_section_widget' => ['nullable', 'string'],
                'website_twitter_widget' => ['nullable', 'string'],
                'records_per_page'       => ['nullable', 'integer', 'min:1'],
                'website_pagination'     => ['nullable', 'integer', 'min:1'],
                'captcha_validation'     => ['nullable', 'in:enabled,disabled,1,0'],
                'adjust_file_size'       => ['nullable'],
            ]);

            $result = $this->service->updateGeneral($data);

            return response()->json([
                'success' => true,
                'message' => 'General settings updated successfully.',
                'data' => $result
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            throw $e;

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }





    /**
     * Validation.
     */
    protected function validateRequest(Request $request): void
    {
        $request->validate([
            'website_name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'website_url' => [
                'sometimes',
                'required',
                'string',
                'url',
                'max:255',
            ],

            'website_status' => [
                'sometimes',
                'nullable',
                'string',
                'in:live,maintenance,offline',
            ],

            'contact_support_email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
            ],

            'website_support_email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
            ],

            'website_icon' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp',
                'max:120',
            ],

            'website_logo' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp',
                'max:325',
            ],

            'website_logo_pb' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp',
                'max:325',
            ],

            'android_icon' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp',
                'max:120',
            ],

            'ios_icon' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp',
                'max:120',
            ],

            'qr_code' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp',
                'max:120',
            ],
        ]);
    }
}