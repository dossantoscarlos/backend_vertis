<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class EditorController extends Controller
{    
    /**
     * Cria o diretório de armazenamento do editor caso ainda não exista.
     */
    public function __construct()
    {
        if (! \Illuminate\Support\Facades\Storage::exists('editor')) {
            \Illuminate\Support\Facades\Storage::makeDirectory('editor');
        }
    }
    /**
     * Render the Monaco editor page.
     */
    public function index()
    {
        return view('editor');
    }

    /**
     * List files in the editor storage directory.
     */
    public function listFiles()
    {
        $files = Storage::files('editor');
        return response()->json(['files' => $files]);
    }

    /**
     * Retrieve file contents.
     */
    public function getFile(Request $request)
    {
        $path = $request->query('path');
        if (! $path || ! Storage::exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        $content = Storage::get($path);
        return response()->json(['content' => $content]);
    }

    /**
     * Save a file (create or update).
     */
    public function saveFile(Request $request)
    {
        $path = $request->input('path');
        $content = $request->input('content');
        if (! $path) {
            return response()->json(['error' => 'Path required'], 400);
        }
        Storage::put($path, $content);
        return response()->json(['status' => 'saved']);
    }

    /**
     * Proxy WebSocket connection to a language server.
     * This endpoint is hit by the front‑end `monaco-languageclient`.
     * It spawns the appropriate LSP process on demand.
     */
    public function lspProxy(Request $request, $language)
    {
        // Map language to executable command
        $commands = [
            'go' => ['gopls', 'serve'],
            'php' => ['php-language-server', 'daemon'],
            'javascript' => ['typescript-language-server', '--stdio'],
            'dart' => ['dart-language-server'],
            'python' => ['pylsp'],
        ];
        if (! isset($commands[$language])) {
            return response()->json(['error' => 'Unsupported language'], 400);
        }
        // Start the process (non‑blocking). The process will inherit STDIN/STDOUT which
        // will be proxied by Laravel WebSockets (beyondcode/laravel-websockets).
        $process = new Process($commands[$language]);
        $process->start();
        // Store the PID in the session so subsequent messages can be routed.
        session(['lsp_' . $language => $process->getPid()]);
        return response()->json(['status' => 'started', 'pid' => $process->getPid()]);
    }
}
?>
