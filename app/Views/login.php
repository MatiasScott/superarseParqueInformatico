<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | InfoPark</title>
    <link rel="icon" type="image/png" href="/assets/img/infopark02.png" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="/css/style.css">
    
   
    
    <style>
        /* 🌌 Fondo general de la app con la trinidad de colores pastel y trama técnica */
        .infopark-bg {
            background-color: #f0f4f8; 
            background-image: 
                radial-gradient(at 0% 0%, rgba(134, 239, 172, 0.2) 0px, transparent 55%),    /* Verde Menta */
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.15) 0px, transparent 60%), /* Celeste */
                radial-gradient(at 50% 50%, rgba(37, 99, 235, 0.08) 0px, transparent 70%),    /* Azul Sutil */
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(0deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 32px 32px, 32px 32px;
        }

        /* Tarjeta con opacidad equilibrada para resaltar los sublimados internos */
        .glass-login-card {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>
<body class="infopark-bg min-h-screen flex items-center justify-center p-4 relative overflow-x-hidden font-sans">

    <div class="absolute top-12 left-12 w-80 h-80 bg-emerald-300/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-12 right-12 w-80 h-80 bg-sky-300/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 transition-all duration-300 hover:scale-[1.005]">
        
       
        
        <div class="glass-login-card rounded-3xl shadow-2xl shadow-slate-900/10 border border-white/80 overflow-hidden">
            
            <div class="p-8 text-center relative border-b border-slate-200/60"
                 style="background-image: 
                    radial-gradient(at 0% 0%, rgba(134, 239, 172, 0.25) 0px, transparent 50%),
                    radial-gradient(at 100% 0%, rgba(56, 189, 248, 0.2) 0px, transparent 50%),
                    radial-gradient(at 50% 100%, rgba(37, 99, 235, 0.06) 0px, transparent 70%),
                    linear-gradient(rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0.55));">
                
                <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-blue-600 to-orange-500"></div>
                
                <div class="inline-flex items-center justify-center w-48 h-14 overflow-hidden bg-transparent mb-2">
                    <img src="/assets/img/infopark01.png" 
                         alt="Vista del Programa InfoPark" 
                         class="w-full h-full object-contain">
                </div>
                
                <p class="text-slate-600 font-bold text-[10px] uppercase tracking-widest mt-1 flex items-center justify-center gap-1.5">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Gestión Técnica Institucional
                </p>
            </div>

            <form action="login/autenticar" method="POST" class="p-8 space-y-5 bg-white/85">
                
                <?php if(isset($_GET['error'])): ?>
                    <div class="bg-rose-50 text-rose-600 p-3.5 rounded-xl text-xs flex items-center gap-2.5 border border-rose-100 font-semibold">
                        <i class="ph-bold ph-warning-circle text-lg"></i>
                        <span>Credenciales incorrectas. Intenta de nuevo.</span>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 text-left">Correo Electrónico</label>
                    <div class="relative group">
                        <i class="ph ph-envelope absolute left-4 top-3.5 text-slate-400 text-lg transition-colors group-focus-within:text-blue-600"></i>
                        <input type="email" name="email" required 
                            class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200/80 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition text-slate-800 font-semibold placeholder-slate-400 text-sm shadow-sm"
                            placeholder="usuario@superarse.edu.ec">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 text-left">Contraseña</label>
                    <div class="relative group">
                        <i class="ph ph-lock absolute left-4 top-3.5 text-slate-400 text-lg transition-colors group-focus-within:text-blue-600"></i>
                        
                        <input type="password" id="passwordInput" name="password" required 
                            class="w-full pl-12 pr-12 py-3 bg-white border border-slate-200/80 rounded-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition text-slate-800 font-semibold placeholder-slate-400 text-sm shadow-sm"
                            placeholder="••••••••">
                        
                        <button type="button" onclick="togglePassword()" 
                            class="absolute right-4 top-3 text-slate-400 hover:text-orange-500 transition-colors outline-none p-0.5 rounded-lg focus:ring-2 focus:ring-orange-500/20" 
                            title="Mostrar/Ocultar contraseña">
                            <i id="toggleIcon" class="ph ph-eye text-xl"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-blue-600/15 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2 group mt-3">
                    <span>Iniciar Sesión Interna</span>
                    <i class="ph ph-arrow-right font-bold transition-transform group-hover:translate-x-1"></i>
                </button>
            </form>

            <div class="p-4 bg-slate-50/90 border-t border-slate-100 text-center">
                <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                    © <?= date('Y') ?> InfoPark | Instituto Superarse
                </p>
            </div>
        </div>
        
        <p class="mt-6 text-center text-slate-400 text-xs font-bold hover:text-blue-600 cursor-pointer transition uppercase tracking-wider text-[10px]">
            ¿Problemas de acceso? Contacta al administrador de TIC.
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'ph ph-eye-slash text-xl text-orange-500';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'ph ph-eye text-xl text-slate-400';
            }
        }
    </script>
</body>
</html>