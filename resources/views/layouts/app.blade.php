<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    @include('partials.head')
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased">
    @include('partials.navbar')
    @include('partials.flash')

    <main>@yield('content')</main>

    @include('partials.footer')
    @include('partials.scripts')
    @stack('scripts')
</body>
</html>
