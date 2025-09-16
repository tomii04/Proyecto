USE CooperativaS;

INSERT INTO Aspirante (CIaspi, PnomA, PapeA, EmailA, PassA, TelA, EstadoSoli, FchSoli, FchAprob, ComPagoIni) VALUES 
('10000001', 'Carlos', 'Ramírez', 'carlos.ramirez@gmail.com', 'password', '0991001001', 'Pendiente', '2025-01-10', NULL, 'Comp001'),
('10000002', 'Laura', 'Martínez', 'laura.martinez@gmail.com', 'password1', '0992002002', 'Aprobado', '2025-02-01', '2025-02-15', 'Comp002');

INSERT INTO Administrador (IDAdmin, Rol, EmailAd, PassAd, FchDesig) VALUES 
(1, 'Presidente', 'carlosyrami@gmail.com', 'password12', '2025-01-01'),
(2, 'Tesorero', 'lauraymartin@gmail.com', 'password132', '2025-02-10');

INSERT INTO Administra (IDAdmin, CIaspi, SoliIngreso) VALUES 
(1, '10000001', 'Solicitud registrada'),
(2, '10000002', 'Solicitud revisada y aceptada');

INSERT INTO Socio (CIsoc, PnomS, PapeS, EmailS, PassS, TelS, CalleS, NumPuertaS, EstadoSoc, FchIngr) VALUES 
('20000001', 'María', 'López', 'm.lopez@gmail.com', 'password102', '0981001001', 'Av. Siempre Viva', '123', 'Activo', '2025-02-20'),
('20000002', 'José', 'Pérez', 'j.perez@gmail.com', 'password112', '0982002002', 'Calle Falsa', '456', 'Activo', '2025-03-10');

INSERT INTO UnidadSocio (CIsoc, UNIDAD, Direccion, Estado) VALUES
(NULL, '101', 'Calle Falsa 123', 'Por empezar'),
(NULL, '102', 'Calle Verdadera 456', 'Por empezar');

INSERT INTO CuotaSocio (CIsoc, Monto, FchVenci) VALUES
('20000001', 100.00, '2025-08-31'),
('20000002', 120.00, '2025-09-30');

INSERT INTO UnidadSocio (CIsoc, UNIDAD, Direccion, Estado) VALUES
('20000001', '101', 'Calle Falsa 123', 'En construcción'),
('20000002', '102', 'Calle Verdadera 456', 'Por empezar');

INSERT INTO UnidadHabit (IDUni, NomLote, FchAsig, EstadoUni) VALUES 
(1, 'Lote A1', '2025-02-25', 'Asignada'),
(2, 'Lote B3', '2025-03-15', 'Asignada');

INSERT INTO Obtiene (CIsoc, IDUni) VALUES 
('20000001', 1),
('20000002', 2);

INSERT INTO Cuota (IDCuota, Monto, FchVenci) VALUES 
(1, 100.00, '2025-08-31'),
(2, 120.00, '2025-09-30');

INSERT INTO Paga (CIsoc, IDCuota, Comprobante) VALUES 
('20000001', 1, 'Pago001'),
('20000002', 2, 'Pago002');

INSERT INTO Asamblea (CIsoc, Acta, FchAsam, Orden) VALUES 
('20000001', 'Acta Asamblea Ordinaria', '2025-03-01', 'Revisión de presupuestos'),
('20000002', 'Acta Asamblea Extraordinaria', '2025-04-05', 'Renovación de directiva');

INSERT INTO JorComun (IDJorn, FchJorn, TareasProg) VALUES 
(1, '2025-05-01', 'Limpieza del parque central'),
(2, '2025-06-10', 'Pintura de fachada del bloque B');

INSERT INTO Participa (CIsoc, IDJorn, Participantes, HsReqSem) VALUES 
('20000001', 1, 'María López', 4),
('20000002', 2, 'José Pérez', 6);

INSERT INTO Vota (CIsoc, AsisAsam) VALUES 
('20000001', TRUE),
('20000002', TRUE);

INSERT INTO HorasTrabajo (CIsoc, Horas) VALUES
('20000001', 8.5),
('20000002', 6.0);

INSERT INTO Comprobantes (CIsoc, Descripcion, Estado) VALUES
('20000001', 'Pago cuota febrero', 'Pendiente'),
('20000002', 'Pago cuota marzo', 'Pendiente');
