<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>ログイン - {{ config('app.name', 'Laravel') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] min-h-screen flex items-center justify-center p-6">
        <main class="w-full max-w-md flex flex-col gap-6 border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 rounded-sm">
            <h1 class="text-lg font-medium">ログイン</h1>

            @if ($errors->any())
                <div class="flex flex-col gap-2 text-sm text-[#f53003] dark:text-[#FF4433]">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
                @csrf

                <label class="flex flex-col gap-2 text-sm">
                    <span>メールアドレス</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        class="border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] rounded-sm px-4 py-2"
                    >
                </label>

                <label class="flex flex-col gap-2 text-sm">
                    <span>パスワード</span>
                    <input
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        class="border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] rounded-sm px-4 py-2"
                    >
                </label>

                <button
                    type="submit"
                    class="inline-block px-5 py-1.5 bg-[#1b1b18] dark:bg-[#eeeeec] border border-black dark:border-[#eeeeec] rounded-sm text-sm leading-normal text-white dark:text-[#1C1C1A]"
                >
                    ログイン
                </button>
            </form>
        </main>
    </body>
</html>
