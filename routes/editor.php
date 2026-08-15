<?php
use App\Http\Controllers\EditorController;

Route::prefix('editor')->group(function () {
    Route::get('/', [EditorController::class, 'index'])->name('editor');
    Route::get('/files', [EditorController::class, 'listFiles'])->name('editor.files');
    Route::get('/file', [EditorController::class, 'getFile'])->name('editor.getFile');
    Route::post('/save', [EditorController::class, 'saveFile'])->name('editor.saveFile');
    Route::post('/lsp/{language}', [EditorController::class, 'lspProxy'])->name('editor.lsp');
});
?>
