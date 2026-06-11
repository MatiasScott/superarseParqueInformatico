let contadorFilas = 1;

function openModal() { 
    document.getElementById('modalComponente').classList.remove('hidden'); 
}

function closeModal() { 
    document.getElementById('modalComponente').classList.add('hidden'); 
}

function agregarFilaComponente() {
    const contenedor = document.getElementById('contenedor-componentes');
    const nuevaFila = document.createElement('div');
    
    nuevaFila.className = "fila-componente grid grid-cols-1 md:grid-cols-12 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 relative items-end transition-all duration-300 transform translate-y-2 opacity-0";
    nuevaFila.id = `fila-${contadorFilas}`;
    
    // Generamos las opciones de equipos usando el array global mapeado de forma segura
    let opcionesEquipos = `<option value="">-- Seleccione Equipo Destino --</option>`;
    if (typeof listaEquiposGlobal !== 'undefined' && Array.isArray(listaEquiposGlobal)) {
        opcionesEquipos += listaEquiposGlobal.map(eq => {
            const nombreSafe = eq.nombre.replace(/"/g, '&quot;');
            const serieSafe = eq.serie ? eq.serie.replace(/"/g, '&quot;') : 'S/N';
            return `<option value="${eq.id}">${nombreSafe} (${serieSafe})</option>`;
        }).join('');
    }
    
    nuevaFila.innerHTML = `
        <div class="md:col-span-3">
            <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Asignar a Equipo *</label>
            <select name="componentes[${contadorFilas}][equipo_id]" required class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                ${opcionesEquipos}
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Tipo *</label>
            <select name="componentes[${contadorFilas}][tipo]" required class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="RAM">RAM</option>
                <option value="Disco Duro">Disco Duro</option>
                <option value="SSD">SSD</option>
                <option value="Procesador">Procesador</option>
                <option value="Tarjeta de Video">Tarjeta de Video</option>
                <option value="Batería">Batería</option>
                <option value="Cargador">Cargador</option>
                <option value="Otro">Otro</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Marca / Modelo</label>
            <input type="text" name="componentes[${contadorFilas}][marca_modelo]" placeholder="Ej: Kingston A400" class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Capacidad / Detalle *</label>
            <input type="text" name="componentes[${contadorFilas}][descripcion]" placeholder="Ej: 16GB DDR4 / 480GB" required class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="text-xs font-bold text-slate-600 uppercase tracking-wider">Estado *</label>
            <select name="componentes[${contadorFilas}][estado]" required class="w-full mt-1.5 p-3 bg-white border border-slate-200 rounded-xl text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="Bueno">Bueno</option>
                <option value="Regular">Regular</option>
                <option value="Dañado">Dañado</option>
            </select>
        </div>

        <div class="md:col-span-1 flex justify-center pb-1">
            <button type="button" onclick="eliminarFilaComponente(${contadorFilas})" class="p-3 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-all duration-200 text-xl shadow-sm">
                <i class="ph ph-trash"></i>
            </button>
        </div>
    `;
    
    contenedor.appendChild(nuevaFila);
    
    setTimeout(() => {
        nuevaFila.classList.remove('translate-y-2', 'opacity-0');
    }, 50);
    
    contadorFilas++;
    contenedor.scrollTop = contenedor.scrollHeight;
}

function eliminarFilaComponente(idFila) {
    const fila = document.getElementById(`fila-${idFila}`);
    if (fila) { 
        fila.classList.add('opacity-0', 'scale-95');
        setTimeout(() => { fila.remove(); }, 200);
    }
}

function filtrarPorEquipo() {
    const idEquipo = document.getElementById('filtro-equipo').value;
    if (idEquipo) {
        window.location.href = `/superarseParqueInformatico/public/componentes?equipo_id=${idEquipo}`;
    } else {
        window.location.href = '/superarseParqueInformatico/public/componentes';
    }
}