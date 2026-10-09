<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center pt-4 pt-sm-0">
            <div class="mb-3">
                <a href="/" class="d-inline-block">
                    <x-application-logo style="width: 5rem; height: 5rem;" />
                </a>
            </div>

            <div class="w-100 mt-3 px-4 py-4 bg-white shadow-sm rounded-3" style="max-width: 28rem;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
