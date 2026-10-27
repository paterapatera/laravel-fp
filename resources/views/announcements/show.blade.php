<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $announcement->title }} - {{ config('app.name', 'Laravel') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] min-h-screen flex flex-col gap-6 p-6 lg:p-8">
        <header class="flex items-center justify-end gap-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="inline-block px-5 py-1.5 border border-[#19140035] dark:border-[#3E3E3A] rounded-sm text-sm leading-normal text-[#1b1b18] dark:text-[#EDEDEC]"
                >
                    ログアウト
                </button>
            </form>
        </header>

        <main class="w-full max-w-xl flex flex-col gap-4 border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 rounded-sm">
            <h1 class="text-lg font-medium">{{ $announcement->title }}</h1>
            <p class="text-sm">{{ $announcement->body }}</p>
        </main>
    </body>
</html>
