<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Transaction;

Route::post(\"/webhook/toppay\", function (Request $request) {
    $request->validate([
        \'user_id\' => \'required|exists:users,id\',
        \'amount\' => \'required|numeric|min:0.01\',
    ]);

    $user = User::find($request->user_id);
    $amount = $request->amount;

    // Asumiendo que el método deposit() ya está implementado en el modelo User
    // y maneja la lógica de la wallet.
    // También se asume que el paquete babaic/laravel-wallet está instalado y configurado.
    $user->deposit($amount);

    Transaction::create([
        \'user_id\' => $user->id,
        \'amount\' => $amount,
        \'type\' => \'deposit\',
        \'description\' => \'Depósito vía webhook TopPay\',
    ]);

    return response()->json([\'message\' => \'Depósito realizado con éxito\'], 200);
});

