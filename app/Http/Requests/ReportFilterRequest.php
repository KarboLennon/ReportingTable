<?php
namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;


class ReportFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'status' => ['nullable', 'array'],
            'status.*' => ['string'],
            'channel' => ['nullable', 'array'],
            'channel.*' => ['string'],
            'category' => ['nullable', 'array'],
            'category.*' => ['string'],
            'sort' => ['nullable', 'in:ordered_at,amount,customer_name,status,channel,category'],
            'dir' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'between:10,200'],
        ];
    }


    public function authorize(): bool
    {
        return true;
    }
}