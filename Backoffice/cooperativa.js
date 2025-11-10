function initPanelUsuarios(rol, ci_sess){
    const tabsContainer = document.getElementById('tabs');
    const panel = document.getElementById('panel-content');
    const API_USUARIOS = '../Backend/api/usuarios.php';
    const API_COOPERATIVA = '../Backend/api/cooperativa.php';
    const escapeHtml = text => text ? (text+'').replace(/[&<>"'`=\/]/g, s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;','`':'&#96;','=':'&#61;'}[s])) : '';
    const addTab = (id,label,callback,isDefault=false)=>{
        const btn = document.createElement('button');
        btn.textContent = label; btn.id = 'tab-'+id;
        btn.onclick = ()=>{
            document.querySelectorAll('.tabs button').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            callback();
        };
        tabsContainer.appendChild(btn);
        if(isDefault) btn.click();
    };
    const fetchJSON = async(url,options)=>{
        try{
            const res = await fetch(url,options);
            const text = await res.text();
            try { return JSON.parse(text); }
            catch(e){ throw new Error(text); }
        }catch(e){
            panel.innerHTML=`<div class="error">Error: ${escapeHtml(e.message)}</div>`;
            throw e;
        }
    };

    async function loadAspirantes(){
        panel.innerHTML='Cargando aspirantes...';
        const aspirantes = await fetchJSON(`${API_USUARIOS}?accion=aspirantes`);
        if(!Array.isArray(aspirantes) || aspirantes.length===0){
            panel.innerHTML='<p>No hay aspirantes registrados</p>'; return;
        }
        let html = `<h2>Aspirantes</h2><div class="table-container"><table>
            <thead><tr><th>CI</th><th>Nombre</th><th>Email</th><th>Tel</th><th>Fecha Solicitud</th><th>Acciones</th></tr></thead><tbody>`;
        aspirantes.forEach(a=>{
            html += `<tr>
                <td>${escapeHtml(a.CIaspi)}</td>
                <td>${escapeHtml(a.PnomA+' '+a.PapeA)}</td>
                <td>${escapeHtml(a.EmailA)}</td>
                <td>${escapeHtml(a.TelA)}</td>
                <td>${escapeHtml(a.FchSoli)}</td>
                <td>
                    <button class="btn-aprobar" data-ci="${escapeHtml(a.CIaspi)}">Aprobar</button>
                    <button class="btn-eliminar" data-ci="${escapeHtml(a.CIaspi)}">Eliminar</button>
                </td>
            </tr>`;
        });
        html += '</tbody></table></div>'; panel.innerHTML = html;
        document.querySelectorAll('.btn-aprobar').forEach(b=>b.onclick = ()=>aprobarAspirante(b.dataset.ci));
        document.querySelectorAll('.btn-eliminar').forEach(b=>b.onclick = ()=>eliminarAspirante(b.dataset.ci));
    }

    async function loadSocios(){
        panel.innerHTML='Cargando socios...';
        const socios = await fetchJSON(`${API_USUARIOS}?accion=socios`);
        if(!Array.isArray(socios) || socios.length===0){
            panel.innerHTML='<p>No hay socios registrados</p>'; return;
        }
        let html = `<h2>Socios</h2><div class="table-container"><table>
            <thead><tr><th>CI</th><th>Nombre</th><th>Email</th><th>Tel</th><th>Estado</th><th>Ingreso</th><th>Unidad</th><th>Acciones</th></tr></thead><tbody>`;
        socios.forEach(s=>{
            html += `<tr>
                <td>${escapeHtml(s.CIsoc)}</td>
                <td>${escapeHtml(s.PnomS+' '+s.PapeS)}</td>
                <td>${escapeHtml(s.EmailS)}</td>
                <td>${escapeHtml(s.TelS)}</td>
                <td>${escapeHtml(s.EstadoSoc)}</td>
                <td>${escapeHtml(s.FchIngr)}</td>
                <td>${escapeHtml(s.Unidad||'--')}</td>
                <td>`;
            html += `<button class="btn-elim-socio" data-ci="${escapeHtml(s.CIsoc)}">Eliminar</button> `;
            html += `<button class="btn-toggle-estado" data-ci="${escapeHtml(s.CIsoc)}" data-estado="${escapeHtml(s.EstadoSoc)}">${s.EstadoSoc==='Activo'?'Desactivar':'Activar'}</button> `;
            if(rol.toLowerCase()==='admin'){
                html += `<button class="btn-asignar-unidad" data-ci="${escapeHtml(s.CIsoc)}">Asignar unidad</button>`;
            }
            html += `</td></tr>`;
        });
        html += '</tbody></table></div>'; panel.innerHTML = html;
        document.querySelectorAll('.btn-elim-socio').forEach(b=>b.onclick = ()=>eliminarSocio(b.dataset.ci));
        document.querySelectorAll('.btn-toggle-estado').forEach(b=>{
            b.onclick = ()=>cambiarEstadoSocio(b.dataset.ci, b.dataset.estado === 'Activo' ? 'Inactivo' : 'Activo');
        });
        document.querySelectorAll('.btn-asignar-unidad').forEach(b=>b.onclick = ()=>openAssignUnidadModal(b.dataset.ci));
    }

    async function loadUnidades(){
        panel.innerHTML='Cargando unidades...';
        const unidades = await fetchJSON(`${API_COOPERATIVA}?accion=unidades`);
        let html = `<h2>Unidades Habitacionales</h2>`;
        html += `<form id="formAddUnidad">
                    <input type="text" id="inputNomLote" placeholder="Nombre Lote / Unidad" required>
                    <select id="selectEstadoUni">
                        <option value="Por empezar">Por empezar</option>
                        <option value="En construcción">En construcción</option>
                        <option value="Finalizada">Finalizada</option>
                    </select>
                    <button type="submit">Agregar unidad</button>
                </form>`;
        if(!Array.isArray(unidades) || unidades.length===0){
            html += '<p>No hay unidades registradas</p>';
            panel.innerHTML = html;
        } else {
            html += `<div class="table-container"><table>
            <thead><tr><th>ID</th><th>Nombre Lote</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>`;
            unidades.forEach(u=>{
                html += `<tr>
                    <td>${u.IDUni}</td>
                    <td>${escapeHtml(u.NomLote)}</td>
                    <td>${escapeHtml(u.EstadoUni)}</td>
                    <td>
                        <button class="btn-estado-unidad" data-id="${u.IDUni}" data-estado="Por empezar">Por empezar</button>
                        <button class="btn-estado-unidad" data-id="${u.IDUni}" data-estado="En construcción">En construcción</button>
                        <button class="btn-estado-unidad" data-id="${u.IDUni}" data-estado="Finalizada">Finalizada</button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table></div>';
            panel.innerHTML = html;
            document.querySelectorAll('.btn-estado-unidad').forEach(b=>b.onclick = ()=>cambiarEstadoUnidad(b.dataset.id,b.dataset.estado));
        }
        const form = document.getElementById('formAddUnidad');
        form.onsubmit = async (e)=>{
            e.preventDefault();
            const nom = document.getElementById('inputNomLote').value.trim();
            const estado = document.getElementById('selectEstadoUni').value;
            if(!nom) return alert('Ingrese nombre de unidad');
            const res = await fetch(`${API_COOPERATIVA}?accion=add_unidad`, {
                method:'POST', body:new URLSearchParams({nom,estado})
            });
            const data = await res.json();
            alert(data.mensaje||data.error);
            loadUnidades();
        };
    }

    function createModal(html){
        const modal = document.createElement('div');
        modal.style.position='fixed';
        modal.style.left='0';
        modal.style.top='0';
        modal.style.width='100%';
        modal.style.height='100%';
        modal.style.background='rgba(0,0,0,0.4)';
        modal.style.display='flex';
        modal.style.alignItems='center';
        modal.style.justifyContent='center';
        modal.innerHTML = `<div style="background:#fff;padding:16px;max-width:600px;width:90%;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,0.2);">${html}</div>`;
        modal.onclick = (e)=>{ if(e.target===modal) document.body.removeChild(modal); };
        document.body.appendChild(modal);
        return modal;
    }

    async function openAssignUnidadModal(ci){
        const unidades = await fetchJSON(`${API_COOPERATIVA}?accion=unidades`);
        let options = `<option value="">-- Ninguna (manual) --</option>`;
        unidades.forEach(u=> options += `<option value="${u.IDUni}">${escapeHtml(u.NomLote)} (${escapeHtml(u.EstadoUni)})</option>`);
        const html = `<h3>Asignar unidad a ${escapeHtml(ci)}</h3>
            <form id="formAssignUnidad">
                <label>Elegir unidad existente</label><br>
                <select id="selectAssignUnidad">${options}</select><br><br>
                <label>O ingresar manualmente</label><br>
                <input type="text" id="inputManualUnidad" placeholder="Unidad (ej. 101)"><br>
                <input type="text" id="inputManualDireccion" placeholder="Dirección"><br>
                <select id="selectManualEstado">
                    <option value="Por empezar">Por empezar</option>
                    <option value="En construcción">En construcción</option>
                    <option value="Finalizada">Finalizada</option>
                </select><br><br>
                <button type="submit">Guardar asignación</button>
                <button type="button" id="btnCancelAssign">Cancelar</button>
            </form>`;
        const modal = createModal(html);
        const form = modal.querySelector('#formAssignUnidad');
        modal.querySelector('#btnCancelAssign').onclick = ()=>document.body.removeChild(modal);
        form.onsubmit = async (e)=>{
            e.preventDefault();
            const idUni = form.querySelector('#selectAssignUnidad').value;
            const manualUni = form.querySelector('#inputManualUnidad').value.trim();
            const manualDir = form.querySelector('#inputManualDireccion').value.trim();
            const manualEstado = form.querySelector('#selectManualEstado').value;
            const fd = new FormData();
            fd.append('ci', ci);
            if(idUni){
                fd.append('iduni', idUni);
            } else if(manualUni && manualDir){
                fd.append('manual_unidad', manualUni);
                fd.append('manual_direccion', manualDir);
                fd.append('manual_estado', manualEstado);
            } else {
                return alert('Seleccione unidad existente o complete los campos manuales (unidad y dirección).');
            }
            const res = await fetch(`${API_COOPERATIVA}?accion=asignar_unidad`, { method:'POST', body: fd });
            const data = await res.json();
            alert(data.mensaje||data.error);
            document.body.removeChild(modal);
            loadSocios();
        };
    }

    async function loadAsambleas(){
        panel.innerHTML='Cargando asambleas...';
        const asamb = await fetchJSON(`${API_COOPERATIVA}?accion=asambleas`);
        let html = `<h2>Asambleas</h2>`;
        if(rol.toLowerCase()==='admin'){
            html += `<form id="formAddAsamblea" enctype="multipart/form-data">
                        <input type="date" id="inputFchAsam" required>
                        <input type="text" id="inputOrden" placeholder="Orden del día" required>
                        <button type="submit">Programar asamblea</button>
                    </form>`;
        }
        html += `<div class="table-container"><table>
            <thead><tr><th>ID</th><th>Fecha</th><th>Orden</th><th>Acta</th><th>Acciones</th></tr></thead><tbody>`;
        if(Array.isArray(asamb)){
            asamb.forEach(a=>{
                const link = a.Acta ? (a.Acta.startsWith('/') || a.Acta.includes('Backend') ? a.Acta : `../Backend/actas/${a.Acta}`) : '';
                html += `<tr>
                    <td>${a.id}</td>
                    <td>${escapeHtml(a.FchAsam)}</td>
                    <td>${escapeHtml(a.Orden||'')}</td>
                    <td>${ a.Acta ? `<a href="${link}" target="_blank">Ver Acta</a>` : '--' }</td>
                    <td>`;
                if(rol.toLowerCase()==='admin'){
                    if(!a.Acta) html += `<button class="btn-subir-acta" data-id="${a.id}">Subir acta</button> `;
                    html += `<button class="btn-eliminar-asamblea" data-id="${a.id}">Eliminar</button>`;
                }
                html += `</td></tr>`;
            });
        }
        html += '</tbody></table></div>';
        panel.innerHTML = html;
        if(rol.toLowerCase()==='admin'){
            const form = document.getElementById('formAddAsamblea');
            form.onsubmit = async (e)=>{
                e.preventDefault();
                const fecha = document.getElementById('inputFchAsam').value;
                const orden = document.getElementById('inputOrden').value.trim();
                if(!fecha || !orden) return alert('Complete fecha y orden');
                const fd = new FormData();
                fd.append('fecha', fecha);
                fd.append('orden', orden);
                const res = await fetch(`${API_COOPERATIVA}?accion=subir_asamblea`, { method:'POST', body: fd });
                const data = await res.json();
                alert(data.mensaje||data.error);
                loadAsambleas();
            };
            document.querySelectorAll('.btn-subir-acta').forEach(b=>b.onclick = ()=>openSubirActaModal(b.dataset.id));
            document.querySelectorAll('.btn-eliminar-asamblea').forEach(b=>b.onclick = ()=>eliminarAsamblea(b.dataset.id));
        }
    }

    function openSubirActaModal(id){
        const html = `<h3>Subir acta para asamblea ${escapeHtml(id)}</h3>
            <form id="formSubirActa" enctype="multipart/form-data">
                <input type="file" id="inputActaFile" accept="application/pdf" required><br><br>
                <button type="submit">Subir acta</button>
                <button type="button" id="btnCancelActa">Cancelar</button>
            </form>`;
        const modal = createModal(html);
        modal.querySelector('#btnCancelActa').onclick = ()=>document.body.removeChild(modal);
        modal.querySelector('#formSubirActa').onsubmit = async (e)=>{
            e.preventDefault();
            const file = modal.querySelector('#inputActaFile').files[0];
            if(!file) return alert('Adjunte PDF');
            const fd = new FormData();
            fd.append('id', id);
            fd.append('acta', file);
            const res = await fetch(`${API_COOPERATIVA}?accion=agregar_acta`, { method:'POST', body: fd });
            const data = await res.json();
            alert(data.mensaje||data.error);
            document.body.removeChild(modal);
            loadAsambleas();
        };
    }

    async function eliminarAsamblea(id){
        if(!confirm('Eliminar asamblea '+id+'?')) return;
        const res = await fetch(`${API_COOPERATIVA}?accion=eliminar_asamblea`, { method:'POST', body: new URLSearchParams({id}) });
        const data = await res.json();
        alert(data.mensaje||data.error);
        loadAsambleas();
    }

    async function loadHoras(){
        panel.innerHTML='Cargando horas...';
        const horas = await fetchJSON(`${API_COOPERATIVA}?accion=horas`);
        let html = `<h2>Horas de Trabajo</h2>`;
        if(!Array.isArray(horas) || horas.length===0){
            html += '<p>No hay horas registradas</p>';
        } else {
            html += `<div class="table-container"><table>
            <thead><tr><th>ID</th><th>Socio</th><th>Horas</th><th>Fecha</th></tr></thead><tbody>`;
            horas.forEach(h=>{
                html += `<tr>
                    <td>${h.id}</td>
                    <td>${escapeHtml(h.PnomS+' '+h.PapeS)}</td>
                    <td>${h.Horas}</td>
                    <td>${h.Fecha}</td>
                </tr>`;
            });
            html += '</tbody></table></div>';
        }
        if(rol.toLowerCase()!=='admin'){
            html += `
                <h3>Registrar horas trabajadas</h3>
                <input type="number" id="inputHoras" placeholder="Horas" min="0" step="0.5">
                <button id="btnSubirHoras">Subir</button>
            `;
            panel.innerHTML = html;
            document.getElementById('btnSubirHoras').onclick = async ()=>{
                const horasVal = document.getElementById('inputHoras').value;
                if(!horasVal) return alert('Ingrese horas');
                const res = await fetch(`${API_COOPERATIVA}?accion=subir_horas`, {
                    method:'POST', body:new URLSearchParams({horas: horasVal})
                });
                const data = await res.json();
                alert(data.mensaje || data.error);
                loadHoras();
            };
        } else panel.innerHTML = html;
    }

    async function loadComprobantes(){
        panel.innerHTML='Cargando comprobantes...';
        const comps = await fetchJSON(`${API_COOPERATIVA}?accion=comprobantes`);
        let html = `<h2>Comprobantes</h2>`;
        if(rol.toLowerCase()!=='admin'){
            html += `<form id="formSubirComp" enctype="multipart/form-data">
                        <input type="text" id="inputComprobanteDesc" placeholder="Descripción" required>
                        <input type="file" id="inputComprobanteFile" accept="application/pdf" required>
                        <button type="submit">Subir comprobante</button>
                    </form>`;
        }
        html += `<div class="table-container"><table>
            <thead><tr><th>ID</th><th>Socio</th><th>Descripción</th><th>Archivo</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody>`;
        if(Array.isArray(comps)){
            comps.forEach(c=>{
                const link = c.Archivo ? (c.Archivo.startsWith('/') || c.Archivo.includes('Backend') ? c.Archivo : `../Backend/comprobantes/${c.Archivo}`) : '';
                const archivoLink = c.Archivo ? `<a href="${link}" target="_blank">Ver PDF</a>` : '';
                html += `<tr>
                    <td>${c.id}</td>
                    <td>${escapeHtml(c.PnomS+' '+c.PapeS)}</td>
                    <td>${escapeHtml(c.Descripcion)}</td>
                    <td>${archivoLink}</td>
                    <td>${escapeHtml(c.Estado)}</td>
                    <td>${escapeHtml(c.Fecha)}</td>`;
                if(rol.toLowerCase()==='admin' && c.Estado==='Pendiente'){
                    html += `<td>
                        <button onclick="aprobarComprobante(${c.id},'Aprobado')">Aprobar</button>
                        <button onclick="aprobarComprobante(${c.id},'Rechazado')">Rechazar</button>
                    </td>`;
                } else html += '<td></td>';
                html += '</tr>';
            });
        }
        html += '</tbody></table></div>';
        panel.innerHTML = html;
        if(rol.toLowerCase()!=='admin'){
            const form = document.getElementById('formSubirComp');
            form.onsubmit = async (e)=>{
                e.preventDefault();
                const desc = document.getElementById('inputComprobanteDesc').value.trim();
                const file = document.getElementById('inputComprobanteFile').files[0];
                if(!desc || !file) return alert('Complete todos los campos y adjunte PDF');
                const fd = new FormData();
                fd.append('descripcion', desc);
                fd.append('archivo', file);
                const res = await fetch(`${API_COOPERATIVA}?accion=subir_comprobante`, { method:'POST', body: fd });
                const data = await res.json();
                alert(data.mensaje||data.error);
                loadComprobantes();
            };
        }
    }

    window.aprobarAspirante = async ci=>{
        const res = await fetch(`${API_USUARIOS}?accion=aprobar_aspirante`, {
            method:'POST', body:new URLSearchParams({ci})
        });
        const data = await res.json();
        alert(data.mensaje||data.error);
        loadAspirantes(); loadSocios();
    };

    window.eliminarAspirante = async ci=>{
        if(!confirm('Eliminar aspirante '+ci+'?')) return;
        const res = await fetch(`${API_USUARIOS}?accion=eliminar_aspirante`, {
            method:'POST', body:new URLSearchParams({ci})
        });
        const data = await res.json();
        alert(data.mensaje||data.error);
        loadAspirantes();
    };

    window.eliminarSocio = async ci=>{
        if(!confirm('Eliminar socio '+ci+'?')) return;
        const res = await fetch(`${API_USUARIOS}?accion=eliminar_socio`, {
            method:'POST', body:new URLSearchParams({ci})
        });
        const data = await res.json();
        alert(data.mensaje||data.error);
        loadSocios();
    };

    window.cambiarEstadoSocio = async(ci,estado)=>{
        const res = await fetch(`${API_USUARIOS}?accion=cambiar_estado_socio`, {
            method:'POST', body:new URLSearchParams({ci,estado})
        });
        const data = await res.json();
        alert(data.mensaje||data.error);
        loadSocios();
    };

    window.aprobarComprobante = async(id,estado)=>{
        const res = await fetch(`${API_COOPERATIVA}?accion=aprobar_comprobante`, {
            method:'POST', body:new URLSearchParams({id,estado})
        });
        const data = await res.json();
        alert(data.mensaje||data.error);
        loadComprobantes();
    };

    window.cambiarEstadoUnidad = async(id,estado)=>{
        const res = await fetch(`${API_COOPERATIVA}?accion=cambiar_estado_unidad`, {
            method:'POST', body:new URLSearchParams({id,estado})
        });
        const data = await res.json();
        alert(data.mensaje||data.error);
        loadUnidades();
    };

    async function loadUnidadSocio(){
        panel.innerHTML='Cargando unidad...';
        try{
            const u = await fetchJSON(`${API_COOPERATIVA}?accion=mi_unidad`);
            panel.innerHTML = `<h2>Mi Unidad Habitacional</h2>
                <p><strong>Unidad:</strong> ${escapeHtml(u.Unidad||'Sin asignar')}</p>
                <p><strong>Dirección:</strong> ${escapeHtml(u.Direccion||'No definida')}</p>
                <p><strong>Estado:</strong> ${escapeHtml(u.Estado||'')}</p>`;
        }catch(e){
            panel.innerHTML = `<p>Error al obtener unidad</p>`;
        }
    }

    if(rol.toLowerCase() === 'admin'){
        addTab('socios','Socios',loadSocios,true);
        addTab('aspirantes','Aspirantes',loadAspirantes);
        addTab('unidades','Unidades',loadUnidades);
        addTab('asambleas','Asambleas',loadAsambleas);
        addTab('horas','Horas',loadHoras);
        addTab('comprobantes','Comprobantes',loadComprobantes);
    } else {
        addTab('unidad','Mi Unidad',loadUnidadSocio,true);
        addTab('comprobantes','Comprobantes',loadComprobantes);
        addTab('asambleas','Asambleas',loadAsambleas);
        addTab('horas','Horas',loadHoras);
    }
    
}
const toggle = document.getElementById("theme-toggle");
const body = document.body;

if (localStorage.getItem("theme") === "dark") {
  body.classList.add("dark-mode");
  if (toggle) toggle.textContent = "☀️";
}

if (toggle) {
  toggle.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    const isDark = body.classList.contains("dark-mode");
    toggle.textContent = isDark ? "☀️" : "🌙";
    localStorage.setItem("theme", isDark ? "dark" : "light");
  });
}
