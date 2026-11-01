<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>お知らせの登録 - {{ config('app.name', 'Laravel') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] min-h-screen flex flex-col gap-6 p-6 lg:p-8">
        <main class="w-full max-w-xl flex flex-col gap-4 border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] p-6 rounded-sm">
            <h1 class="text-lg font-medium">お知らせの登録</h1>

            <form method="POST" action="{{ route('announcements.register.store') }}" class="flex flex-col gap-4">
                @csrf

                <label class="flex flex-col gap-2 text-sm">
                    <span>題名</span>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title', '') }}"
                        class="border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] rounded-sm px-4 py-2"
                    >
                    @error('title')
                        <span class="text-[#f53003] dark:text-[#FF4433]">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex flex-col gap-2 text-sm">
                    <span>要約</span>
                    <input
                        type="text"
                        name="summary"
                        value="{{ old('summary', '') }}"
                        class="border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] rounded-sm px-4 py-2"
                    >
                    @error('summary')
                        <span class="text-[#f53003] dark:text-[#FF4433]">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex flex-col gap-2 text-sm">
                    <span>本文</span>
                    <textarea
                        name="body"
                        class="border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] rounded-sm px-4 py-2"
                    >{{ old('body', '') }}</textarea>
                    @error('body')
                        <span class="text-[#f53003] dark:text-[#FF4433]">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex flex-col gap-2 text-sm">
                    <span>開始</span>
                    <input
                        type="text"
                        name="starts_at"
                        value="{{ old('starts_at', '') }}"
                        class="border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] rounded-sm px-4 py-2"
                    >
                    @error('starts_at')
                        <span class="text-[#f53003] dark:text-[#FF4433]">{{ $message }}</span>
                    @enderror
                </label>

                <label class="flex flex-col gap-2 text-sm">
                    <span>終了</span>
                    <input
                        type="text"
                        name="ends_at"
                        value="{{ old('ends_at', '') }}"
                        class="border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#0a0a0a] rounded-sm px-4 py-2"
                    >
                    @error('ends_at')
                        <span class="text-[#f53003] dark:text-[#FF4433]">{{ $message }}</span>
                    @enderror
                </label>

                @error('window')
                    <span class="text-[#f53003] dark:text-[#FF4433] text-sm">{{ $message }}</span>
                @enderror

                <button
                    type="submit"
                    class="inline-block px-5 py-1.5 bg-[#1b1b18] dark:bg-[#eeeeec] border border-black dark:border-[#eeeeec] rounded-sm text-sm leading-normal text-white dark:text-[#1C1C1A]"
                >
                    保存
                </button>
            </form>
        </main>
    </body>
</html>
