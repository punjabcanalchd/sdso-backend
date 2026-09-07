<?php

namespace Modules\Admin\Requests\Others;

use App\Http\Requests\BaseRequest;
use App\Validation\Rules\FileRules;

class StorePageRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // Page
            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:pages,slug',
            ],

            'status' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'page_type' => [
                'required',
                'integer',
            ],

            'external_url' => [
                'nullable',
                'string',
                'max:255',
            ],

            // General translations
            'title' => [
                'required',
                'array',
            ],

            'title.*' => [
                'required',
                'string',
            ],

            'description' => [
                'required',
                'array',
            ],

            'description.*' => [
                'required',
                'string',
            ],

            // Meta translations
            'meta_title' => [
                'nullable',
                'array',
            ],

            'meta_title.*' => [
                'nullable',
                'string',
            ],

            'meta_description' => [
                'nullable',
                'array',
            ],

            'meta_description.*' => [
                'nullable',
                'string',
            ],

            'meta_keyword' => [
                'nullable',
                'array',
            ],

            'meta_keyword.*' => [
                'nullable',
                'string',
            ],

            // // Punjabi same as English
            // 'same_as_english_pb' => [
            //     'nullable',
            //     'boolean',
            // ],

            // Banner
            'page_banner' => FileRules::image(
                5,
                ['jpg', 'jpeg', 'png', 'webp'],
                1366,
                350,
                false
            ),
        ];
    }

    public function messages(): array
    {
        return [

            'slug.required' => 'Slug is required.',
            'slug.unique' => 'This slug already exists.',

            'status.required' => 'Status is required.',
            'status.boolean' => 'Status must be valid.',

            'sort_order.required' => 'Sort order is required.',
            'sort_order.integer' => 'Sort order must be a number.',
            'sort_order.min' => 'Sort order cannot be negative.',

            'page_type.required' => 'Page type is required.',
            'page_type.integer' => 'Page type must be a valid number.',

            'title.required' => 'Title is required.',
            'title.array' => 'Title must be provided in the correct format.',
            'title.*.required' => 'Title is required.',
            'title.*.string' => 'Title must be a valid string.',

            'description.required' => 'Description is required.',
            'description.array' => 'Description must be provided in the correct format.',
            'description.*.required' => 'Description is required.',
            'description.*.string' => 'Description must be a valid string.',

            'meta_title.array' => 'Meta title must be provided in the correct format.',
            'meta_title.*.string' => 'Meta title must be a valid string.',

            'meta_description.array' => 'Meta description must be provided in the correct format.',
            'meta_description.*.string' => 'Meta description must be a valid string.',

            'meta_keyword.array' => 'Meta keyword must be provided in the correct format.',
            'meta_keyword.*.string' => 'Meta keyword must be a valid string.',

            'page_banner.mimes' => 'Page banner must be a JPEG, PNG, JPG, or WEBP image.',
            'page_banner.max' => 'Page banner must not exceed 5 MB.',
            'page_banner.dimensions' => 'Page banner dimensions must be exactly 1366 x 350 pixels.',
        ];
    }
}
