<?php

namespace Database\Seeders;

use App\Models\AboutTimelineItem;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\ClientLogo;
use App\Models\Faq;
use App\Models\FooterLegalLink;
use App\Models\HeroContent;
use App\Models\MockupOffer;
use App\Models\NavLink;
use App\Models\ObjectionQuestion;
use App\Models\Portfolio;
use App\Models\PricePackage;
use App\Models\ProcessStep;
use App\Models\ReferencePriceCard;
use App\Models\SectionHeader;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\ValueProp;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        BusinessSetting::firstOrCreate(['id' => 1], [
            'business_name' => 'LIMA AI',
            'footer_tagline' => 'Jasa pembuatan website profesional.',
            'footer_copyright' => '© 2026 LIMA AI',
            'contact_email' => 'halo@lima.ai',
            'phone' => '081234567890',
            'whatsapp_number' => '081234567890',
            'whatsapp_message_template' => 'Halo, saya {{nama}} ingin memesan paket {{paket}} dengan kode booking {{kode}}.',
            'address' => 'Jakarta, Indonesia',
            'description' => 'Jasa pembuatan website profesional.',
            'payment_instructions' => 'Transfer ke BCA 1234567890 a.n. LIMA AI, lalu konfirmasi via WhatsApp.',
        ]);

        HeroContent::firstOrCreate(['id' => 1], [
            'eyebrow' => 'Jasa Pembuatan Website',
            'heading' => 'Website Profesional untuk Bisnis Anda',
            'subheading' => 'Desain modern, cepat, dan SEO-friendly.',
            'cta_label' => 'Pesan Sekarang',
            'cta_target' => '/pesan',
            'trust_badge_text' => 'Dipercaya 100+ klien',
        ]);

        SeoSetting::firstOrCreate(['id' => 1], [
            'meta_title' => 'LIMA AI — Jasa Pembuatan Website',
            'meta_description' => 'Jasa pembuatan website profesional: cepat, modern, SEO-friendly.',
        ]);

        MockupOffer::firstOrCreate(['id' => 1], [
            'eyebrow' => 'Mockup Berbayar',
            'heading' => 'Lihat Desain Dulu Sebelum Komitmen',
            'description' => 'Dapatkan mockup homepage sebelum memesan paket penuh.',
            'feature_bullets' => ['Desain homepage', 'Revisi 1x', 'Estimasi 3 hari'],
            'price' => 149000,
            'cta_label' => 'Minta Mockup',
        ]);

        $headers = [
            'value_props' => ['heading' => 'Apa yang Kami Maksud dengan Website Profesional'],
            'process' => ['heading' => 'Proses Kerja'],
            'objection' => ['heading' => 'Masih Ragu?', 'note_text' => 'Harga pihak ketiga dicek per 2026-10-01.'],
            'services' => ['heading' => 'Layanan Kami'],
            'portfolio' => ['heading' => 'Portofolio'],
            'pricing' => ['heading' => 'Paket & Harga'],
            'faq' => ['heading' => 'Pertanyaan Umum'],
            'about' => ['heading' => 'Tentang Kami'],
            'team' => ['heading' => 'Tim Kami'],
        ];
        foreach ($headers as $key => $h) {
            SectionHeader::firstOrCreate(['section_key' => $key], [
                'eyebrow_text' => null,
                'heading' => $h['heading'],
                'note_text' => $h['note_text'] ?? null,
            ]);
        }

        foreach (['Cepat', 'Modern', 'SEO-friendly', 'Aman'] as $i => $t) {
            ValueProp::firstOrCreate(['title' => $t], [
                'description' => 'Deskripsi '.$t,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        foreach (['Konsultasi', 'Desain', 'Development', 'Serah Terima'] as $i => $t) {
            ProcessStep::firstOrCreate(['step_number' => $i + 1], [
                'title' => $t,
                'description' => 'Tahap '.$t,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        foreach (['Apakah mahal?', 'Berapa lama?', 'Apakah bisa revisi?'] as $i => $q) {
            ObjectionQuestion::firstOrCreate(['question' => $q], [
                'answer' => 'Jawaban '.$q,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        foreach ([['Domain .com', 'Rp150-200rb', '/tahun'], ['Hosting', 'Rp300-500rb', '/tahun'], ['SSL', 'Gratis', '']] as $i => [$label, $val, $note]) {
            ReferencePriceCard::firstOrCreate(['label' => $label], [
                'price_value' => $val,
                'price_note' => $note,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        foreach (['Company Profile', 'Toko Online', 'Landing Page', 'Custom'] as $i => $n) {
            Service::firstOrCreate(['name' => $n], [
                'description' => 'Layanan '.$n,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $cat = Category::firstOrCreate(['slug' => 'company-profile'], ['name' => 'Company Profile', 'sort_order' => 0]);
        for ($i = 1; $i <= 3; $i++) {
            Portfolio::firstOrCreate(['title' => 'Contoh Portofolio '.$i], [
                'category_id' => $cat->id,
                'image_url' => '/storage/portfolio/contoh-'.$i.'.jpg',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => $i - 1,
            ]);
        }

        $packages = [
            ['Startup', 1500000, false, 'order'],
            ['Business', 3500000, true, 'order'],
            ['Custom', null, false, 'contact'],
        ];
        foreach ($packages as $i => [$name, $price, $rec, $cta]) {
            PricePackage::firstOrCreate(['name' => $name], [
                'tagline' => $name,
                'price' => $price,
                'show_price' => $price !== null,
                'features' => ['Fitur A', 'Fitur B'],
                'is_recommended' => $rec,
                'cta_label' => $cta === 'order' ? 'Pilih Paket' : 'Konsultasi',
                'cta_action' => $cta,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        foreach (['Berapa biayanya?', 'Berapa lama pengerjaan?', 'Apakah ada garansi?', 'Bagaimana pembayaran?'] as $i => $q) {
            Faq::firstOrCreate(['question' => $q], [
                'answer' => 'Jawaban '.$q,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        foreach ([['Beranda', '/', 'topnav'], ['Portofolio', '/portofolio', 'topnav'], ['Harga', '/#pricing', 'topnav'], ['Kontak', '/#kontak', 'footer']] as $i => [$label, $target, $placement]) {
            NavLink::firstOrCreate(['label' => $label, 'placement' => $placement], [
                'target' => $target,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
        FooterLegalLink::firstOrCreate(['label' => 'Kebijakan Privasi'], [
            'url' => '/privasi',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        Testimonial::firstOrCreate(['client_name' => 'Klien Contoh'], [
            'content' => 'Hasil memuaskan.',
            'rating' => 5,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        // About/Team/ClientLogo dibiarkan kosong (auto-sembunyi) — seed kosong disengaja.
        AboutTimelineItem::query()->delete();
        TeamMember::query()->delete();
        ClientLogo::query()->delete();
    }
}
