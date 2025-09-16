DROP SCHEMA IF EXISTS CooperativaS;
CREATE SCHEMA IF NOT EXISTS CooperativaS;
USE CooperativaS;

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
    Rol VARCHAR(30),
    EmailAd VARCHAR(100),
    PassAd VARCHAR(100),
    FchDesig DATE
);

CREATE TABLE Asamblea (
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
    IDUni INT PRIMARY KEY,
    NomLote VARCHAR(50),
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
    CIsoc VARCHAR(15) NOT NULL,
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Horas DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc)
);

CREATE TABLE Comprobantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    CIsoc VARCHAR(15) NOT NULL,
    Descripcion TEXT NOT NULL,
    Estado ENUM('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
    Fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CIsoc) REFERENCES Socio(CIsoc)
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
