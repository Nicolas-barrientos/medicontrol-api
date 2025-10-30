<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicamentoCatalogo;
use Illuminate\Http\Request;

class MedicamentoCatalogoController extends Controller
{
    /**
     * Devuelve una lista de medicamentos, con filtro opcional por nombre.
     */
    public function index(Request $request)
    {
        $nombre = $request->input('nombre');

        $query = MedicamentoCatalogo::query();

        // Filtrar por nombre comercial o genérico si se pasa parámetro
        if ($nombre) {
            $query->where('nombre_comercial', 'like', "%$nombre%")
                  ->orWhere('nombre_generico', 'like', "%$nombre%");
        }

        // Limitar resultados y seleccionar solo campos relevantes
        $medicamentos = $query->limit(50)
            ->get([
                'id',
                'laboratorio_titular',
                'numero_certificado',
                'nombre_comercial',
                'nombre_generico',
                'concentracion',
                'forma_farmaceutica',
                'presentacion'
            ]);

        return response()->json($medicamentos);
    }

    /**
     * Devuelve un medicamento específico por ID
     */
    public function show($id)
    {
        $med = MedicamentoCatalogo::find($id);

        if (!$med) {
            return response()->json(['message' => 'Medicamento no encontrado'], 404);
        }

        // Devolver solo campos relevantes
        return response()->json([
            'id' => $med->id,
            'laboratorio_titular' => $med->laboratorio_titular,
            'numero_certificado' => $med->numero_certificado,
            'nombre_comercial' => $med->nombre_comercial,
            'nombre_generico' => $med->nombre_generico,
            'concentracion' => $med->concentracion,
            'forma_farmaceutica' => $med->forma_farmaceutica,
            'presentacion' => $med->presentacion,
        ]);
    }
}
