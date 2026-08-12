<?php

namespace Modules\Admin\Requests\Others;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\CommonRules;
use App\Validation\Rules\SecurityRules;
use App\Helpers\RSAHelper;
use App\Validation\Patterns\RegexPatterns;
use Illuminate\Support\Facades\Crypt;
use App\Validation\Rules\FileRules;

class StorePageRequest extends BaseRequest
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
        $rules = [];


        $rules['slug'] = ['required','string','unique:pages,slug'];

        $rules['title'] = ['required','array'];

        $rules['title.*'] = ['required','string'];

        $rules['description'] = ['required','array'];

        $rules['description.*'] = ['required','string'];

        $rules['page_banner'] = FileRules::image(5,['jpg', 'jpeg', 'png', 'webp'], 1366, 350, false);

        return $rules;
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM VALIDATION MESSAGES
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [

            'slug.required' => 'Slug is required.',

            'slug.unique' => 'This slug already exists.',

            'title.required' => 'Title is required.',

            'title.array' => 'Title must be provided in the correct format.',

            'title.*.required' => 'Title is required.',

            'title.*.string' => 'Title must be a valid string.',

            'description.required' => 'Description is required.',

            'description.array' => 'Description must be provided in the correct format.',

            'description.*.required' => 'Description is required.',

            'description.*.string' => 'Description must be a valid string.',

            'page_banner.mimes' => 'Page banner must be a JPEG, PNG, or JPG image.',

            'page_banner.max' => 'Page banner must not exceed 5 MB.',

            'page_banner.dimensions' => 'Page banner dimensions must be exactly 1366 x 350 pixels.',
        ];
    }
}
