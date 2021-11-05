<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('/users', [UserController::class, 'store']);

Route::middleware(['apiJWT'])->group(function () {
    /** Informações do usuário logado */
    Route::get('auth/me', [AuthController::class, 'me']);

    /** Encerra o acesso */
    Route::get('auth/logout', [AuthController::class, 'logout']);

    /** Atualiza o token */
    Route::get('auth/refresh', [AuthController::class, 'refresh']);
    /** Listagem dos usuarios cadastrados, este rota serve de teste para verificar a proteção feita pelo jwt */

    Route::get('/users', [UserController::class, 'index']);
    /*Daqui para baixo você pode ir adiciondo todas as rotas que deverão estar protegidas em sua API*/
});
