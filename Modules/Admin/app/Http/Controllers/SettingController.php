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
     * Get Layout Settings
     */
    public function layout()
    {
        return $this->getLayout();
    }

    public function getLayout()
    {
        try {
            $data = $this->service->getLayout();

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
     * Update Layout Settings
     */
    public function updateLayout(Request $request)
    {
        try {
            $imageValidator = function ($attribute, $value, $fail) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['jpeg', 'png', 'jpg', 'webp', 'gif'], true)) {
                        $fail("The {$attribute} must be a valid image file (jpeg, png, jpg, webp, gif).");
                    }
                    if ($value->getSize() > 2048 * 1024) {
                        $fail("The {$attribute} must not exceed 2MB.");
                    }
                }
            };

            $data = $request->validate([
                'menus'                             => ['nullable'],
                'website_menus'                     => ['nullable'],
                'home_page_slider'                  => ['nullable'],
                'home_slider'                       => ['nullable'],
                'home_page_slider_timing'           => ['nullable', 'numeric', 'min:500', 'max:60000'],
                'slider_timing'                     => ['nullable', 'numeric', 'min:500', 'max:60000'],
                'footer_slider'                     => ['nullable'],
                'home_page_link_slider'             => ['nullable'],
                'home_link_slider'                  => ['nullable'],
                'home_page_other_links_slider'      => ['nullable'],
                'home_other_slider'                 => ['nullable'],
                'home_page_top_footer_links_slider' => ['nullable'],
                'home_top_footer_slider'            => ['nullable'],
                'index_text_page'                   => ['nullable'],
                'website_index_text_page_id'        => ['nullable'],
                'screen_reader_page'                => ['nullable'],
                'website_sc_reader_page_id'         => ['nullable'],
                'default_page_banner'               => ['nullable', $imageValidator],
                'website_default_banner'            => ['nullable', $imageValidator],
                'search_page_banner'                => ['nullable', $imageValidator],
                'website_search_banner'             => ['nullable', $imageValidator],
                'punjab_map'                        => ['nullable', $imageValidator],
            ]);

            $result = $this->service->updateLayout($data);

            return response()->json([
                'success' => true,
                'message' => 'Layout settings updated successfully.',
                'data' => $result
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Update Layout Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update layout settings.'
            ], 500);
        }
    }

    /**
     * Get Footer Settings
     */
    public function footer()
    {
        return $this->getFooter();
    }

    public function getFooter()
    {
        try {
            $data = $this->service->getFooter();

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
     * Update Footer Settings
     */
    public function updateFooter(Request $request)
    {
        try {
            $data = $request->validate([
                'footer_category_1_title_en' => ['nullable', 'string', 'max:255'],
                'footer_cat1_title'          => ['nullable', 'string', 'max:255'],
                'footer_category_1_title_pb' => ['nullable', 'string', 'max:255'],
                'footer_cat1_title_pb'       => ['nullable', 'string', 'max:255'],
                'footer_category_1_menus'    => ['nullable'],
                'footer_cat1_menus'          => ['nullable'],

                'footer_category_2_title_en' => ['nullable', 'string', 'max:255'],
                'footer_cat2_title'          => ['nullable', 'string', 'max:255'],
                'footer_category_2_title_pb' => ['nullable', 'string', 'max:255'],
                'footer_cat2_title_pb'       => ['nullable', 'string', 'max:255'],
                'footer_category_2_menus'    => ['nullable'],
                'footer_cat2_menus'          => ['nullable'],

                'footer_category_3_title_en' => ['nullable', 'string', 'max:255'],
                'footer_cat3_title'          => ['nullable', 'string', 'max:255'],
                'footer_category_3_title_pb' => ['nullable', 'string', 'max:255'],
                'footer_cat3_title_pb'       => ['nullable', 'string', 'max:255'],
                'footer_category_3_menus'    => ['nullable'],
                'footer_cat3_menus'          => ['nullable'],

                'copyright_heading_1_en'     => ['nullable', 'string', 'max:500'],
                'website_copyright1'         => ['nullable', 'string', 'max:500'],
                'copyright_heading_1_pb'     => ['nullable', 'string', 'max:500'],
                'website_copyright1_pb'      => ['nullable', 'string', 'max:500'],

                'copyright_heading_2_en'     => ['nullable', 'string', 'max:500'],
                'website_copyright2'         => ['nullable', 'string', 'max:500'],
                'copyright_heading_2_pb'     => ['nullable', 'string', 'max:500'],
                'website_copyright2_pb'      => ['nullable', 'string', 'max:500'],
            ]);

            $result = $this->service->updateFooter($data);

            return response()->json([
                'success' => true,
                'message' => 'Footer settings updated successfully.',
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
     * Get Manage Section Settings
     */
    public function manageSection()
    {
        return $this->getManageSection();
    }

    public function getManageSection()
    {
        try {
            $data = $this->service->getManageSection();

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
     * Update Manage Section Settings
     */
    public function updateManageSection(Request $request)
    {
        try {
            $data = $request->validate([
                'section_1_status'    => ['nullable'],
                'home_page_section_1' => ['nullable'],
                'section_2_status'    => ['nullable'],
                'home_page_section_2' => ['nullable'],
                'section_3_status'    => ['nullable'],
                'home_page_section_3' => ['nullable'],
                'section_4_status'    => ['nullable'],
                'home_page_section_4' => ['nullable'],
                'section_5_status'    => ['nullable'],
                'home_page_section_5' => ['nullable'],
                'section_6_status'    => ['nullable'],
                'home_page_section_6' => ['nullable'],
                'section_7_status'    => ['nullable'],
                'home_page_section_7' => ['nullable'],
            ]);

            $result = $this->service->updateManageSection($data);

            return response()->json([
                'success' => true,
                'message' => 'Manage section settings updated successfully.',
                'data' => $result
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Manage Section Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process manage section settings.'
            ], 500);
        }
    }

    /**
     * Get Logo Settings
     */
    public function logo(): JsonResponse
    {
        return $this->getLogo();
    }

    public function getLogo(): JsonResponse
    {
        try {
            $data = $this->service->getLogo();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Get Logo Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve logo settings.'
            ], 500);
        }
    }

    /**
     * Update Logo Settings
     */
    public function updateLogo(Request $request): JsonResponse
    {
        try {
            $imageValidator = function ($attribute, $value, $fail) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['jpeg', 'png', 'jpg', 'webp', 'gif'], true)) {
                        $fail("The {$attribute} must be a valid image file (jpeg, png, jpg, webp, gif).");
                    }
                    if ($value->getSize() > 2048 * 1024) {
                        $fail("The {$attribute} must not exceed 2MB.");
                    }
                }
            };

            $data = $request->validate([
                'logo_english'                       => ['nullable', $imageValidator],
                'website_logo'                        => ['nullable', $imageValidator],
                'logo_punjabi'                        => ['nullable', $imageValidator],
                'website_logo_pb'                     => ['nullable', $imageValidator],
                'secondary_logo_english'              => ['nullable', $imageValidator],
                'secondary_logo_punjabi'              => ['nullable', $imageValidator],
                'logo_heading_en'                     => ['nullable', 'string', 'max:255'],
                'website_logo_heading_en'             => ['nullable', 'string', 'max:255'],
                'logo_heading_pb'                     => ['nullable', 'string', 'max:255'],
                'website_logo_heading_pb'             => ['nullable', 'string', 'max:255'],
                'logo_sub_heading_en'                 => ['nullable', 'string', 'max:500'],
                'website_logo_sub_heading_en'         => ['nullable', 'string', 'max:500'],
                'logo_sub_heading_pb'                 => ['nullable', 'string', 'max:500'],
                'website_logo_sub_heading_pb'         => ['nullable', 'string', 'max:500'],
                'toogle_status'                       => ['nullable'],
                'government_officials_toggle'         => ['nullable'],
                'chief_minister_logo'                 => ['nullable', $imageValidator],
                'cabinet_minister_logo'               => ['nullable', $imageValidator],
                'chief_minister_logo_heading_en'      => ['nullable', 'string', 'max:255'],
                'chief_minister_logo_heading_pb'      => ['nullable', 'string', 'max:255'],
                'chief_minister_logo_sub_heading_en'  => ['nullable', 'string', 'max:500'],
                'chief_minister_logo_sub_heading_pb'  => ['nullable', 'string', 'max:500'],
                'cabinet_minister_logo_heading_en'    => ['nullable', 'string', 'max:255'],
                'cabinet_minister_logo_heading_pb'    => ['nullable', 'string', 'max:255'],
                'cabinet_minister_logo_sub_heading_en'=> ['nullable', 'string', 'max:500'],
                'cabinet_minister_logo_sub_heading_pb'=> ['nullable', 'string', 'max:500'],
            ]);

            $result = $this->service->updateLogo($data);

            return response()->json([
                'success' => true,
                'message' => 'Logo settings updated successfully.',
                'data' => $result
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Update Logo Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update logo settings.'
            ], 500);
        }
    }

    /**
     * Get Contact Us Settings
     */
    public function contactUs(): JsonResponse
    {
        return $this->getContactUs();
    }

    public function getContactUs(): JsonResponse
    {
        try {
            $data = $this->service->getContactUs();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Get Contact Us Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve contact us settings.'
            ], 500);
        }
    }

    /**
     * Update Contact Us Settings
     */
    public function updateContactUs(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'contact_title_en'    => ['nullable', 'string', 'max:255'],
                'title_en'            => ['nullable', 'string', 'max:255'],
                'contact_title_pb'    => ['nullable', 'string', 'max:255'],
                'title_pb'            => ['nullable', 'string', 'max:255'],
                'contact_phone'       => ['nullable', 'string', 'max:30'],
                'phone_number'        => ['nullable', 'string', 'max:30'],
                'contact_address_en'  => ['nullable', 'string', 'max:1000'],
                'address_en'          => ['nullable', 'string', 'max:1000'],
                'contact_address_pb'  => ['nullable', 'string', 'max:1000'],
                'address_pb'          => ['nullable', 'string', 'max:1000'],
                'map_token'           => ['nullable', 'string', 'max:500'],
                'token_key'           => ['nullable', 'string', 'max:500'],
                'map_url'             => ['nullable', 'url', 'max:500'],
                'map_api_key'         => ['nullable', 'string', 'max:500'],
                'map_api'             => ['nullable', 'string', 'max:500'],
                'block_service_url'   => ['nullable', 'url', 'max:500'],
                'block_service'       => ['nullable', 'url', 'max:500'],
                'block_service_token' => ['nullable', 'string', 'max:500'],
                'block_token'         => ['nullable', 'string', 'max:500'],
                'latitude'            => ['nullable', 'numeric', 'between:-90,90'],
                'longitude'           => ['nullable', 'numeric', 'between:-180,180'],
            ]);

            $result = $this->service->updateContactUs($data);

            return response()->json([
                'success' => true,
                'message' => 'Contact Us settings updated successfully.',
                'data' => $result
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Update Contact Us Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact us settings.'
            ], 500);
        }
    }

    /**
     * Get Meta Information Settings
     */
    public function metaInformation(): JsonResponse
    {
        return $this->getMetaInformation();
    }

    public function getMetaInformation(): JsonResponse
    {
        try {
            $data = $this->service->getMetaInformation();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Get Meta Information Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meta information settings.'
            ], 500);
        }
    }

    /**
     * Update Meta Information Settings
     */
    public function updateMetaInformation(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'meta_title_en'               => ['nullable', 'string', 'max:255'],
                'website_meta_title'          => ['nullable', 'string', 'max:255'],
                'meta_title_pb'               => ['nullable', 'string', 'max:255'],
                'website_meta_title_pb'       => ['nullable', 'string', 'max:255'],
                'meta_description_en'         => ['nullable', 'string', 'max:1000'],
                'website_meta_description'    => ['nullable', 'string', 'max:1000'],
                'meta_description_pb'         => ['nullable', 'string', 'max:1000'],
                'website_meta_description_pb' => ['nullable', 'string', 'max:1000'],
                'meta_keyword_en'             => ['nullable', 'string', 'max:1000'],
                'website_meta_keywords'       => ['nullable', 'string', 'max:1000'],
                'meta_keyword_pb'             => ['nullable', 'string', 'max:1000'],
                'website_meta_keywords_pb'    => ['nullable', 'string', 'max:1000'],
            ]);

            $result = $this->service->updateMetaInformation($data);

            return response()->json([
                'success' => true,
                'message' => 'Meta Information settings updated successfully.',
                'data' => $result
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Update Meta Information Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update meta information settings.'
            ], 500);
        }
    }

    /**
     * Get Additional Settings
     */
    public function additionalSettings(): JsonResponse
    {
        return $this->getAdditionalSettings();
    }

    public function getAdditionalSettings(): JsonResponse
    {
        try {
            $data = $this->service->getAdditionalSettings();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Get Additional Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve additional settings.'
            ], 500);
        }
    }

    /**
     * Update Additional Settings
     */
    public function updateAdditionalSettings(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'ln_bg_color'    => ['required', 'string', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
                'ln_txt_color'  => ['required', 'string', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
                'file_size_status' => ['required'],
            ]);

            $result = $this->service->updateAdditionalSettings($data);
            

            return response()->json([
                'success' => true,
                'message' => 'Additional settings updated successfully.',
                'data' => $result
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Update Additional Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update additional settings.'
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