<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MedicamentoController;
use App\Http\Controllers\Api\RegistroTomaController;
use App\Http\Controllers\API\UserController;

Route::middleware('auth:sanctum')->post('/users/update-token', [UserController::class, 'updateToken']);

// 🔒 Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // CRUD completo de medicamentos
    Route::get('/medicamentos', [MedicamentoController::class, 'index']);
    Route::post('/medicamentos', [MedicamentoController::class, 'store']);
    Route::put('/medicamentos/{id}', [MedicamentoController::class, 'update']);
    Route::delete('/medicamentos/{id}', [MedicamentoController::class, 'destroy']);
    
    // Endpoint exclusivo para actualizar solo la imagen
    Route::post('/medicamentos/{id}/imagen', [MedicamentoController::class, 'updateImagen']);

    // 📊 CRUD de registros de tomas
    Route::get('/registros-tomas', [RegistroTomaController::class, 'index']);
    Route::post('/registros-tomas', [RegistroTomaController::class, 'store']);
    Route::put('/registros-tomas/{id}', [RegistroTomaController::class, 'update']);
    Route::delete('/registros-tomas/{id}', [RegistroTomaController::class, 'destroy']);
    
    // 📈 Ruta para estadísticas y reporte de progreso
    Route::get('/progreso', [RegistroTomaController::class, 'progreso']);
    Route::get('/estadisticas', [RegistroTomaController::class, 'estadisticas']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

// 🔓 Rutas públicas de autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ Ruta para BUSCAR medicamentos PAMI
Route::get('/pami/buscar', function (Illuminate\Http\Request $request) {
    $q = $request->input('q');

    if (empty($q)) {
        return response()->json([]);
    }

    return App\Models\PamiMedicamento::where('droga', 'ILIKE', "%$q%")
        ->orWhere('marca', 'ILIKE', "%$q%")
        ->orderBy('droga')
        ->limit(50)
        ->get();
});

// ✅ Ruta para VER TODOS los medicamentos PAMI (paginado)
Route::get('/pami/medicamentos', function () {
    return App\Models\PamiMedicamento::orderBy('droga')->paginate(100);
});