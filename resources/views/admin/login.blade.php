<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
            @csrf
            <div>
                <input name="email" type="email" required placeholder="Email" value="{{ old('email') }}"
                    class="w-full px-4 py-3 rounded-lg bg-zinc-900 border border-zinc-800 text-white text-sm placeholder-zinc-600 focus:outline-none focus:border-zinc-600" />
            </div>
            <div>
                <input name="password" type="password" required placeholder="Password"
                    class="w-full px-4 py-3 rounded-lg bg-zinc-900 border border-zinc-800 text-white text-sm placeholder-zinc-600 focus:outline-none focus:border-zinc-600" />
            </div>
            @if($errors->any())
                <p class="text-xs text-red-400">{{ $errors->first() }}</p>
            @endif
            <button type="submit" class="w-full py-3 text-sm font-medium text-zinc-400 bg-zinc-900 border border-zinc-800 rounded-lg hover:bg-zinc-800 transition-colors">
                →
            </button>
        </form>
    </div>
</body>
</html>
