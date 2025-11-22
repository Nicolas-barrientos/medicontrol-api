<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicamentoController extends Controller
{
    // 📦 Listar medicamentos del usuario autenticado
    public function index()
    {
        return response()->json(
            auth()->user()->medicamentos()->latest()->get()
        );
    }

    // ➕ Crear nuevo medicamento (con imagen opcional)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'presentacion' => 'nullable|string|max:255',
            'dosis' => 'nullable|string|max:255',
            'frecuencia' => 'nullable|string|max:255',
            'via' => 'nullable|string|max:255',
            'stock' => 'nullable|integer',
            'hora_recordatorio' => 'nullable|string',
            'inicio' => 'nullable|date',
            'fin' => 'nullable|date',
            'notas' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Guardar imagen si se envía
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('medicamentos', 'public');
            $validated['imagen'] = $path;
        }

        $validated['user_id'] = auth()->id();
        $medicamento = Medicamento::create($validated);

        return response()->json($medicamento, 201);
    }

    // ✏️ Actualizar medicamento
    public function update(Request $request, $id)
    {
        try {
            // Buscar el medicamento
            $medicamento = Medicamento::find($id);

            if (!$medicamento) {
                return response()->json([
                    'message' => 'Medicamento no encontrado'
                ], 404);
            }

            // Verificar que pertenece al usuario autenticado
            if ($medicamento->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'No tienes permiso para editar este medicamento'
                ], 403);
            }

            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'presentacion' => 'nullable|string|max:255',
                'dosis' => 'nullable|string|max:255',
                'frecuencia' => 'nullable|string|max:255',
                'via' => 'nullable|string|max:255',
                'stock' => 'nullable|integer',
                'hora_recordatorio' => 'nullable|string',
                'inicio' => 'nullable|date',
                'fin' => 'nullable|date',
                'notas' => 'nullable|string',
                'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            // Si hay imagen nueva, eliminar la anterior y guardar la nueva
            if ($request->hasFile('imagen')) {
                if ($medicamento->imagen && Storage::disk('public')->exists($medicamento->imagen)) {
                    Storage::disk('public')->delete($medicamento->imagen);
                }
                $path = $request->file('imagen')->store('medicamentos', 'public');
                $validated['imagen'] = $path;
            }

            $medicamento->update($validated);
            
            return response()->json([
                'message' => 'Medicamento actualizado correctamente',
                'data' => $medicamento
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar medicamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 🗑️ Eliminar medicamento
    public function destroy($id)
    {
        try {
            // Buscar el medicamento
            $medicamento = Medicamento::find($id);

            if (!$medicamento) {
                return response()->json([
                    'message' => 'Medicamento no encontrado'
                ], 404);
            }

            // Verificar que pertenece al usuario autenticado
            if ($medicamento->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'No tienes permiso para eliminar este medicamento'
                ], 403);
            }

            // Eliminar imagen si existe
            if ($medicamento->imagen && Storage::disk('public')->exists($medicamento->imagen)) {
                Storage::disk('public')->delete($medicamento->imagen);
            }

            // Eliminar el medicamento
            $medicamento->delete();

            return response()->json([
                'message' => 'Medicamento eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar medicamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 📸 Actualizar solo la imagen de un medicamento
    public function updateImagen(Request $request, $id)
    {
        try {
            // Buscar el medicamento
            $medicamento = Medicamento::find($id);

            if (!$medicamento) {
                return response()->json([
                    'message' => 'Medicamento no encontrado'
                ], 404);
            }

            // Verificar que pertenece al usuario autenticado
            if ($medicamento->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'No tienes permiso para actualizar este medicamento'
                ], 403);
            }

            // Validar que venga una imagen válida
            $request->validate([
                'imagen' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Si ya tenía una imagen, eliminarla del almacenamiento
            if ($medicamento->imagen && Storage::disk('public')->exists($medicamento->imagen)) {
                Storage::disk('public')->delete($medicamento->imagen);
            }

            // Guardar la nueva imagen
            $path = $request->file('imagen')->store('medicamentos', 'public');

            // Actualizar campo en la base de datos
            $medicamento->update(['imagen' => $path]);

            return response()->json([
                'message' => 'Imagen actualizada correctamente',
                'imagen_url' => asset('storage/' . $path)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar imagen',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}