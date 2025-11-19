<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PamiMedicamento;
use Illuminate\Support\Facades\DB;

class ImportarPamiCsv extends Command
{
    protected $signature = 'pami:import {file}';
    protected $description = 'Importar medicamentos desde un CSV de PAMI';

    public function handle()
    {
        $relative = $this->argument('file');
        $path = storage_path('app/' . $relative);

        if (!file_exists($path)) {
            $this->error("❌ Archivo no encontrado: $path");
            return;
        }

        $this->info("📥 Leyendo archivo desde: $path");

        ini_set('auto_detect_line_endings', true);

        $file = fopen($path, 'rb');

        if (!$file) {
            $this->error("❌ Error abriendo el archivo");
            return;
        }

        DB::disableQueryLog();

        // Leer la primera línea para detectar separador
        $header = fgets($file);
        $delimiter = $this->detectarDelimitador($header);

        $this->info("➡ Usando separador detectado: '{$delimiter}'");

        rewind($file);

        $importados = 0;
        $lineNumber = 0;

        while (!feof($file)) {

            $line = fgets($file);

            if ($line === false) break;
            $lineNumber++;

            // Saltar encabezado
            if ($lineNumber === 1) continue;

            // Saltar líneas vacías
            if (trim($line) === "") continue;

            // Forzar UTF-8 sin colgar
            $line = mb_convert_encoding($line, 'UTF-8', 'ISO-8859-1, UTF-8');

            // Separar con REGEX (soporta comillas y comas internas)
            $data = preg_split("/{$delimiter}(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/", $line);

            // Limpiar comillas y espacios
            $data = array_map(function ($v) {
                return trim(str_replace('"', '', $v));
            }, $data);

            if (count($data) < 5) {
                continue;
            }

            PamiMedicamento::create([
                'droga'        => $data[0],
                'marca'        => $data[1],
                'presentacion' => $data[2],
                'laboratorio'  => $data[3],
                'cobertura'    => $data[4]
            ]);

            $importados++;

            if ($importados % 500 === 0) {
                $this->info("➡ Importados: $importados...");
            }
        }

        fclose($file);

        $this->info("✅ Importación finalizada: $importados medicamentos");
    }

    private function detectarDelimitador($line)
    {
        $delimiters = [',', ';', "\t"];
        $counts = [];

        foreach ($delimiters as $d) {
            $counts[$d] = substr_count($line, $d);
        }

        arsort($counts);
        return array_key_first($counts);
    }
}
