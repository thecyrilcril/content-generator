<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GenerateArticleRequest extends FormRequest
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
            'prompt' => ['required', 'string'],
            // 'keyword' => ['required', 'string'],
            // 'keyword_density' => ['required', 'numeric'],
            'author_style' => ['required', Rule::in(\App\Enums\AuthorStyle::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'prompt.required' => 'Please enter your prompt',
            'author_style.required' => 'Choose an Author style or tone',
        ];
    }
}
