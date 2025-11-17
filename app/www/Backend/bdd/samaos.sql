CREATE DATABASE IF NOT EXISTS samaos;
USE samaos;

CREATE TABLE Aspirante (
    CIaspi VARCHAR(15) PRIMARY KEY,
    PnomA VARCHAR(50),
    PapeA VARCHAR(50),
    EmailA VARCHAR(100),
    PassA VARCHAR(100),
    TelA VARCHAR(20),
    EstadoSoli VARCHAR(30),
    FchSoli DATE,
    FchAprob DATE,
    ComPagoIni TEXT
);

CREATE TABLE Socio (
    CIsoc VARCHAR(15) PRIMARY KEY,
    PnomS VARCHAR(50),
    PapeS VARCHAR(50),
    EmailS VARCHAR(100),
    PassS VARCHAR(100),
    TelS VARCHAR(20),
    CalleS VARCHAR(100),
    NumPuertaS VARCHAR(10),
    EstadoSoc VARCHAR(30),
    FchIngr DATE
);

CREATE TABLE Administrador (
    IDAdmin INT PRIMARY KEY,
    CIadm VARCHAR(15) UNIQUE,
    Rol VARCHAR(30),
    NomA VARCHAR(50),
    ApeA VARCHAR(50),
    EmailAd VARCHAR(100),
    PassAd VARCHAR(100),
    FchDesig DATE
);

CREATE TABLE Asamblea (
	id INT AUTO_INCREMENT PRIMARY KEY,
    CIsoc VARCHAR(15),
    Acta TEXT,
    FchAsam DATE,
    Orden TEXT,
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc)
);

CREATE TABLE Cuota (
    IDCuota INT PRIMARY KEY,
    Monto DECIMAL(10,2),
    FchVenci DATE
);

CREATE TABLE UnidadHabit (
    IDUni  INT AUTO_INCREMENT PRIMARY KEY,
    NomLote VARCHAR(50),
    Direccion VARCHAR(100),
    FchAsig DATE,
    EstadoUni VARCHAR(30)
);

CREATE TABLE JorComun (
    IDJorn INT PRIMARY KEY,
    FchJorn DATE,
    TareasProg TEXT
);

CREATE TABLE Administra (
    IDAdmin INT,
    CIaspi VARCHAR(15),
    SoliIngreso TEXT,
    PRIMARY KEY (IDAdmin, CIaspi),
    FOREIGN KEY (IDAdmin) REFERENCES Administrador(IDAdmin),
    FOREIGN KEY (CIaspi) REFERENCES Aspirante(CIaspi)
);

CREATE TABLE Paga (
    CIsoc VARCHAR(15),
    IDCuota INT,
    Comprobante TEXT,
    PRIMARY KEY (CIsoc, IDCuota),
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc),
    FOREIGN KEY (IDCuota) REFERENCES Cuota(IDCuota)
);

CREATE TABLE Obtiene (
    CIsoc VARCHAR(15),
    IDUni INT,
    PRIMARY KEY (CIsoc, IDUni),
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc),
    FOREIGN KEY (IDUni) REFERENCES UnidadHabit(IDUni)
);

CREATE TABLE Vota (
    CIsoc VARCHAR(15),
    AsisAsam BOOLEAN,
    PRIMARY KEY (CIsoc),
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc)
);

CREATE TABLE Participa (
    CIsoc VARCHAR(15),
    IDJorn INT,
    Participantes TEXT,
    HsReqSem INT,
    PRIMARY KEY (CIsoc, IDJorn),
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc),
    FOREIGN KEY (IDJorn) REFERENCES JorComun(IDJorn)
);

CREATE TABLE HorasTrabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    CIsoc VARCHAR(15) NULL,
    CIadm VARCHAR(15) NULL,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Horas DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc),
    FOREIGN KEY (CIadm) REFERENCES Administrador(CIadm)
);

CREATE TABLE Comprobantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    CIsoc VARCHAR(15) NULL,
    CIadm VARCHAR(15) NULL,
    Descripcion TEXT NOT NULL,
    Estado ENUM('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc),
    FOREIGN KEY (CIadm) REFERENCES Administrador(CIadm)
);

CREATE TABLE CuotaSocio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    CIsoc VARCHAR(15) NOT NULL,
    Monto DECIMAL(10,2) NOT NULL,
    FchVenci DATE NOT NULL,
    Estado ENUM('Pendiente','Pagada') DEFAULT 'Pendiente',
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc)
);

CREATE TABLE UnidadSocio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    CIsoc VARCHAR(15),
    UNIDAD VARCHAR(10) NOT NULL,
    Direccion VARCHAR(100) NOT NULL,
    Estado ENUM('En construcción','Por empezar','Finalizada') DEFAULT 'Por empezar',
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc)
);



INSERT INTO Aspirante (CIaspi, PnomA, PapeA, EmailA, PassA, TelA, EstadoSoli, FchSoli, FchAprob, ComPagoIni) VALUES 
('10000001', 'Carlos', 'Ramírez', 'carlos.ramirez@gmail.com', 'password', '0991001001', 'Pendiente', '2025-01-10', NULL, 'Comp001'),
('10000002', 'Laura', 'Martínez', 'laura.martinez@gmail.com', 'password1', '0992002002', 'Aprobado', '2025-02-01', '2025-02-15', 'Comp002');

INSERT INTO Administrador (IDAdmin, CIadm, Rol, NomA, ApeA, EmailAd, PassAd, FchDesig) VALUES 
(1, '20120000', 'Presidente', 'Tomas', 'Gomez', 'carlosyrami@gmail.com', 'password12', '2025-01-01'),
(2, '20080000', 'Tesorero', 'Javier', 'Abaldi', 'lauraymartin@gmail.com', 'password132', '2025-02-10');

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

INSERT INTO UnidadHabit (IDUni, NomLote, Direccion, FchAsig, EstadoUni) VALUES 
(1, 'Lote A1','Av Rivera 1221', '2025-02-25', 'Finalizada'),
(2, 'Lote B3','Bv Artigas 3412', '2025-03-15', 'En construcción');

INSERT INTO Obtiene (CIsoc, IDUni) VALUES 
('20000001', 1),
('20000002', 2);

INSERT INTO Cuota (IDCuota, Monto, FchVenci) VALUES 
(1, 100.00, '2025-08-31'),
(2, 120.00, '2025-09-30');

INSERT INTO Paga (CIsoc, IDCuota, Comprobante) VALUES 
('20000001', 1, 'Pago001'),
('20000002', 2, 'Pago002');

INSERT INTO Asamblea (id, CIsoc, Acta, FchAsam, Orden) VALUES 
(1, '20000001', 'Acta Asamblea Ordinaria', '2025-03-01', 'Revisión de presupuestos'),
(2, '20000002', 'Acta Asamblea Extraordinaria', '2025-04-05', 'Renovación de directiva');

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
