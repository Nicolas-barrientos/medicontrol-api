<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MedicamentoCatalogo;

class SepararMedicamentos extends Command
{
    protected $signature = 'medicamentos:separar';
    protected $description = 'Separa los datos importados en laboratorio_titular a sus columnas correspondientes';

    public function handle()
    {
        $medicamentos = MedicamentoCatalogo::all();
        $this->info("Procesando " . $medicamentos->count() . " registros...");

        foreach ($medicamentos as $med) {
            // Dividir la columna por comas
            $partes = str_getcsv($med->laboratorio_titular); // maneja comillas correctamente

            if (count($partes) >= 7) {
                $med->laboratorio_titular = $partes[0] ?? null;
                $med->numero_certificado = $partes[1] ?? null;
                $med->nombre_comercial = $partes[2] ?? null;
                $med->nombre_generico = $partes[3] ?? null;
                $med->concentracion = $partes[4] ?? null;
                $med->forma_farmaceutica = $partes[5] ?? null;
                $med->presentacion = $partes[6] ?? null;

                $med->save();
                $this->info("Registro ID {$med->id} actualizado");
            } else {
                $this->warn("Registro ID {$med->id} no tiene el formato esperado");
            }
        }

        $this->info("¡Proceso finalizado!");
    }
}
