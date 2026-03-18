<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BorrowBooksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('book_id') && ! $this->has('books')) {
            $this->merge([
                'books' => [$this->input('book_id')],
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'books' => ['required', 'array', 'min:1'],
            'books.*' => ['integer', 'distinct', 'exists:books,id'],
        ];
    }
}
