<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:menunggu_konfirmasi,menunggu_pembayaran,dalam_pengerjaan,selesai,dibatalkan',
        ];
    }
}
