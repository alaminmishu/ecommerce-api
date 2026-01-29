<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UploadProductImagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:5'], // Max 5 images
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'], // Max 5MB per image
            'is_primary' => ['nullable', 'integer', 'min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            'images.required' => 'Please upload at least one image.',
            'images.array' => 'Images must be an array of files.',
            'images.min' => 'Please upload at least one image.',
            'images.max' => 'You can upload a maximum of 5 images.',
            'images.*.file' => 'Each image must be a valid file.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be of type: jpeg, png, jpg, gif, svg.',
            'images.*.max' => 'Each image must not exceed 5MB in size.',
            'is_primary.integer' => 'The primary image index must be an integer.',
            'is_primary.min' => 'The primary image index must be at least 0.',
        ];
    }

    /**
     * Get body parameters for API documentation
     */
    public function bodyParameters(): array
    {
        return [
            'images' => [
                'description' => 'Array of image files to upload (max 5 images, 5MB each)',
                'example' => 'image1.jpg, image2.png',
            ],
            'images.*' => [
                'description' => 'Individual image file (jpeg, jpg, png, webp)',
                'example' => 'product-image.jpg',
            ],
            'is_primary' => [
                'description' => 'Index of the primary image (0-based)',
                'example' => 0,
            ],
        ];
    }
}
