<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class IconsDownloadMaterial extends Command
{
    protected $signature = 'icons:download-material';
    protected $description = 'Baixa o pacote Google Material Icons (SVG) de forma segura, com timeout estendido.';

    public function handle()
    {
        $folder = public_path('icons/material');

        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
            $this->info("✔ Pasta criada: public/icons/material");
        }

        $zipUrl = "https://codeload.github.com/google/material-design-icons/zip/refs/heads/master";
        $zipPath = storage_path('material_icons.zip');

        $this->info("⬇ Baixando Material Icons... Isso pode levar alguns minutos...");

        // Abrir arquivo para escrita
        $fp = fopen($zipPath, 'w+');

        // Fazer download via cURL nativo → muito mais rápido
        $ch = curl_init($zipUrl);

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutos
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 32);

        $result = curl_exec($ch);

        if (!$result) {
            $this->error("❌ Erro ao baixar: " . curl_error($ch));
            curl_close($ch);
            fclose($fp);
            return Command::FAILURE;
        }

        curl_close($ch);
        fclose($fp);

        $this->info("✔ Download concluído.");

        // Extrair ZIP
        $this->info("📦 Extraindo arquivos...");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo(storage_path('material_icons'));
            $zip->close();
        } else {
            $this->error("❌ Falha ao abrir o ZIP.");
            return Command::FAILURE;
        }

        // Copiar apenas os SVG
        $source = storage_path('material_icons/material-design-icons-master/src');

        if (!File::exists($source)) {
            $this->error("❌ Estrutura inesperada no ZIP.");
            return Command::FAILURE;
        }

        File::copyDirectory($source, $folder);

        // Limpar arquivos temporários
        File::delete($zipPath);
        File::deleteDirectory(storage_path('material_icons'));

        $this->info("🎉 Material Icons instalados com sucesso!");
        return Command::SUCCESS;
    }
}
