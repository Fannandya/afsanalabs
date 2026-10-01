<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MockupOffer;
use App\Models\Order;
use App\Models\PricePackage;
use App\Models\SectionHeader;
use App\Models\User;
use App\Services\NotifyService;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function seedCatalog(): PricePackage
    {
        MockupOffer::create([
            'heading' => 'Mockup', 'description' => 'Desc',
            'price' => 149000, 'cta_label' => 'Minta',
        ]);

        return PricePackage::create([
            'name' => 'Startup', 'price' => 1500000, 'show_price' => true,
            'cta_label' => 'Pilih', 'cta_action' => 'order', 'is_active' => true,
        ]);
    }

    public function test_guard_menolak_tanpa_sesi(): void
    {
        $this->getJson('/api/v1/admin/orders')->assertStatus(401);
    }

    public function test_home_memuat_payload(): void
    {
        $package = $this->seedCatalog();
        $this->getJson('/api/v1/home')->assertOk()
            ->assertJsonPath('data.businessSettings', null)
            ->assertJsonCount(1, 'data.pricePackages');
        $this->assertSame('Startup', $package->name);
    }

    public function test_order_paket_lalu_lacak_unik(): void
    {
        $package = $this->seedCatalog();
        $payload = ['package_id' => $package->id, 'name' => 'Budi', 'email' => 'budi@x.com', 'phone' => '0811'];

        $code1 = $this->postJson('/api/v1/orders', $payload)->assertCreated()->json('trackingCode');
        $code2 = $this->postJson('/api/v1/orders', [...$payload, 'email' => 'ani@x.com'])->assertCreated()->json('trackingCode');

        $this->assertNotSame($code1, $code2);
        $this->getJson('/api/v1/orders/track/'.$code1)->assertOk()
            ->assertJsonPath('data.status', 'menunggu_konfirmasi')
            ->assertJsonMissing(['id' => 1]);
    }

    public function test_paket_nonaktif_ditolak_422(): void
    {
        $package = $this->seedCatalog();
        $package->update(['is_active' => false]);

        $this->postJson('/api/v1/orders', [
            'package_id' => $package->id, 'name' => 'Budi', 'email' => 'budi@x.com', 'phone' => '0811',
        ])->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_validasi_form_422_plus_fields(): void
    {
        $this->postJson('/api/v1/contact', ['name' => 'x'])->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['fields']]);
    }

    public function test_transisi_status_order(): void
    {
        $package = $this->seedCatalog();
        $admin = $this->admin();
        $code = $this->postJson('/api/v1/orders', [
            'package_id' => $package->id, 'name' => 'Budi', 'email' => 'budi@x.com', 'phone' => '0811',
        ])->json('trackingCode');
        $order = Order::where('tracking_code', $code)->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/admin/orders/'.$order->id.'/status', ['status' => 'dalam_pengerjaan'])
            ->assertOk()->assertJsonPath('data.status', 'dalam_pengerjaan');

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/admin/orders/'.$order->id.'/status', ['status' => 'ngawur'])
            ->assertStatus(422);
    }

    public function test_upload_menolak_bukan_gambar(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/uploads', [
            'file' => UploadedFile::fake()->create('doc.txt', 10, 'text/plain'),
        ])->assertStatus(422);
    }

    public function test_section_key_tidak_dikenal_404(): void
    {
        $admin = $this->admin();
        SectionHeader::create(['section_key' => 'pricing', 'heading' => 'Harga']);
        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/admin/section-headers/ngawur', ['heading' => 'x'])
            ->assertStatus(404);
    }

    public function test_hanya_satu_is_recommended(): void
    {
        $this->seedCatalog();
        $admin = $this->admin();
        $b = PricePackage::create([
            'name' => 'Business', 'price' => 3500000, 'cta_label' => 'Pilih',
            'cta_action' => 'order', 'is_active' => true, 'is_recommended' => true,
        ]);
        $a = PricePackage::where('name', 'Startup')->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/admin/packages/'.$a->id, ['is_recommended' => true])
            ->assertOk();

        $this->assertTrue($a->fresh()->is_recommended);
        $this->assertFalse($b->fresh()->is_recommended);
    }

    public function test_update_kategori_slug_sama_lolos(): void
    {
        $admin = $this->admin();
        $cat = Category::create(['name' => 'Web', 'slug' => 'web']);

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/admin/categories/'.$cat->id, ['name' => 'Web Baru'])
            ->assertOk()->assertJsonPath('data.slug', 'web');
    }

    public function test_upload_php_berisi_png_ditolak(): void
    {
        $admin = $this->admin();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $tmp = tempnam(sys_get_temp_dir(), 'evil').'.php';
        file_put_contents($tmp, $png);
        $file = new UploadedFile($tmp, 'shell.php', 'image/png', null, true);

        // Framework memblokir ekstensi php di validasi mimes (shouldBlockPhpUpload).
        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/uploads', ['file' => $file, 'folder' => 'misc'])
            ->assertStatus(422);
    }

    public function test_upload_png_valid_tersimpan_berekstensi_aman(): void
    {
        $admin = $this->admin();

        $url = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/uploads', [
                'file' => UploadedFile::fake()->image('foto.PNG', 100, 100),
                'folder' => 'portfolio',
            ])
            ->assertCreated()->json('url');

        $this->assertStringStartsWith('/storage/portfolio/', $url);
        $this->assertStringEndsWith('.png', $url);
    }

    public function test_nav_target_wajib_spa_atau_anchor(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/nav-links', [
            'label' => 'X', 'target' => 'https://evil.example', 'placement' => 'topnav',
        ])->assertStatus(422)->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/nav-links', [
            'label' => 'OK', 'target' => '#kontak', 'placement' => 'footer',
        ])->assertCreated();
    }

    public function test_ganti_password_berhasil_dan_mencabut_sesi_lain(): void
    {
        $admin = $this->admin();
        $oldHash = $admin->password;

        $this->actingAs($admin, 'sanctum')->patchJson('/api/v1/admin/password', [
            'current_password' => 'password',
            'password' => 'Baru12345',
            'password_confirmation' => 'Baru12345',
        ])->assertOk()->assertJsonPath('data.ok', true);

        $this->assertNotSame($oldHash, $admin->fresh()->password);
        $this->assertTrue(Hash::check('Baru12345', $admin->fresh()->password));
    }

    public function test_mail_tidak_terkonfigurasi_maka_skip(): void
    {
        config()->set('mail.mailers.smtp.host', null);

        $this->assertFalse(NotifyService::mailConfigured());
    }
}
