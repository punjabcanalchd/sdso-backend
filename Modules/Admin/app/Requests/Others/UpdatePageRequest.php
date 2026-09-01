<?php

namespace Modules\Admin\Requests\Others;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\FileRules;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION RULES
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        // Get page_id from route
        $publicId = $this->route('public_id');
        $pageId = 0;
        // echo '';
        dd($publicId);
        if ($publicId) {
            try {
                $pageId = (int) Crypt::decryptString(urldecode($publicId));
            } catch (\Exception $e) {
                $pageId = 0;
            }
        }

        return [
            'slug' => [
                // 'required',
                'string',
                'max:255',

                // Rule::unique('pages', 'slug')
                //     ->ignore($pageId, 'page_id'),
            ],

            'title' => ['required', 'array'],

            'title.*' => ['required', 'string'],

            'description' => ['required', 'array'],

            'description.*' => ['required', 'string'],

            'page_banner' => FileRules::image(5, ['jpg', 'jpeg', 'png', 'webp'], 1366, 350, false),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM VALIDATION MESSAGES
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [

            // 'slug.required' => 'Slug is required.',

            // 'slug.unique' => 'This slug already exists.',

            'title.required' => 'Title is required.',

            'title.array' => 'Title must be an array.',

            'title.*.required' => 'Title is required.',

            'title.*.string' => 'Title must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be an array.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',

            'page_banner.mimes' => 'Page banner must be a JPG, JPEG, or PNG image.',

            'page_banner.max' => 'Page banner must not exceed 5 MB.',

            'page_banner.dimensions' => 'Page banner dimensions must be exactly 1366 x 350 pixels.',
        ];
    }
}
