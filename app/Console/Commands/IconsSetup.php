<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class IconsSetup extends Command
{
    protected $signature = 'icons:setup';
    protected $description = 'Cria a pasta de ícones em public/icons e adiciona um .gitignore padrão';

    public function handle()
    {
        $path = public_path('icons');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info("✔ Pasta criada: public/icons");
        } else {
            $this->info("ℹ A pasta public/icons já existe.");
        }

        // cria .gitignore
        $gitignore = $path . '/.gitignore';

        if (!File::exists($gitignore)) {
            File::put($gitignore, "*\n!.gitignore\n");
            $this->info("✔ Arquivo .gitignore criado.");
        }

        $this->info("🎉 Setup de ícones concluído!");
        return Command::SUCCESS;
    }
}
