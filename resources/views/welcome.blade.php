<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Andreak') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-surface text-on-surface font-sans antialiased">
        <div id="app"></div>
    </body>
    <script>
        window.appConfig = {
            appName: @json(config('app.name')),
            registrationEnabled: @json((bool) config('auth.public_registration')),
        };
    </script>
</html>
