<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Plog - Advanced Laravel Logging</title>
    @livewireStyles
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-900">
    <div class="w-full h-screen flex flex-col">
        <div class="bg-white p-4 border-b border-gray-200 flex justify-between items-center">
            <div class="text-2xl font-bold text-blue-600">📊 Plog</div>
            <div class="text-gray-600 text-sm">
                @auth
                    Logged in as: {{ Auth::user()->email }}
                @else
                    Not authenticated
                @endauth
            </div>
        </div>

        <div class="flex-1">
            @livewire('plog-viewer')
        </div>
    </div>

    @livewireScripts
</body>
</html>