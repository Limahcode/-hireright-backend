<?php

namespace App\Http\Requests\Job;

use App\Models\Job;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user can create job
        // return Auth::user()->can('create', Job::class);
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'type' => 'required|in:full-time,part-time,contract,remote',
            'location' => 'required|string|max:255',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|gt:salary_min',
            'skills' => 'required|array',
            'skills.*' => 'exists:skills,id',
            'deadline' => 'required|date|after:today',
        ];
    }

    public function messages(): array
    {
        return [
            'salary_max.gt' => 'Maximum salary must be greater than minimum salary',
            'skills.required' => 'At least one skill must be selected',
            // Custom messages for specific validation rules
        ];
    }
}
