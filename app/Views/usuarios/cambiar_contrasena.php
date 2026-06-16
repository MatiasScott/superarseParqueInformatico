<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 to-blue-600 p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-400 rounded-2xl mb-4 shadow-lg shadow-blue-500/30">
                <i class="ph ph-lock-key-open text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">Cambio de contraseña obligatorio</h1>
            <p class="text-slate-200 text-sm mt-2">Por seguridad, debes actualizar tu contraseña antes de continuar.</p>
        </div>

        <div class="p-8">
            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-100 p-4 text-sm text-rose-700">
                    <?php if ($_GET['error'] === '1'): ?>
                        Las contraseñas no coinciden o están vacías. Intenta nuevamente.
                    <?php else: ?>
                        Ocurrió un error. Vuelve a intentarlo.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form action="/cambiar-contrasena/guardar" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nueva contraseña</label>
                    <input type="password" name="new_password" required minlength="8"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                        placeholder="Ingresa tu nueva contraseña">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Confirmar contraseña</label>
                    <input type="password" name="confirm_password" required minlength="8"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition"
                        placeholder="Repite la contraseña anterior">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-600/20 transition-all transform active:scale-[0.98]">
                    Guardar contraseña
                </button>
            </form>

            <div class="mt-6 text-sm text-slate-500 text-center">
                Por tu seguridad, no podrás continuar hasta que completes este cambio.
            </div>
        </div>
    </div>
</div>
