function initPanelUsuarios(rol, ci_sess) {
    window.ciSesion = ci_sess;
    const tabsContainer = document.getElementById('tabs');
    const contentContainer = document.getElementById('panel-content');
    const API_USUARIOS = '../Backend/api/usuarios.php';
    const API_COOPERATIVA = '../Backend/api/cooperativa.php';
    const escapeHtml = text => text ? (text + '').replace(/[&<>"'`=\/]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;','`':'&#96;','=':'&#61;'}[s])) : '';

    const panels = {};

    function createPanelIfMissing(id) {
        if (panels[id]) return panels[id];
        const el = document.getElementById(id);
        if (el) {
            panels[id] = el;
            el.style.display = 'none';
            return el;
        }
        const div = document.createElement('div');
        div.id = id;
        div.classList.add('subpanel');
        div.style.display = 'none';
        contentContainer.appendChild(div);
        panels[id] = div;
        return div;
    }

    function clearActiveTabs() {
        const buttons = tabsContainer.querySelectorAll('button');
        buttons.forEach(b => b.classList.remove('active'));
        Object.values(panels).forEach(p => p.style.display = 'none');
    }

    function addTab(id, label, callback, isDefault = false) {
        const btn = document.createElement('button');
        btn.textContent = label;
        btn.id = 'tab-' + id;
        btn.type = 'button';
        btn.onclick = async () => {
            clearActiveTabs();
            btn.classList.add('active');
            const panelId = 'panel' + id.charAt(0).toUpperCase() + id.slice(1);
            const panelEl = createPanelIfMissing(panelId);
            panelEl.style.display = 'block';
            try {
                await callback(panelEl);
            } catch (e) {
                panelEl.innerHTML = `<div class="error">Error: ${escapeHtml(e.message || e)}</div>`;
            }
        };
        tabsContainer.appendChild(btn);
        if (isDefault) btn.click();
    }

    async function fetchJSON(url, options) {
        const res = await fetch(url, options);
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error(text || 'Respuesta no válida JSON');
        }
    }

    async function loadAspirantes(panel) {
        panel.innerHTML = 'Cargando aspirantes...';
        try {
            const aspirantes = await fetchJSON(`${API_USUARIOS}?accion=aspirantes`);
            if (!Array.isArray(aspirantes) || aspirantes.length === 0) {
                panel.innerHTML = '<p>No hay aspirantes registrados</p>';
                return;
            }
            let html = `<h2>Aspirantes</h2>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>CI</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Tel</th>
                                        <th>Fecha Solicitud</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>`;
            aspirantes.forEach(a => {
                const nombre = `${a.PnomA || ''} ${a.PapeA || ''}`.trim();
                html += `<tr>
                            <td>${escapeHtml(a.CIaspi)}</td>
                            <td>${escapeHtml(nombre)}</td>
                            <td>${escapeHtml(a.EmailA || '')}</td>
                            <td>${escapeHtml(a.TelA || '')}</td>
                            <td>${escapeHtml(a.FchSoli || '')}</td>
                            <td>
                                <button class="btn-aprobar" data-ci="${escapeHtml(a.CIaspi)}">Aprobar</button>
                                <button class="btn-eliminar" data-ci="${escapeHtml(a.CIaspi)}">Eliminar</button>
                            </td>
                        </tr>`;
            });
            html += '</tbody></table></div>';
            panel.innerHTML = html;
            panel.querySelectorAll('.btn-aprobar').forEach(b => {
                b.addEventListener('click', async () => {
                    const ci = b.dataset.ci;
                    await aprobarAspirante(ci, panel);
                });
            });
            panel.querySelectorAll('.btn-eliminar').forEach(b => {
                b.addEventListener('click', async () => {
                    const ci = b.dataset.ci;
                    await eliminarAspirante(ci, panel);
                });
            });
        } catch (err) {
            console.error(err);
            panel.innerHTML = '<p>Error cargando aspirantes</p>';
        }
    }    

    async function loadSocios(panel) {
        panel.innerHTML = 'Cargando socios...';
        const socios = await fetchJSON(`${API_USUARIOS}?accion=socios`);
        if (!Array.isArray(socios) || socios.length === 0) {
            panel.innerHTML = '<p>No hay socios registrados</p>';
            return;
        }
        let html = `
            <h2>Socios</h2>
            <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>CI</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Email</th>
                        <th>Tel</th>
                        <th>Estado</th>
                        <th>Ingreso</th>
                        <th>Unidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
        `;
    
        socios.forEach(s => {
            const nombre = escapeHtml((s.PnomS ?? '') + " " + (s.PapeS ?? ''));
            const rol = s.Rol ? escapeHtml(s.Rol) : "Socio";
            html += `
                <tr>
                    <td>${escapeHtml(s.CIsoc)}</td>
                    <td>${nombre}</td>
                    <td>${rol}</td>
                    <td>${escapeHtml(s.EmailS ?? '')}</td>
                    <td>${escapeHtml(s.TelS ?? '')}</td>
                    <td>${escapeHtml(s.EstadoSoc ?? '')}</td>
                    <td>${escapeHtml(s.FchIngr ?? '')}</td>
                    <td>${escapeHtml(s.Unidad ?? '--')}</td>
                    <td>
                        <button class="btn-elim-socio" data-ci="${escapeHtml(s.CIsoc)}">
                            Eliminar
                        </button>
    
                        <button class="btn-toggle-estado" 
                                data-ci="${escapeHtml(s.CIsoc)}" 
                                data-estado="${escapeHtml(s.EstadoSoc)}">
                            ${s.EstadoSoc === 'Activo' ? 'Desactivar' : 'Activar'}
                        </button>
    
                        ${window.rol && window.rol.toLowerCase() === 'admin' ? `
                            <button class="btn-hacer-admin" data-ci="${escapeHtml(s.CIsoc)}">
                                Hacer Admin
                            </button>
    
                            <button class="btn-asignar-unidad" data-ci="${escapeHtml(s.CIsoc)}">
                                Asignar Unidad
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
        });
    
        html += `</tbody></table></div>`;
        panel.innerHTML = html;
        panel.querySelectorAll('.btn-elim-socio').forEach(b => {
            b.addEventListener('click', async () => {
                const ci = b.dataset.ci;
                if (!ci) return alert("CI no encontrada en el botón.");
    
                if (!confirm(`Eliminar socio ${ci}?`)) return;
    
                const res = await fetch(`${API_USUARIOS}?accion=eliminar_socio`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ci })
                });
    
                const data = await res.json();
                alert(data.mensaje || data.error);
                loadSocios(panel);
            });
        });
        panel.querySelectorAll('.btn-toggle-estado').forEach(b => {
            b.addEventListener('click', async () => {
                const ci = b.dataset.ci;
                const nuevoEstado = b.dataset.estado === 'Activo' ? 'Inactivo' : 'Activo';
    
                await cambiarEstadoSocio(ci, nuevoEstado, panel);
            });
        });
        panel.querySelectorAll('.btn-hacer-admin').forEach(b => {
            b.addEventListener('click', async () => {
                const ci = b.dataset.ci;
    
                const res = await fetch(`${API_USUARIOS}?accion=hacer_admin`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ci })
                });
    
                const data = await res.json();
                alert(data.mensaje || data.error);
                loadSocios(panel);
            });
        });
        panel.querySelectorAll('.btn-asignar-unidad').forEach(b => {
            b.addEventListener('click', () => {
                openAssignUnidadModal(b.dataset.ci, panel);
            });
        });
    }    

    async function loadUnidades(panel) {
        panel.innerHTML = 'Cargando unidades...';
    
        const unidades = await fetchJSON(`${API_COOPERATIVA}?accion=unidades`);
    
        let html = `
            <h2>Unidades Habitacionales</h2>
            <form id="formAddUnidad">
                <input type="text" id="inputNomLote" placeholder="Nombre Lote / Unidad" required>
                <input type="text" id="inputDireccionUni" placeholder="Dirección de la unidad" required>
                <select id="selectEstadoUni">
                    <option value="Por empezar">Por empezar</option>
                    <option value="En construcción">En construcción</option>
                    <option value="Finalizada">Finalizada</option>
                </select>
                <button type="submit">Agregar unidad</button>
            </form>
        `;
    
        if (!Array.isArray(unidades) || unidades.length === 0) {
            html += '<p>No hay unidades registradas</p>';
        } else {
            html += `
                <div class="table-container">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Nombre Lote</th><th>Dirección</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
            `;
            unidades.forEach(u => {
                html += `
                    <tr>
                        <td>${u.IDUni}</td>
                        <td>${escapeHtml(u.NomLote)}</td>
                        <td>${escapeHtml(u.Direccion || '')}</td>
                        <td>${escapeHtml(u.EstadoUni)}</td>
                        <td>
                            <button class="btn-estado-unidad" data-id="${u.IDUni}" data-estado="Por empezar">Por empezar</button>
                            <button class="btn-estado-unidad" data-id="${u.IDUni}" data-estado="En construcción">En construcción</button>
                            <button class="btn-estado-unidad" data-id="${u.IDUni}" data-estado="Finalizada">Finalizada</button>
                        </td>
                    </tr>
                `;
            });
            html += '</tbody></table></div>';
        }
    
        panel.innerHTML = html;
    
        panel.querySelectorAll('.btn-estado-unidad').forEach(b =>
            b.addEventListener('click', async () => {
                const id = b.dataset.id;
                const estado = b.dataset.estado;
                const res = await fetch(`${API_COOPERATIVA}?accion=cambiar_estado_unidad`, {
                    method: 'POST',
                    body: new URLSearchParams({ id, estado })
                });
                const data = await res.json();
                alert(data.mensaje || data.error);
                await loadUnidades(panel);
            })
        );
    
        const form = panel.querySelector('#formAddUnidad');
        form.onsubmit = async e => {
            e.preventDefault();
            const nom = document.getElementById('inputNomLote').value.trim();
            const dir = document.getElementById('inputDireccionUni').value.trim();
            const estado = document.getElementById('selectEstadoUni').value;
    
            if (!nom || !dir) return alert('Complete nombre y dirección de la unidad');
    
            const res = await fetch(`${API_COOPERATIVA}?accion=add_unidad`, {
                method: 'POST',
                body: new URLSearchParams({ nom, dir, estado })
            });
    
            const data = await res.json();
            alert(data.mensaje || data.error);
            await loadUnidades(panel);
        };
    }    
    
    async function loadAsambleas(panel) {
        panel.innerHTML = 'Cargando asambleas...';
        const asamb = await fetchJSON(`${API_COOPERATIVA}?accion=asambleas`);
        let html = `<h2>Asambleas</h2>`;
        if (rol.toLowerCase() === 'admin') {
            html += `<form id="formAddAsamblea" enctype="multipart/form-data">
                        <input type="date" id="inputFchAsam" required>
                        <input type="text" id="inputOrden" placeholder="Orden del día" required>
                        <button type="submit">Programar asamblea</button>
                    </form>`;
        }
        if (!Array.isArray(asamb) || asamb.length === 0) {
            html += '<p>No hay asambleas registradas</p>';
            panel.innerHTML = html;
            if (rol.toLowerCase() === 'admin') {
                const formEmpty = panel.querySelector('#formAddAsamblea');
                formEmpty.onsubmit = async e => {
                    e.preventDefault();
                    const fecha = document.getElementById('inputFchAsam').value;
                    const orden = document.getElementById('inputOrden').value.trim();
                    if (!fecha || !orden) return alert('Complete fecha y orden');
                    const fd = new FormData();
                    fd.append('fecha', fecha);
                    fd.append('orden', orden);
                    const res = await fetch(`${API_COOPERATIVA}?accion=subir_asamblea`, { method: 'POST', body: fd });
                    const data = await res.json();
                    alert(data.mensaje || data.error);
                    await loadAsambleas(panel);
                };
            }
            return;
        }
        html += `<div class="table-container"><table>
            <thead><tr><th>ID</th><th>Fecha</th><th>Orden</th><th>Acta</th><th>Acciones</th></tr></thead><tbody>`;
        asamb.forEach(a => {
            const link = a.Acta ? (a.Acta.startsWith('/') || a.Acta.includes('Backend') ? a.Acta : `../Backend/actas/${a.Acta}`) : '';
            html += `<tr>
                <td>${a.id}</td>
                <td>${escapeHtml(a.FchAsam)}</td>
                <td>${escapeHtml(a.Orden || '')}</td>
                <td>${ a.Acta ? `<a href="${link}" target="_blank">Ver Acta</a>` : '--' }</td>
                <td>${rol.toLowerCase() === 'admin' ? (!a.Acta ? `<button class="btn-subir-acta" data-id="${a.id}">Subir acta</button>` : '') + ` <button class="btn-elim-asamblea" data-id="${a.id}">Eliminar</button>` : ''}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        panel.innerHTML = html;
        if (rol.toLowerCase() === 'admin') {
            const form = panel.querySelector('#formAddAsamblea');
            form.onsubmit = async e => {
                e.preventDefault();
                const fecha = document.getElementById('inputFchAsam').value;
                const orden = document.getElementById('inputOrden').value.trim();
                if (!fecha || !orden) return alert('Complete fecha y orden');
                const fd = new FormData();
                fd.append('fecha', fecha);
                fd.append('orden', orden);
                const res = await fetch(`${API_COOPERATIVA}?accion=subir_asamblea`, { method: 'POST', body: fd });
                const data = await res.json();
                alert(data.mensaje || data.error);
                await loadAsambleas(panel);
            };
            panel.querySelectorAll('.btn-subir-acta').forEach(b => b.addEventListener('click', () => openSubirActaModal(b.dataset.id, panel)));
            panel.querySelectorAll('.btn-elim-asamblea').forEach(b => b.addEventListener('click', async () => {
                const id = b.dataset.id;
                if (!confirm('Eliminar asamblea ' + id + '?')) return;
                const res = await fetch(`${API_COOPERATIVA}?accion=eliminar_asamblea`, { method: 'POST', body: new URLSearchParams({ id }) });
                const data = await res.json();
                alert(data.mensaje || data.error);
                await loadAsambleas(panel);
            }));
        }
    }

    async function loadHoras(panel) {
        panel.innerHTML = 'Cargando horas...';
        const horas = await fetchJSON(`${API_COOPERATIVA}?accion=horas`);
        if (!Array.isArray(horas) || horas.length === 0) {
            panel.innerHTML = '<p>No hay horas registradas</p>';
            if (rol.toLowerCase() === 'admin' || rol.toLowerCase() === 'socio') {
                panel.innerHTML += `
                    <h3>Registrar horas trabajadas</h3>
                    <input type="number" id="inputHoras" placeholder="Horas" min="0" step="0.5">
                    <button id="btnSubirHoras">Subir</button>
                `;
                panel.querySelector('#btnSubirHoras').addEventListener('click', async () => {
                    const horasVal = panel.querySelector('#inputHoras').value;
                    if (!horasVal) return alert('Ingrese horas');
                    const res = await fetch(`${API_COOPERATIVA}?accion=subir_horas`, {
                        method: 'POST',
                        body: new URLSearchParams({ horas: horasVal })
                    });
                    const data = await res.json();
                    alert(data.mensaje || data.error);
                    await loadHoras(panel);
                });
            }
            return;
        }
        let html = `<h2>Horas de Trabajo</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Horas</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>`;
        horas.forEach(h => {
            html += `<tr>
                        <td>${h.id}</td>
                        <td>${escapeHtml(h.Usuario || 'Sin usuario')}</td>
                        <td>${h.Horas}</td>
                        <td>${escapeHtml(h.Fecha)}</td>
                    </tr>`;
        });
        html += '</tbody></table></div>';
        if (rol.toLowerCase() === 'admin' || rol.toLowerCase() === 'socio') {
            html += `
                <h3>Registrar horas trabajadas</h3>
                <input type="number" id="inputHoras" placeholder="Horas" min="0" step="0.5">
                <button id="btnSubirHoras">Subir</button>
            `;
        }
        panel.innerHTML = html;
        if (rol.toLowerCase() === 'admin' || rol.toLowerCase() === 'socio') {
            panel.querySelector('#btnSubirHoras').addEventListener('click', async () => {
                const horasVal = panel.querySelector('#inputHoras').value;
                if (!horasVal) return alert('Ingrese horas');
    
                const res = await fetch(`${API_COOPERATIVA}?accion=subir_horas`, {
                    method: 'POST',
                    body: new URLSearchParams({ horas: horasVal })
                });
    
                const data = await res.json();
                alert(data.mensaje || data.error);
                await loadHoras(panel);
            });
        }
    }

    async function loadComprobantes(panel) {
        panel.innerHTML = 'Cargando comprobantes...';
        const comps = await fetchJSON(`${API_COOPERATIVA}?accion=comprobantes`);
    
        if (!Array.isArray(comps) || comps.length === 0) {
            panel.innerHTML = '<p>No hay comprobantes</p>';
        } else {
            let html = `<h2>Comprobantes</h2><div class="table-container"><table>
                <thead><tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Descripción</th>
                    <th>Archivo</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr></thead><tbody>`;
    
            comps.forEach(c => {
                let nombre = '';
                if (c.PnomS || c.PapeS) {
                    nombre = `${c.PnomS || ''} ${c.PapeS || ''}`.trim();
                } else if (c.NomA || c.ApeA) {
                    nombre = `${c.NomA || ''} ${c.ApeA || ''}`.trim();
                } else {
                    nombre = 'Sin usuario';
                }
    
                const link = c.Archivo ? `../Backend/comprobantes/${c.Archivo}` : '';
    
                html += `<tr>
                    <td>${c.id}</td>
                    <td>${escapeHtml(nombre)}</td>
                    <td>${escapeHtml(c.Descripcion || '')}</td>
                    <td>${c.Archivo ? `<a href="${link}" target="_blank">Ver PDF</a>` : '--'}</td>
                    <td>${escapeHtml(c.Estado || '')}</td>
                    <td>${escapeHtml(c.Fecha || '')}</td>
                    <td>${rol.toLowerCase() === 'admin' && c.Estado === 'Pendiente' ? 
                        `<button class="btn-aprobar-comp" data-id="${c.id}">Aprobar</button>
                         <button class="btn-rechazar-comp" data-id="${c.id}">Rechazar</button>` : ''}</td>
                </tr>`;
            });
    
            html += '</tbody></table></div>';
            panel.innerHTML = html;
        }
    
        if (rol.toLowerCase() === 'admin' || rol.toLowerCase() === 'socio') {
            panel.innerHTML += `<form id="formSubirComp" enctype="multipart/form-data">
                <input type="text" id="inputComprobanteDesc" placeholder="Descripción" required>
                <input type="file" id="inputComprobanteFile" accept="application/pdf" required>
                <button type="submit">Subir comprobante</button>
            </form>`;
    
            const form = panel.querySelector('#formSubirComp');
            form.onsubmit = async e => {
                e.preventDefault();
                const desc = panel.querySelector('#inputComprobanteDesc').value.trim();
                const file = panel.querySelector('#inputComprobanteFile').files[0];
                if (!desc || !file) return alert('Complete todos los campos y adjunte PDF');
                const fd = new FormData();
                fd.append('descripcion', desc);
                fd.append('archivo', file);
                const res = await fetch(`${API_COOPERATIVA}?accion=subir_comprobante`, { method: 'POST', body: fd });
                const data = await res.json();
                alert(data.mensaje || data.error);
                await loadComprobantes(panel);
            };
        }
    
        panel.querySelectorAll('.btn-aprobar-comp').forEach(b => b.addEventListener('click', async () => {
            const id = b.dataset.id;
            const res = await fetch(`${API_COOPERATIVA}?accion=aprobar_comprobante`, {
                method: 'POST',
                body: new URLSearchParams({ id, estado: 'Aprobado' })
            });
            const data = await res.json();
            alert(data.mensaje || data.error);
            await loadComprobantes(panel);
        }));
    
        panel.querySelectorAll('.btn-rechazar-comp').forEach(b => b.addEventListener('click', async () => {
            const id = b.dataset.id;
            const res = await fetch(`${API_COOPERATIVA}?accion=aprobar_comprobante`, {
                method: 'POST',
                body: new URLSearchParams({ id, estado: 'Rechazado' })
            });
            const data = await res.json();
            alert(data.mensaje || data.error);
            await loadComprobantes(panel);
        }));
    }
    

    async function loadUnidadSocio(panel) {
        panel.innerHTML = 'Cargando unidad...';
        try {
            const u = await fetchJSON(`${API_COOPERATIVA}?accion=mi_unidad`);
            if (u && (u.Unidad || u.Direccion || u.Estado)) {
                const unidad = escapeHtml(u.Unidad || 'Sin asignar');
                const direccion = escapeHtml(u.Direccion || 'No definida');
                const estado = escapeHtml(u.Estado || '');
                panel.innerHTML = `<h2>Mi Unidad Habitacional</h2>
                    <p><strong>Unidad:</strong> ${unidad}</p>
                    <p><strong>Dirección:</strong> ${direccion}</p>
                    <p><strong>Estado:</strong> ${estado}</p>`;
                return;
            }
            panel.innerHTML = `<p>Sin unidad asignada</p>`;
        } catch (e) {
            panel.innerHTML = `<div class="error">Error al obtener unidad</div>`;
        }
    }
    
    async function openAssignUnidadModal(ci, parentPanel) {
        const unidades = await fetchJSON(`${API_COOPERATIVA}?accion=unidades`);
        let options = `<option value="">-- Ninguna (manual) --</option>`;
        if (Array.isArray(unidades)) {
            unidades.forEach(u => {
                options += `<option value="${u.IDUni}">${escapeHtml(u.NomLote)} (${escapeHtml(u.EstadoUni)})</option>`;
            });
        }
        const modal = document.createElement('div');
        modal.classList.add('modal-overlay');
        Object.assign(modal.style, {
            position: 'fixed',
            left: '0',
            top: '0',
            width: '100%',
            height: '100%',
            background: 'rgba(0,0,0,0.4)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
        });
        modal.innerHTML = `
            <div style="background:#fff;padding:16px;max-width:600px;width:90%;border-radius:6px;">
                <h3>Asignar unidad a ${escapeHtml(ci)}</h3>
                <form id="formAssignUnidad">
                    <div style="margin-bottom:12px;">
                        <label><strong>Elegir unidad existente</strong></label><br>
                        <select id="selectAssignUnidad">${options}</select>
                    </div>
    
                    <div style="margin-bottom:12px; border-top:1px solid #ccc; padding-top:12px;">
                        <label><strong>O ingresar manualmente</strong></label><br>
                        <input type="text" id="inputManualUnidad" placeholder="Unidad (ej. 101)" style="width:48%; margin-right:4%;">
                        <input type="text" id="inputManualDireccion" placeholder="Dirección" style="width:48%;"><br><br>
                        <select id="selectManualEstado" style="width:100%;">
                            <option value="Por empezar">Por empezar</option>
                            <option value="En construcción">En construcción</option>
                            <option value="Finalizada">Finalizada</option>
                        </select>
                    </div>
    
                    <div style="margin-top:12px;">
                        <button type="submit">Guardar asignación</button>
                        <button type="button" id="btnCancelAssign">Cancelar</button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', e => {
            if (e.target === modal) document.body.removeChild(modal);
        });
        modal.querySelector('#btnCancelAssign').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        modal.querySelector('#formAssignUnidad').onsubmit = async e => {
            e.preventDefault();
            const idUni = modal.querySelector('#selectAssignUnidad').value;
            const manualUni = modal.querySelector('#inputManualUnidad').value.trim();
            const manualDir = modal.querySelector('#inputManualDireccion').value.trim();
            const manualEstado = modal.querySelector('#selectManualEstado').value;
            const fd = new FormData();
            fd.append('ci', ci);
            if (idUni) {
                fd.append('iduni', idUni);
            } else if (manualUni && manualDir) {
                fd.append('manual_unidad', manualUni);
                fd.append('manual_direccion', manualDir);
                fd.append('manual_estado', manualEstado);
            } else {
                return alert('Seleccione unidad existente o complete los campos manuales (unidad y dirección).');
            }
            const res = await fetch(`${API_COOPERATIVA}?accion=asignar_unidad`, { method: 'POST', body: fd });
            const data = await res.json();
            alert(data.mensaje || data.error);
            document.body.removeChild(modal);
            const panelSoc = document.getElementById('panelSocios');
            if (panelSoc && panelSoc.style.display !== 'none') await loadSocios(panelSoc);
            const panelUni = document.getElementById('panelUnidades');
            if (panelUni && panelUni.style.display !== 'none') await loadUnidades(panelUni);
        };
    }

    function openSubirActaModal(id, parentPanel) {
        const modal = document.createElement('div');
        modal.classList.add('modal-overlay');
        modal.style.position = 'fixed';
        modal.style.left = '0';
        modal.style.top = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.background = 'rgba(0,0,0,0.4)';
        modal.style.display = 'flex';
        modal.style.alignItems = 'center';
        modal.style.justifyContent = 'center';
        modal.innerHTML = `<div style="background:#fff;padding:16px;max-width:600px;width:90%;border-radius:6px;">
            <h3>Subir acta para asamblea ${escapeHtml(id)}</h3>
            <form id="formSubirActa" enctype="multipart/form-data">
                <input type="file" id="inputActaFile" accept="application/pdf" required><br><br>
                <button type="submit">Subir acta</button>
                <button type="button" id="btnCancelActa">Cancelar</button>
            </form>
        </div>`;
        document.body.appendChild(modal);
        modal.addEventListener('click', e => { if (e.target === modal) { document.body.removeChild(modal); } });
        modal.querySelector('#btnCancelActa').addEventListener('click', () => document.body.removeChild(modal));
        modal.querySelector('#formSubirActa').onsubmit = async e => {
            e.preventDefault();
            const file = modal.querySelector('#inputActaFile').files[0];
            if (!file) return alert('Adjunte PDF');
            const fd = new FormData();
            fd.append('id', id);
            fd.append('acta', file);
            const res = await fetch(`${API_COOPERATIVA}?accion=agregar_acta`, { method: 'POST', body: fd });
            const data = await res.json();
            alert(data.mensaje || data.error);
            document.body.removeChild(modal);
            const panelAsam = createPanelIfMissing('panelAsambleas');
            await loadAsambleas(panelAsam);
        };
    }

    async function aprobarAspirante(ci, panel) {
        const res = await fetch(`${API_USUARIOS}?accion=aprobar_aspirante`, { method: 'POST', body: new URLSearchParams({ ci }) });
        const data = await res.json();
        alert(data.mensaje || data.error);
        await loadAspirantes(panel);
        const panelSoc = createPanelIfMissing('panelSocios');
        await loadSocios(panelSoc);
    }

    async function eliminarAspirante(ci, panel) {
        if (!confirm('Eliminar aspirante ' + ci + '?')) return;
        const res = await fetch(`${API_USUARIOS}?accion=eliminar_aspirante`, { method: 'POST', body: new URLSearchParams({ ci }) });
        const data = await res.json();
        alert(data.mensaje || data.error);
        await loadAspirantes(panel);
    }

    async function eliminarSocio(ci, panel) {
        if (!ci) {
            alert("Falta CI.");
            return;
        }
    
        if (!confirm('Eliminar socio ' + ci + '?')) return;
    
        const res = await fetch(`${API_USUARIOS}?accion=eliminar_socio`, { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ci }) });
        const data = await res.json();
        alert(data.mensaje || data.error);    
        await loadSocios(panel);
    }
    
    async function cambiarEstadoSocio(ci, estado, panel) {
        const res = await fetch(`${API_USUARIOS}?accion=cambiar_estado_socio`, { method: 'POST', body: new URLSearchParams({ ci, estado }) });
        const data = await res.json();
        alert(data.mensaje || data.error);
        const panelSoc = createPanelIfMissing('panelSocios');
        await loadSocios(panelSoc);
    }

    window.hacerAdmin = async function (ci) {
        if (!confirm(`¿Seguro que querés convertir a ${ci} en administrador?`)) return;
        const res = await fetch(`${API_USUARIOS}?accion=hacer_admin`, {
            method: "POST",
            body: new URLSearchParams({ ci })
        });
        const data = await res.json();
        if (data.ok) {
            alert("El socio ahora es administrador.");
        } else {
            alert("Error: " + data.error);
        }    
        const panelSoc = createPanelIfMissing('panelSocios');
        await loadSocios(panelSoc);
    };    

    const toggle = document.getElementById("theme-toggle");
    const body = document.body;
    if (localStorage.getItem("theme") === "dark") {
        body.classList.add("dark-mode");
        if (toggle) toggle.textContent = "☀️";
    } else {
        if (toggle) toggle.textContent = "🌙";
    }
    if (toggle) toggle.addEventListener('click', () => {
        body.classList.toggle("dark-mode");
        const isDark = body.classList.contains("dark-mode");
        toggle.textContent = isDark ? "☀️" : "🌙";
        localStorage.setItem("theme", isDark ? "dark" : "light");
    });

    if (rol.toLowerCase() === 'admin') {
        addTab('unidad', 'Mi Unidad', loadUnidadSocio, true);
        addTab('socios', 'Socios', loadSocios, true);
        addTab('aspirantes', 'Aspirantes', loadAspirantes);
        addTab('unidades', 'Unidades', loadUnidades);
        addTab('asambleas', 'Asambleas', loadAsambleas);
        addTab('horas', 'Horas', loadHoras);
        addTab('comprobantes', 'Comprobantes', loadComprobantes);
    } else {
        addTab('unidad', 'Mi Unidad', loadUnidadSocio, true);
        addTab('comprobantes', 'Comprobantes', loadComprobantes);
        addTab('asambleas', 'Asambleas', loadAsambleas);
        addTab('horas', 'Horas', loadHoras);
    }
}