<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class AclUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $secret = config('acl.internal_secret');

        if (! is_string($secret) || $secret === '') {
            return ! app()->isProduction();
        }

        $providedSecret = $this->header('X-Internal-Secret', '');

        return is_string($providedSecret) && hash_equals($secret, $providedSecret);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    public function email(): string
    {
        return Str::lower($this->string('email')->trim()->toString());
    }
}
