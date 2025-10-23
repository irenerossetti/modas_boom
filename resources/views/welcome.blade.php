@php
if (auth()->check()) {
    $user = auth()->user();
    if ($user->id_rol == 1) {
        $redirectUrl = route('dashboard');
    } elseif ($user->id_rol == 2) {
        $redirectUrl = '/empleado-dashboard';
    } else {
        $redirectUrl = null; // Clients stay on landing
    }
} else {
    $redirectUrl = null;
}
@endphp

@if($redirectUrl)
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Modas Boom - Redirigiendo...</title>
    </head>
    <body>
        <script>
            window.location.href = '{{ $redirectUrl }}';
        </script>
        <p>Redirigiendo...</p>
    </body>
</html>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Modas Boom - Colección de Moda</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-boom-cream-200">
        <!-- Header -->
        <header class="fixed top-0 left-0 right-0 bg-boom-cream-200 shadow-md z-50">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Logo y título a la izquierda -->
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/logo_boom.jpg') }}" alt="Modas Boom Logo" class="h-12 w-auto">
                        <h1 class="text-2xl font-bold text-boom-text-dark">Modas Boom</h1>
                    </div>

                    <!-- Navegación central -->
                    <nav class="hidden md:flex items-center gap-8">
                        <a href="#inicio" class="nav-link text-boom-text-dark hover:text-boom-red-title transition-colors font-medium" data-section="inicio">Colección</a>
                        <a href="#coleccion" class="nav-link text-boom-text-dark hover:text-boom-red-title transition-colors font-medium" data-section="coleccion">Formal</a>
                        <a href="#informal" class="nav-link text-boom-text-dark hover:text-boom-red-title transition-colors font-medium" data-section="informal">Informal</a>
                        <a href="#contacto" class="nav-link text-boom-text-dark hover:text-boom-red-title transition-colors font-medium" data-section="contacto">Contacto</a>
                    </nav>

                    <!-- Botones de autenticación a la derecha -->
                    <div class="flex items-center gap-4">
                        @auth
                            <span class="text-boom-text-dark font-medium">Hola, {{ auth()->user()->nombre }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-block px-5 py-2 bg-boom-red-report hover:bg-boom-red-title text-white rounded-lg text-sm leading-normal transition-colors font-medium">
                                    Cerrar Sesión
                                </button>
                            </form>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="inline-block px-5 py-2 bg-boom-red-report hover:bg-boom-red-title text-white rounded-lg text-sm leading-normal transition-colors font-medium"
                            >
                                Iniciar Sesión
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-block px-5 py-2 text-boom-text-dark border-0 rounded-lg text-sm leading-normal transition-colors"
                                >
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="w-full max-w-7xl mx-auto px-6 py-8 pt-28">
                <!-- Hero Section -->
                <section id="inicio" class="text-center mb-20">
                    <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-2xl p-12 mb-12 shadow-lg border-0">
                        <h2 class="text-5xl font-bold text-boom-text-dark mb-6">Descubre Nuestra Colección</h2>
                        <p class="text-xl text-boom-text-medium mb-8 max-w-3xl mx-auto leading-relaxed">
                            Descubre nuestra exclusiva colección de moda formal e informal.
                            Diseños únicos que combinan elegancia, comodidad y estilo contemporáneo.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            @auth
                            @endauth
                        </div>
                    </div>
                </section>

                <!-- Colección Formal -->
                <section id="coleccion" class="mb-20">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-boom-text-dark mb-4">Colección Formal</h2>
                        <p class="text-lg text-boom-text-medium max-w-2xl mx-auto">
                            Elegancia y sofisticación para ocasiones especiales.
                            Diseños que transmiten confianza y profesionalismo.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-8">
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Formal/Generated Image October 02, 2025 - 10_03AM.png') }}" alt="Formal 1" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Traje Ejecutivo</h3>
                                <p class="text-boom-text-medium text-sm">Elegancia clásica para el mundo corporativo</p>
                            </div>
                        </div>
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Formal/Generated Image October 02, 2025 - 10_27AM.png') }}" alt="Formal 2" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Vestido de Noche</h3>
                                <p class="text-boom-text-medium text-sm">Sophistication para eventos especiales</p>
                            </div>
                        </div>
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Formal/Generated Image October 02, 2025 - 10_29AM.png') }}" alt="Formal 3" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Blazer Moderno</h3>
                                <p class="text-boom-text-medium text-sm">Estilo contemporáneo con toque clásico</p>
                            </div>
                        </div>
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Formal/Generated Image October 02, 2025 - 10_37AM.png') }}" alt="Formal 4" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Conjunto Ejecutivo</h3>
                                <p class="text-boom-text-medium text-sm">Profesionalismo y comodidad</p>
                            </div>
                        </div>
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Formal/Generated Image October 02, 2025 - 10_44AM.png') }}" alt="Formal 5" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Traje de Ceremonia</h3>
                                <p class="text-boom-text-medium text-sm">Perfección para ocasiones formales</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Colección Informal -->
                <section id="informal" class="mb-20">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-bold text-boom-text-dark mb-4">Colección Informal</h2>
                        <p class="text-lg text-boom-text-medium max-w-2xl mx-auto">
                            Comodidad y estilo para el día a día.
                            Diseños versátiles que se adaptan a tu personalidad.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-8">
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Informal/Generated Image October 02, 2025 - 10_06AM.png') }}" alt="Informal 1" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Casual Chic</h3>
                                <p class="text-boom-text-medium text-sm">Estilo urbano con toque elegante</p>
                            </div>
                        </div>
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Informal/Generated Image October 02, 2025 - 10_23AM.png') }}" alt="Informal 2" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Street Style</h3>
                                <p class="text-boom-text-medium text-sm">Tendencias urbanas modernas</p>
                            </div>
                        </div>
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Informal/Generated Image October 02, 2025 - 10_40AM.png') }}" alt="Informal 3" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Boho Casual</h3>
                                <p class="text-boom-text-medium text-sm">Libertad y comodidad expresiva</p>
                            </div>
                        </div>
                        <div class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow group border-0">
                            <div class="aspect-[3/4] overflow-hidden">
                                <img src="{{ asset('images/editados/Informal/Generated Image September 22, 2025 - 6_44PM.png') }}" alt="Informal 4" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="p-6">
                                <h3 class="font-semibold text-boom-text-dark mb-2">Weekend Vibes</h3>
                                <p class="text-boom-text-medium text-sm">Relajado pero con estilo</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Call to Action Section -->
                <section class="bg-boom-cream-100 dark:bg-boom-cream-600 rounded-2xl p-12 text-center shadow-lg border-0">
                    <h2 class="text-3xl font-bold text-boom-text-dark mb-4">¿Listo para renovar tu guardarropa?</h2>
                    <p class="text-lg text-boom-text-medium mb-8 max-w-2xl mx-auto">
                        Únete a nuestra comunidad y descubre las últimas tendencias en moda.
                        Crea tu cuenta y accede a ofertas exclusivas.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @auth
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-boom-red-report hover:bg-boom-red-title text-white rounded-xl text-lg font-semibold transition-colors shadow-md">
                                    Crear Cuenta Gratis
                                </a>
                            @endif
                            <a href="{{ route('login') }}" class="inline-block px-8 py-4 text-boom-text-dark border-0 rounded-xl text-lg font-semibold transition-colors">
                                Iniciar Sesión
                            </a>
                        @endauth
                    </div>
                </section>
            </main>
        </div>

        <!-- Footer -->
        <footer id="contacto" class="w-full mt-16 py-8 border-t border-boom-cream-400">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <p class="text-boom-text-medium">
                    © 2025 Modas Boom. Todos los derechos reservados.
                </p>
            </div>
        </footer>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif>

        <style>
            .nav-link.active {
                color: #d12800;
                text-shadow: 0 0 12px rgba(209, 40, 0, 0.4);
                font-weight: 700;
                background-color: rgba(245, 48, 3, 0.1);
                padding: 4px 8px;
                border-radius: 6px;
                transition: all 0.3s ease;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const navLinks = document.querySelectorAll('.nav-link');
                const sections = document.querySelectorAll('section[id]');

                function updateActiveLink() {
                    const scrollPosition = window.scrollY + 150; // Offset for header

                    // Special handling for inicio section (top of page)
                    if (scrollPosition < 300) {
                        navLinks.forEach(link => {
                            link.classList.remove('active');
                            if (link.getAttribute('data-section') === 'inicio') {
                                link.classList.add('active');
                            }
                        });
                        return;
                    }

                    sections.forEach(section => {
                        const sectionTop = section.offsetTop;
                        const sectionHeight = section.offsetHeight;
                        const sectionId = section.getAttribute('id');

                        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                            navLinks.forEach(link => {
                                link.classList.remove('active');
                                if (link.getAttribute('data-section') === sectionId) {
                                    link.classList.add('active');
                                }
                            });
                        }
                    });
                }

                // Update on scroll
                window.addEventListener('scroll', updateActiveLink);

                // Update on page load
                updateActiveLink();

                // Smooth scrolling for nav links
                navLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('data-section');
                        const targetSection = document.getElementById(targetId);

                        if (targetSection) {
                            const offsetTop = targetSection.offsetTop - 100; // Account for fixed header
                            window.scrollTo({
                                top: offsetTop,
                                behavior: 'smooth'
                            });
                        }
                    });
                });
            });
        </script>
    </body>
    </html>
@endif
