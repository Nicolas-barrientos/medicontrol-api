<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MedicamentoController;
use App\Http\Controllers\API\UserController;

Route::middleware('auth:sanctum')->post('/users/update-token', [UserController::class, 'updateToken']);

// 🔒 Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // CRUD principal de medicamentos
    Route::apiResource('medicamentos', MedicamentoController::class);

    // Endpoint exclusivo para actualizar solo la imagen
    Route::put('medicamentos/{medicamento}/imagen', [MedicamentoController::class, 'updateImagen']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

// 🔓 Rutas públicas de autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ Nueva ruta pública para buscar en el catálogo (CSV importado)
Route::get('/pami/buscar', function (Illuminate\Http\Request $request) {
    $q = $request->input('q');

    return App\Models\PamiMedicamento::where('droga', 'ILIKE', "%$q%")
        ->orWhere('marca', 'ILIKE', "%$q%")
        ->limit(20)
        ->get();
});
