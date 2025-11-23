<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegistroToma;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroTomaController extends Controller
{
    /**
     * 📋 Listar registros de tomas con filtros opcionales
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $query = $user->registrosTomas()->with('medicamento');

        // Filtro por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->fechasEntre($request->fecha_inicio, $request->fecha_fin);
        }

        // Filtro por medicamento
        if ($request->has('medicamento_id')) {
            $query->where('medicamento_id', $request->medicamento_id);
        }

        // Filtro por estado (tomado/no tomado)
        if ($request->has('tomado')) {
            $tomado = filter_var($request->tomado, FILTER_VALIDATE_BOOLEAN);
            if ($tomado) {
                $query->tomados();
            } else {
                $query->noTomados();
            }
        }

        // Ordenar por fecha descendente
        $registros = $query->orderBy('fecha_hora', 'desc')->get();

        return response()->json($registros);
    }

    /**
     * ➕ Crear nuevo registro de toma
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicamento_id' => 'required|exists:medicamentos,id',
            'tomado' => 'required|boolean',
            'fecha_hora' => 'required|date',
            'notas' => 'nullable|string',
        ]);

        /** @var User $user */
        $user = $request->user();
        
        // Verificar que el medicamento pertenece al usuario
        $medicamento = $user->medicamentos()->find($validated['medicamento_id']);
        
        if (!$medicamento) {
            return response()->json([
                'message' => 'El medicamento no pertenece al usuario'
            ], 403);
        }

        $validated['user_id'] = $user->id;
        $registro = RegistroToma::create($validated);

        return response()->json([
            'message' => 'Registro creado correctamente',
            'data' => $registro->load('medicamento')
        ], 201);
    }

    /**
     * ✏️ Actualizar registro de toma
     */
    public function update(Request $request, $id)
    {
        /** @var User $user */
        $user = $request->user();
        
        $registro = RegistroToma::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        // Verificar que pertenece al usuario
        if ($registro->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'tomado' => 'sometimes|boolean',
            'fecha_hora' => 'sometimes|date',
            'notas' => 'nullable|string',
        ]);

        $registro->update($validated);

        return response()->json([
            'message' => 'Registro actualizado correctamente',
            'data' => $registro->load('medicamento')
        ]);
    }

    /**
     * 🗑️ Eliminar registro de toma
     */
    public function destroy(Request $request, $id)
    {
        /** @var User $user */
        $user = $request->user();
        
        $registro = RegistroToma::find($id);

        if (!$registro) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        // Verificar que pertenece al usuario
        if ($registro->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $registro->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    /**
     * 📊 Obtener datos de progreso (para generar PDF)
     */
    public function progreso(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        /** @var User $user */
        $user = $request->user();
        $userId = $user->id;
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        // Obtener medicamentos del usuario
        $medicamentos = $user->medicamentos;

        // Obtener registros en el rango de fechas
        $registros = RegistroToma::delUsuario($userId)
            ->fechasEntre($fechaInicio, $fechaFin)
            ->with('medicamento')
            ->get();

        // Agrupar registros por medicamento
        $registrosPorMedicamento = $registros->groupBy('medicamento_id');

        // Calcular estadísticas por medicamento
        $detalleMedicamentos = [];
        foreach ($medicamentos as $medicamento) {
            $registrosMed = $registrosPorMedicamento->get($medicamento->id, collect());
            
            $tomados = $registrosMed->where('tomado', true)->count();
            $noTomados = $registrosMed->where('tomado', false)->count();
            $total = $tomados + $noTomados;
            $porcentaje = $total > 0 ? round(($tomados / $total) * 100) : 0;

            $detalleMedicamentos[] = [
                'id' => $medicamento->id,
                'nombre' => $medicamento->nombre,
                'dosis' => $medicamento->dosis,
                'frecuencia' => $medicamento->frecuencia,
                'tomados' => $tomados,
                'no_tomados' => $noTomados,
                'total' => $total,
                'porcentaje' => $porcentaje,
            ];
        }

        // Calcular estadísticas generales
        $totalTomas = $registros->count();
        $tomasCumplidas = $registros->where('tomado', true)->count();
        $tomasNoCumplidas = $registros->where('tomado', false)->count();
        $adherenciaGeneral = $totalTomas > 0 ? round(($tomasCumplidas / $totalTomas) * 100) : 0;

        return response()->json([
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'estadisticas_generales' => [
                'total_medicamentos' => $medicamentos->count(),
                'total_tomas' => $totalTomas,
                'tomas_cumplidas' => $tomasCumplidas,
                'tomas_no_cumplidas' => $tomasNoCumplidas,
                'adherencia' => $adherenciaGeneral,
            ],
            'detalle_medicamentos' => $detalleMedicamentos,
        ]);
    }

    /**
     * 📈 Obtener estadísticas generales del usuario
     */
    public function estadisticas(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $userId = $user->id;

        // Estadísticas de los últimos 30 días
        $fechaInicio = now()->subDays(30);
        $fechaFin = now();

        $registros = RegistroToma::delUsuario($userId)
            ->fechasEntre($fechaInicio, $fechaFin)
            ->get();

        $totalTomas = $registros->count();
        $tomasCumplidas = $registros->where('tomado', true)->count();
        $adherencia = $totalTomas > 0 ? round(($tomasCumplidas / $totalTomas) * 100) : 0;

        // Medicamentos con mejor y peor adherencia
        $adherenciaPorMedicamento = DB::table('registros_tomas')
            ->select(
                'medicamento_id',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN tomado = true THEN 1 ELSE 0 END) as cumplidas')
            )
            ->where('user_id', $userId)
            ->where('fecha_hora', '>=', $fechaInicio)
            ->where('fecha_hora', '<=', $fechaFin)
            ->groupBy('medicamento_id')
            ->get();

        return response()->json([
            'periodo' => 'Últimos 30 días',
            'total_tomas' => $totalTomas,
            'tomas_cumplidas' => $tomasCumplidas,
            'adherencia_general' => $adherencia,
            'adherencia_por_medicamento' => $adherenciaPorMedicamento,
        ]);
    }
}