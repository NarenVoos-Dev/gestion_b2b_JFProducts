<!-- Sidebar -->
<div id="sidebarOverlay" onclick="closeSidebar()" class="fixed inset-0 bg-black/40 opacity-0 invisible transition-all duration-400 z-40"></div>

<!-- Sidebar: W-64 compactado -->
<nav id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white/95 backdrop-blur-xl shadow-2xl -translate-x-full transition-transform duration-400 z-50 pt-16">
    <div class="p-4 text-center border-b border-[#028dff]/10">
        <!-- Logo/Avatar -->
        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#0f4db3] to-[#028dff] flex items-center justify-center text-white font-bold text-lg mx-auto mb-3 shadow-xl shadow-[#0f4db3]/30">
            {{ Str::limit(Auth::user()->name, 2, '') }}
        </div>
        
        <!-- Nombre del Usuario (Persona) -->
        <div class="font-bold text-gray-900 text-base mb-0.5">
            {{ Auth::user()->name }}
        </div>
        
        <!-- Razón Social o Rol (Usaremos el nombre del cliente si está disponible)
        <div class="text-[#028dff] text-xs font-medium">
            {{ Auth::user()->client->name ?? 'Usuario de Portal' }}
        </div> -->
    </div>
    
    <!-- Contenedor Principal de Links (para que ocupe el espacio completo) -->
    <div class="py-4 flex flex-col h-[calc(100%-120px)] overflow-y-auto"> 
        <div class="flex-grow space-y-1">
            <!-- SECCIÓN PRINCIPAL -->
            <div class="mb-4">
                <div class="px-4 pb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Principal</div>
                
                <!-- Links de Navegación -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-gray-600 transition-all duration-300 text-sm font-medium hover:bg-[#0f4db3]/5 hover:text-[#0f4db3] hover:translate-x-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
                <a href="{{ route('catalogo') }}" class="flex items-center gap-3 px-4 py-2 text-gray-600 transition-all duration-300 text-sm font-medium hover:bg-[#0f4db3]/5 hover:text-[#0f4db3] hover:translate-x-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    Catálogo
                </a>
            </div>
            
            <!-- SECCIÓN PEDIDOS -->
            <div class="mb-4">
                <div class="px-4 pb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Pedidos</div>
                <a href="{{ route('pedidos.list') }}" class="flex items-center gap-3 px-4 py-2 text-gray-600 transition-all duration-300 text-sm font-medium hover:bg-[#0f4db3]/5 hover:text-[#0f4db3] hover:translate-x-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    Historial de Pedidos
                </a>
                <a href="{{ route('cuentas.pagar') }}" class="flex items-center gap-3 px-4 py-2 text-gray-600 transition-all duration-300 text-sm font-medium hover:bg-[#0f4db3]/5 hover:text-[#0f4db3] hover:translate-x-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    Cuentas por Pagar
                </a>
            </div>
            
            <!-- SECCIÓN HERRAMIENTAS -->
            <div>
                <div class="px-4 pb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">Herramientas</div>
                <a href="#" onclick="toggleCart(); return false;" class="flex items-center gap-3 px-4 py-2 text-gray-600 transition-all duration-300 text-sm font-medium hover:bg-[#0f4db3]/5 hover:text-[#0f4db3] hover:translate-x-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    Ver Carrito
                </a>
                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-2 text-gray-600 transition-all duration-300 text-sm font-medium hover:bg-[#0f4db3]/5 hover:text-[#0f4db3] hover:translate-x-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Mi Perfil
                </a>
            </div>
        </div>
        
        <!-- Bloque de Cerrar Sesión (Fijo en la parte inferior) -->
        <!-- Borde superior limpio y clase 'mt-auto' removida, usando el div padre flex-col -->
        <div class="border-t border-[#028dff]/10 p-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 py-2 text-gray-600 transition-all duration-300 text-sm font-medium hover:bg-red-500/10 hover:text-red-500" title="Cerrar Sesión">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</nav>