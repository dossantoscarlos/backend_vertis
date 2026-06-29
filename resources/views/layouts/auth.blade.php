<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-zinc-100 dark:bg-zinc-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }} · Login</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="h-full antialiased">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
