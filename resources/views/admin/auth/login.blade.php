<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | {{ config('app.name', 'Tour & Travel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-blue-600 px-8 py-6 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-white/20 flex items-center justify-center text-white text-2xl mb-3">
                    <i class="fa-solid fa-plane-departure"></i>
                </div>
                <h1 class="text-xl font-bold text-white">Tour & Travel Admin</h1>
                <p class="text-blue-100 text-sm mt-1">Sign in with your admin email & password</p>
            </div>
            <form method="POST" action="{{ route('admin.login.post') }}" class="px-8 py-6 space-y-4">
                @csrf
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="admin@tourtravel.com"
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm @error('email') border-red-400 @enderror">
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-lock"></i></span>
                        <input id="password" type="password" name="password" required placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm">
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-slate-600">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600">
                        Remember me
                    </label>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Login to Dashboard
                </button>
                <p class="text-center text-xs text-slate-400 pt-2">
                    Default: <code class="bg-slate-100 px-1.5 py-0.5 rounded">admin@tourtravel.com / Admin@123456</code>
                </p>
            </form>
        </div>
        <p class="text-center text-slate-400 text-xs mt-4">&copy; {{ date('Y') }} Tour & Travel. Secure admin area.</p>
    </div>
</body>
</html>
