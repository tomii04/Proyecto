function initPanelUsuarios(rol, ci_sess){
    const tabsContainer = document.getElementById('tabs');
    const panel = document.getElementById('panel-content');

    const API_USUARIOS = 'api/usuarios.php';
    const API_COOPERATIVA = 'api/cooperativa.php';

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

    // ================= ADMIN FUNCTIONS =================
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
                    <button onclick="aprobarAspirante('${escapeHtml(a.CIaspi)}')">Aprobar</button>
                    <button onclick="eliminarAspirante('${escapeHtml(a.CIaspi)}')">Eliminar</button>
                </td>
            </tr>`;
        });
        html += '</tbody></table></div>'; panel.innerHTML = html;
    }

    async function loadSocios(){
        panel.innerHTML='Cargando socios...';
        const socios = await fetchJSON(`${API_USUARIOS}?accion=socios`);
        if(!Array.isArray(socios) || socios.length===0){
            panel.innerHTML='<p>No hay socios registrados</p>'; return;
        }
        let html = `<h2>Socios</h2><div class="table-container"><table>
            <thead><tr><th>CI</th><th>Nombre</th><th>Email</th><th>Tel</th><th>Estado</th><th>Ingreso</th><th>Acciones</th></tr></thead><tbody>`;
        socios.forEach(s=>{
            html += `<tr>
                <td>${escapeHtml(s.CIsoc)}</td>
                <td>${escapeHtml(s.PnomS+' '+s.PapeS)}</td>
                <td>${escapeHtml(s.EmailS)}</td>
                <td>${escapeHtml(s.TelS)}</td>
                <td>${escapeHtml(s.EstadoSoc)}</td>
                <td>${escapeHtml(s.FchIngr)}</td>
                <td>
                    <button onclick="eliminarSocio('${s.CIsoc}')">Eliminar</button>
                    <button onclick="cambiarEstadoSocio('${s.CIsoc}','${s.EstadoSoc==='Activo'?'Inactivo':'Activo'}')">${s.EstadoSoc==='Activo'?'Desactivar':'Activar'}</button>
                </td>
            </tr>`;
        });
        html += '</tbody></table></div>'; panel.innerHTML = html;
    }

    async function loadUnidades(){
        panel.innerHTML='Cargando unidades...';
        const unidades = await fetchJSON(`${API_COOPERATIVA}?accion=unidades`);
        if(!Array.isArray(unidades) || unidades.length===0){
            panel.innerHTML='<p>No hay unidades registradas</p>'; return;
        }
        let html = `<h2>Unidades Habitacionales</h2>
            <div class="table-container"><table>
            <thead><tr><th>ID</th><th>Nombre Lote</th><th>Estado</th></tr></thead><tbody>`;
        unidades.forEach(u=>{
            html += `<tr>
                <td>${u.IDUni}</td>
                <td>${escapeHtml(u.NomLote)}</td>
                <td>${escapeHtml(u.EstadoUni)}</td>
            </tr>`;
        });
        html += '</tbody></table></div>'; panel.innerHTML = html;
    }

    async function loadAsambleas(){
        panel.innerHTML='Cargando asambleas...';
        const asamb = await fetchJSON(`${API_COOPERATIVA}?accion=asambleas`);
        if(!Array.isArray(asamb) || asamb.length===0){
            panel.innerHTML='<p>No hay asambleas registradas</p>'; return;
        }
        let html = `<h2>Asambleas</h2>
            <div class="table-container"><table>
            <thead><tr><th>Socio</th><th>Acta</th><th>Fecha</th><th>Orden</th></tr></thead><tbody>`;
        asamb.forEach(a=>{
            html += `<tr>
                <td>${escapeHtml(a.PnomS+' '+a.PapeS)}</td>
                <td>${escapeHtml(a.Acta||'')}</td>
                <td>${escapeHtml(a.FchAsam)}</td>
                <td>${escapeHtml(a.Orden||'')}</td>
            </tr>`;
        });
        html += '</tbody></table></div>'; panel.innerHTML = html;
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
                <button id="btnSubirHoras">Subir horas</button>
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
        if(!Array.isArray(comps) || comps.length===0){
            html += '<p>No hay comprobantes registrados</p>';
        } else {
            html += `<div class="table-container"><table>
            <thead><tr><th>ID</th><th>Socio</th><th>Descripción</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody>`;
            comps.forEach(c=>{
                html += `<tr>
                    <td>${c.id}</td>
                    <td>${escapeHtml(c.PnomS+' '+c.PapeS)}</td>
                    <td>${escapeHtml(c.Descripcion)}</td>
                    <td>${escapeHtml(c.Estado)}</td>
                    <td>${escapeHtml(c.Fecha)}</td>`;
                if(rol.toLowerCase()==='admin' && c.Estado==='Pendiente'){
                    html += `<td>
                        <button onclick="aprobarComprobante(${c.id},'Aprobado')">Aprobar</button>
                        <button onclick="aprobarComprobante(${c.id},'Rechazado')">Rechazar</button>
                    </td>`;
                } else {
                    html += '<td></td>';
                }
                html += '</tr>';
            });
            html += '</tbody></table></div>';
        }
        if(rol.toLowerCase()!=='admin'){
            html += `
                <h3>Subir comprobante</h3>
                <input type="text" id="inputComprobante" placeholder="Descripción">
                <button id="btnSubirComp">Subir</button>
            `;
            panel.innerHTML = html;
            document.getElementById('btnSubirComp').onclick = async ()=>{
                const desc = document.getElementById('inputComprobante').value.trim();
                if(!desc) return alert('Ingrese descripción');
                const res = await fetch(`${API_COOPERATIVA}?accion=subir_comprobante`, {
                    method:'POST', body:new URLSearchParams({descripcion: desc})
                });
                const data = await res.json();
                alert(data.mensaje || data.error);
                loadComprobantes();
            };
        } else panel.innerHTML = html;
    }

    // ================= ACTIONS =================
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

    // ================= INIT TABS =================
    if(rol.toLowerCase() === 'admin'){
        addTab('socios','Socios',loadSocios,true);
        addTab('aspirantes','Aspirantes',loadAspirantes);
        addTab('unidades','Unidades',loadUnidades);
        addTab('asambleas','Asambleas',loadAsambleas);
        addTab('horas','Horas',loadHoras);
        addTab('comprobantes','Comprobantes',loadComprobantes);
    } else {
        addTab('horas','Horas',loadHoras,true);
        addTab('comprobantes','Comprobantes',loadComprobantes);
    }
}
