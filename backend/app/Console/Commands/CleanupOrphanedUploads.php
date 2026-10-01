<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedUploads extends Command
{
    protected $signature = 'uploads:cleanup {--apply : pindahkan file yatim ke .trash} {--min-age-hours=24 : lewati file lebih baru dari N jam}';

    protected $description = 'Kumpulkan URL dari 8 kolom gambar kanonis dan pindahkan file yatim ke .trash (default dry-run)';

    /** @return array<string> */
    public static function canonicalImageColumns(): array
    {
        return [
            'hero_content.image_url',
            'portfolios.image_url',
            'team_members.photo_url',
            'client_logos.logo_url',
            'about_timeline_items.image_url',
            'mockup_offer.image_url',
            'seo_settings.og_image_url',
            'business_settings.footer_map_url',
        ];
    }

    public function handle(): int
    {
        $used = [];
        foreach (self::canonicalImageColumns() as $col) {
            [$table, $column] = explode('.', $col);
            try {
                $urls = DB::table($table)->whereNotNull($column)->pluck($column);
                foreach ($urls as $url) {
                    $rel = $this->toRelative((string) $url);
                    if ($rel) {
                        $used[$rel] = true;
                    }
                }
            } catch (\Throwable $e) {
                $this->warn("Lewati {$col}: ".$e->getMessage());
            }
        }

        $disk = Storage::disk('public');
        $files = $disk->allFiles();
        $minAgeHours = (int) $this->option('min-age-hours');
        $now = time();
        $orphans = [];
        foreach ($files as $file) {
            if (str_starts_with($file, '.trash/')) {
                continue;
            }
            if (basename($file) === '.gitignore') {
                continue;
            }
            if (isset($used[$file]) || isset($used['/storage/'.$file])) {
                continue;
            }
            try {
                $mtime = $disk->lastModified($file);
                if (($now - $mtime) < $minAgeHours * 3600) {
                    continue;
                }
            } catch (\Throwable) {
            }
            $orphans[] = $file;
        }

        if (! $this->option('apply')) {
            $this->info('Dry-run: '.count($orphans).' file yatim.');
            foreach (array_slice($orphans, 0, 50) as $f) {
                $this->line(' - '.$f);
            }

            return self::SUCCESS;
        }

        $stamp = date('Ymd-His');
        foreach ($orphans as $file) {
            try {
                $disk->move($file, '.trash/'.$stamp.'/'.$file);
            } catch (\Throwable $e) {
                $this->warn("Gagal pindah {$file}: ".$e->getMessage());
            }
        }
        $this->info('Dipindah: '.count($orphans).' file ke .trash/'.$stamp);

        return self::SUCCESS;
    }

    private function toRelative(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (str_starts_with($url, '/storage/')) {
            return ltrim(substr($url, strlen('/storage/')), '/');
        }
        if (str_contains($url, '/storage/')) {
            return ltrim(substr($url, strpos($url, '/storage/') + 9), '/');
        }

        return null;
    }
}
