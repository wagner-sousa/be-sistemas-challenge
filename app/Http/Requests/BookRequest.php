<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
        $bookId = $this->route('book')?->id ?? $this->route('book');

        $requiredRule = $isUpdate ? ['sometimes', 'required'] : ['required'];

        return [
            'title' => [...$requiredRule, 'string', 'max:255'],
            'author_name' => [...$requiredRule, 'string', 'max:255'],
            'isbn_code' => [
                ...$requiredRule,
                'string',
                'size:13',
                Rule::unique('books', 'isbn_code')->ignore($bookId),
            ],
            'total_quantity' => [...$requiredRule, 'integer', 'min:1'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
