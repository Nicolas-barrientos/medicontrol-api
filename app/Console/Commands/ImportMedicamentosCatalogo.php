<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB; // 👈 ESTA LÍNEA AGREGA
use Carbon\Carbon;

class ImportMedicamentosCatalogo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:catalogo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa los medicamentos del archivo vnm-2018.csv al catálogo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = storage_path('app/vnm-2018.csv');

        if (!file_exists($file)) {
            $this->error('⚠️ El archivo vnm-2018.csv no se encuentra en storage/app');
            return;
        }

        $this->info('⏳ Importando medicamentos del catálogo ANMAT...');

        if (($handle = fopen($file, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ';');
            $count = 0;

            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                DB::table('medicamentos_catalogo')->insert([
                    'laboratorio_titular' => $data[0] ?? null,
                    'numero_certificado' => $data[1] ?? null,
                    'nombre_comercial' => $data[2] ?? null,
                    'nombre_generico' => $data[3] ?? null,
                    'concentracion' => $data[4] ?? null,
                    'forma_farmaceutica' => $data[5] ?? null,
                    'presentacion' => $data[6] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
            fclose($handle);

            $this->info("✅ Importación completada: $count registros agregados");
        } else {
            $this->error('❌ No se pudo abrir el archivo CSV');
        }
    }
}
