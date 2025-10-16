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
    public function update(Request $request, Medicamento $medicamento)
    {
        $this->authorize('update', $medicamento);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'presentacion' => 'nullable|string|max:255',
            'dosis' => 'nullable|string|max:255',
            'frecuencia' => 'nullable|string|max:255',
            'via' => 'nullable|string|max:255',
            'stock' => 'nullable|integer',
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
        return response()->json($medicamento);
    }

    // 🗑️ Eliminar medicamento
    public function destroy(Medicamento $medicamento)
    {
        $this->authorize('delete', $medicamento);

        if ($medicamento->imagen && Storage::disk('public')->exists($medicamento->imagen)) {
            Storage::disk('public')->delete($medicamento->imagen);
        }

        $medicamento->delete();
        return response()->json(['message' => 'Medicamento eliminado correctamente']);
    }

        // 📸 Actualizar solo la imagen de un medicamento
    public function updateImagen(Request $request, Medicamento $medicamento)
    {
        // Asegurar que el medicamento pertenece al usuario autenticado
        $this->authorize('update', $medicamento);

        // Validar que venga una imagen válida
        $request->validate([
            'imagen' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Si ya tenía una imagen, eliminarla del almacenamiento
        if ($medicamento->imagen && \Storage::disk('public')->exists($medicamento->imagen)) {
            \Storage::disk('public')->delete($medicamento->imagen);
        }

        // Guardar la nueva imagen
        $path = $request->file('imagen')->store('medicamentos', 'public');

        // Actualizar campo en la base de datos
        $medicamento->update(['imagen' => $path]);

        return response()->json([
            'message' => 'Imagen actualizada correctamente',
            'imagen_url' => asset('storage/' . $path)
        ]);
    }
}
