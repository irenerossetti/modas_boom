<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PublishDompdfFonts extends Command
{
    protected $signature = 'dompdf:publish-fonts';
    protected $description = 'Copy bundled DomPDF DejaVu fonts to storage/fonts so they can be reliably embedded in PDFs.';

    public function handle()
    {
        $vendorFontDir = base_path('vendor/dompdf/dompdf/lib/fonts');
        $destDir = storage_path('fonts');

        if (!is_dir($vendorFontDir)) {
            $this->error("DomPDF vendor fonts not found at: {$vendorFontDir}");
            return 1;
        }
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $files = glob($vendorFontDir . DIRECTORY_SEPARATOR . '*.ttf');
        foreach ($files as $file) {
            $dest = $destDir . DIRECTORY_SEPARATOR . basename($file);
            copy($file, $dest);
            $this->info("Copied: " . basename($file));
        }

        $this->info("DomPDF fonts copied to: {$destDir}");
        return 0;
    }
}
