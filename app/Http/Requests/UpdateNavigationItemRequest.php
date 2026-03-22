<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNavigationItemRequest extends FormRequest
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
            'label' => 'sometimes|required|string|max:255',
            'route' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:255',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'integer',
            'parent_id' => 'nullable|exists:navigation_items,id',
            'is_active' => 'boolean',
            'group' => 'sometimes|required|string|max:50',
            'permission' => 'nullable|string|max:255',
        ];
    }
}
