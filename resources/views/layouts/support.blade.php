<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-zinc-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }} · Workspace</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="h-full bg-zinc-50 antialiased text-zinc-900">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
