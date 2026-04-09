<?php

namespace App\Http\Requests;

use App\Services\SupplierImportExportService;
use Illuminate\Foundation\Http\FormRequest;

class ImportSupplierRequest extends FormRequest
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
            'strategy' => [
                'required',
                'in:'.implode(',', [
                    SupplierImportExportService::STRATEGY_OVERWRITE,
                    SupplierImportExportService::STRATEGY_SKIP,
                    SupplierImportExportService::STRATEGY_DUPLICATE_LAYUP,
                    SupplierImportExportService::STRATEGY_REJECT,
                ]),
            ],
            'payload' => ['nullable', 'string', 'required_without:file'],
            'file' => ['nullable', 'file', 'mimes:json,txt', 'required_without:payload'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadAsArray(): array
    {
        $content = (string) ($this->input('payload') ?? '');

        if ($this->hasFile('file')) {
            $content = (string) file_get_contents($this->file('file')->getRealPath());
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }
}
