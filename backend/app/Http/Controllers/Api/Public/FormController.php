<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\StoreContactMessageRequest;
use App\Http\Requests\StoreMockupRequestRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Models\BusinessSetting;
use App\Models\Consultation;
use App\Models\ContactMessage;
use App\Models\MockupOffer;
use App\Models\Order;
use App\Models\PricePackage;
use App\Services\NotifyService;
use App\Services\TrackingCodeService;
use App\Support\Whatsapp;

class FormController extends Controller
{
    public function storeContact(StoreContactMessageRequest $request)
    {
        $row = ContactMessage::create([...$request->validated(), 'status' => 'baru']);
        NotifyService::send('pesan_kontak', 'Pesan kontak baru dari '.$row->name, $row->id);

        return response()->json(['message' => 'Pesan terkirim.'], 201);
    }

    public function storeConsultation(StoreConsultationRequest $request)
    {
        $row = Consultation::create([...$request->validated(), 'status' => 'baru']);
        NotifyService::send('konsultasi_baru', 'Konsultasi baru dari '.$row->name, $row->id);

        return response()->json(['message' => 'Konsultasi terkirim.'], 201);
    }

    public function storeOrder(StoreOrderRequest $request)
    {
        $data = $request->validated();
        $package = PricePackage::find($data['package_id']);
        if (! $package) {
            return response()->json(['error' => ['message' => 'Paket tidak ditemukan.']], 404);
        }
        if (! $package->is_active) {
            return response()->json(['error' => ['message' => 'Paket tidak aktif.', 'code' => 'VALIDATION_ERROR', 'fields' => ['package_id' => ['Paket tidak aktif.']]]], 422);
        }
        $order = Order::create([
            'tracking_code' => TrackingCodeService::generate(),
            'order_type' => 'paket',
            'package_id' => $package->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'notes' => $data['notes'] ?? null,
            'related_mockup_order_id' => $data['related_mockup_order_id'] ?? null,
            'status' => 'menunggu_konfirmasi',
        ]);
        NotifyService::send('pesanan_baru', 'Pesanan paket baru '.$order->tracking_code.' ('.$package->name.')', $order->id);

        $business = BusinessSetting::find(1);
        $normalized = Whatsapp::normalize($business?->whatsapp_number);
        $whatsappUrl = null;
        if ($normalized) {
            $message = Whatsapp::fillTemplate($business?->whatsapp_message_template, [
                'kode' => $order->tracking_code,
                'paket' => $package->name,
                'nama' => $order->name,
            ]);
            $whatsappUrl = Whatsapp::url($normalized, $message);
        }

        return response()->json(['trackingCode' => $order->tracking_code, 'whatsappUrl' => $whatsappUrl], 201);
    }

    public function storeMockup(StoreMockupRequestRequest $request)
    {
        $offer = MockupOffer::find(1);
        if (! $offer) {
            return response()->json(['error' => ['message' => 'Penawaran mockup belum dikonfigurasi.']], 404);
        }
        $data = $request->validated();
        $order = Order::create([
            'tracking_code' => TrackingCodeService::generate(),
            'order_type' => 'mockup',
            'mockup_fee' => $offer->price,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'notes' => $data['notes'] ?? null,
            'status' => 'menunggu_pembayaran',
        ]);
        NotifyService::send('permintaan_mockup', 'Permintaan mockup baru '.$order->tracking_code, $order->id);

        $business = BusinessSetting::find(1);

        return response()->json([
            'trackingCode' => $order->tracking_code,
            'mockupFee' => $order->mockup_fee,
            'paymentInstructions' => $business?->payment_instructions,
        ], 201);
    }
}
