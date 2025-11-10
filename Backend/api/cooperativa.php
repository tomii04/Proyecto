<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if(!isset($_SESSION['usuario'])){
    echo json_encode(["error"=>"No autorizado"]); exit;
}

require_once __DIR__ . '/../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $stmtCheck = $pdo->query("SHOW COLUMNS FROM Comprobantes LIKE 'Archivo'");
    if($stmtCheck->rowCount() === 0){
        $pdo->exec("ALTER TABLE Comprobantes ADD COLUMN Archivo VARCHAR(255) NULL");
    }
    $stmtCheck2 = $pdo->query("SHOW COLUMNS FROM Asamblea LIKE 'Acta'");
    if($stmtCheck2->rowCount() === 0){
        $pdo->exec("ALTER TABLE Asamblea ADD COLUMN Acta VARCHAR(255) NULL");
    }
} catch(PDOException $e){}

function isAdmin(){
    $r = $_SESSION['rol'] ?? '';
    return in_array($r, ['admin','Administrador','ADMIN']);
}

if($method === 'POST'){
    $action = $_GET['accion'] ?? null;

    switch($action){
        case 'add_unidad':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $nom = trim($_POST['nom'] ?? '');
            $estado = $_POST['estado'] ?? 'Por empezar';
            if($nom === ''){ echo json_encode(["error"=>"Falta nombre"]); exit; }
            $stmt = $pdo->prepare("INSERT INTO UnidadHabit (NomLote, EstadoUni) VALUES (?,?)");
            $stmt->execute([$nom,$estado]);
            echo json_encode(["success"=>true,"mensaje"=>"Unidad agregada"]); exit;

        case 'cambiar_estado_unidad':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $id = $_POST['id'] ?? null;
            $estado = $_POST['estado'] ?? null;
            if(!$id || !$estado){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $stmt = $pdo->prepare("UPDATE UnidadHabit SET EstadoUni=? WHERE IDUni=?");
            $stmt->execute([$estado,$id]);
            echo json_encode(["success"=>true,"mensaje"=>"Estado actualizado"]); exit;

        case 'subir_asamblea':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $fecha = $_POST['fecha'] ?? null;
            $orden = $_POST['orden'] ?? null;
            if(!$fecha || !$orden){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $actaFilename = null;
            if(isset($_FILES['acta']) && isset($_FILES['acta']['error']) && $_FILES['acta']['error']===0){
                $f = $_FILES['acta'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if($ext !== 'pdf'){ echo json_encode(["error"=>"Solo PDF permitido"]); exit; }
                $destDir = __DIR__ . '/../actas';
                if(!is_dir($destDir)) mkdir($destDir, 0777, true);
                $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $destDir . '/' . $name;
                if(!move_uploaded_file($f['tmp_name'],$dest)){ echo json_encode(["error"=>"No se pudo guardar acta"]); exit; }
                $actaFilename = $name;
            }
            $stmt = $pdo->prepare("INSERT INTO Asamblea (CIsoc, Acta, FchAsam, Orden) VALUES (NULL,?,?,?)");
            $stmt->execute([$actaFilename, $fecha, $orden]);
            echo json_encode(["success"=>true,"mensaje"=>"Asamblea guardada"]); exit;

        case 'subir_acta':
        case 'agregar_acta':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $id = $_POST['id'] ?? null;
            if(!$id){ echo json_encode(["error"=>"Falta id"]); exit; }
            if(!isset($_FILES['acta']) || !isset($_FILES['acta']['error']) || $_FILES['acta']['error'] !== 0){ echo json_encode(["error"=>"No se recibió archivo"]); exit; }
            $f = $_FILES['acta'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if($ext !== 'pdf'){ echo json_encode(["error"=>"Solo PDF permitido"]); exit; }
            $destDir = __DIR__ . '/../actas';
            if(!is_dir($destDir)) mkdir($destDir, 0777, true);
            $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $destDir . '/' . $name;
            if(!move_uploaded_file($f['tmp_name'],$dest)){ echo json_encode(["error"=>"No se pudo guardar acta"]); exit; }
            $stmt = $pdo->prepare("UPDATE Asamblea SET Acta=? WHERE id=?");
            $stmt->execute([$name, $id]);
            echo json_encode(["success"=>true,"mensaje"=>"Acta subida"]); exit;

        case 'subir_comprobante':
            if(($_SESSION['rol'] ?? '') !== 'socio'){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $descripcion = trim($_POST['descripcion'] ?? '');
            $CIsoc = $_SESSION['CIsoc'] ?? null;
            if(!$descripcion || !$CIsoc){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            if(!isset($_FILES['archivo']) || !isset($_FILES['archivo']['error']) || $_FILES['archivo']['error'] !== 0){
                echo json_encode(["error"=>"No se recibió archivo"]); exit;
            }
            $f = $_FILES['archivo'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if($ext !== 'pdf'){ echo json_encode(["error"=>"Solo PDF permitido"]); exit; }
            $destDir = __DIR__ . '/../comprobantes';
            if(!is_dir($destDir)) mkdir($destDir, 0777, true);
            $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $destDir . '/' . $name;
            if(!move_uploaded_file($f['tmp_name'],$dest)){ echo json_encode(["error"=>"No se pudo guardar archivo"]); exit; }
            $stmt = $pdo->prepare("INSERT INTO Comprobantes (CIsoc, Descripcion, Archivo, Estado) VALUES (?,?,?, 'Pendiente')");
            $stmt->execute([$CIsoc, $descripcion, $name]);
            echo json_encode(["success"=>true,"mensaje"=>"Comprobante subido"]); exit;

        case 'aprobar_comprobante':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $id = $_POST['id'] ?? null;
            $estado = $_POST['estado'] ?? null;
            if(!$id || !$estado){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $estado = in_array($estado, ['Aprobado','Rechazado']) ? $estado : 'Pendiente';
            $stmt = $pdo->prepare("UPDATE Comprobantes SET Estado=? WHERE id=?");
            $stmt->execute([$estado, $id]);
            echo json_encode(["success"=>true,"mensaje"=>"Comprobante actualizado"]); exit;

        case 'asignar_unidad':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $ci = $_POST['ci'] ?? null;
            $unidad = $_POST['iduni'] ?? $_POST['manual_unidad'] ?? null;
            $direccion = $_POST['manual_direccion'] ?? 'No definida';
            $estadoU = $_POST['manual_estado'] ?? 'Por empezar';
            if(!$ci || !$unidad){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $stmt = $pdo->prepare("UPDATE Socio SET Unidad=? WHERE CIsoc=?");
            $stmt->execute([$unidad, $ci]);
            $stmt = $pdo->prepare("SELECT id FROM UnidadSocio WHERE CIsoc=? LIMIT 1");
            $stmt->execute([$ci]);
            $exists = $stmt->fetch();
            if($exists){
                $stmt = $pdo->prepare("UPDATE UnidadSocio SET UNIDAD=?, Direccion=?, Estado=? WHERE CIsoc=?");
                $stmt->execute([$unidad, $direccion, $estadoU, $ci]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO UnidadSocio (CIsoc, UNIDAD, Direccion, Estado) VALUES (?,?,?,?)");
                $stmt->execute([$ci, $unidad, $direccion, $estadoU]);
            }
            echo json_encode(["success"=>true,"mensaje"=>"Unidad asignada"]); exit;

        case 'subir_horas':
            if(($_SESSION['rol'] ?? '') !== 'socio'){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $horas = $_POST['horas'] ?? null;
            $CIsoc = $_SESSION['CIsoc'] ?? null;
            if($horas === null || $horas === '' || !$CIsoc){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $horasFloat = floatval(str_replace(',', '.', $horas));
            $stmt = $pdo->prepare("INSERT INTO HorasTrabajo (CIsoc, Horas) VALUES (?, ?)");
            $stmt->execute([$CIsoc, $horasFloat]);
            echo json_encode(["success"=>true,"mensaje"=>"Horas registradas"]); exit;

        default:
            echo json_encode(["error"=>"Acción POST no válida"]); exit;
    }
}

if($method === 'GET'){
    $action = $_GET['accion'] ?? null;

    switch($action){
        case 'unidades':
            $stmt = $pdo->query("SELECT IDUni, NomLote, FchAsig, EstadoUni FROM UnidadHabit ORDER BY IDUni ASC");
            echo json_encode($stmt->fetchAll()); exit;

        case 'asambleas':
            $stmt = $pdo->query("SELECT id, Acta, FchAsam, Orden FROM Asamblea ORDER BY FchAsam DESC");
            echo json_encode($stmt->fetchAll()); exit;

        case 'horas':
            $rol = $_SESSION['rol'] ?? '';
            $CIsoc = $_SESSION['CIsoc'] ?? null;
            if($rol === 'socio' && $CIsoc){
                $stmt = $pdo->prepare("SELECT h.id, h.Horas, h.Fecha, s.PnomS, s.PapeS FROM HorasTrabajo h LEFT JOIN Socio s ON h.CIsoc=s.CIsoc WHERE h.CIsoc=? ORDER BY Fecha DESC");
                $stmt->execute([$CIsoc]);
            } else {
                $stmt = $pdo->query("SELECT h.id, h.Horas, h.Fecha, s.PnomS, s.PapeS FROM HorasTrabajo h LEFT JOIN Socio s ON h.CIsoc=s.CIsoc ORDER BY Fecha DESC");
            }
            echo json_encode($stmt->fetchAll()); exit;

        case 'comprobantes':
            $rol = $_SESSION['rol'] ?? '';
            $CIsoc = $_SESSION['CIsoc'] ?? null;
            if($rol === 'socio' && $CIsoc){
                $stmt = $pdo->prepare("SELECT c.*, s.PnomS, s.PapeS, s.Unidad FROM Comprobantes c LEFT JOIN Socio s ON c.CIsoc=s.CIsoc WHERE c.CIsoc=? ORDER BY Fecha DESC");
                $stmt->execute([$CIsoc]);
            } else {
                $stmt = $pdo->query("SELECT c.*, s.PnomS, s.PapeS, s.Unidad FROM Comprobantes c LEFT JOIN Socio s ON c.CIsoc=s.CIsoc ORDER BY Fecha DESC");
            }
            echo json_encode($stmt->fetchAll()); exit;

        case 'mi_unidad':
            $CIsoc = $_SESSION['CIsoc'] ?? null;
            if(!$CIsoc){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $stmt = $pdo->prepare("SELECT UNIDAD as Unidad, Direccion, Estado FROM UnidadSocio WHERE CIsoc=? LIMIT 1");
            $stmt->execute([$CIsoc]);
            $u = $stmt->fetch();
            if($u){
                echo json_encode($u); exit;
            }
            $stmt = $pdo->prepare("SELECT uh.NomLote as Unidad, uh.EstadoUni as Estado, uh.FchAsig as Direccion FROM Obtiene o JOIN UnidadHabit uh ON o.IDUni=uh.IDUni WHERE o.CIsoc=? LIMIT 1");
            $stmt->execute([$CIsoc]);
            $u2 = $stmt->fetch();
            if($u2){
                echo json_encode(["Unidad"=>$u2['Unidad'],"Direccion"=>$u2['Direccion'],"Estado"=>$u2['Estado']]); exit;
            }
            echo json_encode(["Unidad"=>null,"Direccion"=>null,"Estado"=>null]); exit;

        default:
            echo json_encode(["error"=>"Listado no válido"]); exit;
    }
}

echo json_encode(["error"=>"Ruta no válida o método incorrecto"]); exit;
