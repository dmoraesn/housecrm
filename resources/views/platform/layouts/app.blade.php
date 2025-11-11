<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'HouseCRM')</title>
    <!-- Meta CSRF para AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Estilos e scripts gerenciados pelo Vite -->
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/kanban.css',
        'resources/js/kanban.js'
    ])
    <!-- Estilos principais do Orchid -->
    @orchidStyles
    <!-- Estilos adicionais de views -->
    @stack('head')
    @stack('styles')
</head>
<body>
    <div id="app">
        @yield('content')
    </div>
    <!-- Scripts principais do Orchid -->
    @orchidScripts
    <!-- Scripts adicionais das views -->
    @stack('scripts')
</body>
</html>
