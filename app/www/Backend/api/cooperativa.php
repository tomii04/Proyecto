<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'] ?? (php_sapi_name() === 'cli' ? 'CLI' : 'GET');
require_once __DIR__ . '/../conexion.php';
global $pdo;

try {
    if($pdo->query("SHOW COLUMNS FROM Comprobantes LIKE 'Archivo'")->rowCount() === 0){
        $pdo->exec("ALTER TABLE Comprobantes ADD COLUMN Archivo VARCHAR(255) NULL");
    }
    if($pdo->query("SHOW COLUMNS FROM Asamblea LIKE 'Acta'")->rowCount() === 0){
        $pdo->exec("ALTER TABLE Asamblea ADD COLUMN Acta VARCHAR(255) NULL");
    }
} catch(PDOException $e){}

function isAdmin(){
    $r = $_SESSION['rol'] ?? '';
    return in_array($r, ['admin','Administrador','ADMIN']);
}

if($method === 'CLI'){
    echo json_encode(["success"=>true,"mensaje"=>"cooperativa.php listo para pruebas CLI"]);
    exit;
}

$rol = $_SESSION['rol'] ?? 'invitado';
$CIsoc = $_SESSION['CIsoc'] ?? null;
error_log("DEBUG >>> rol=$rol CI=$CIsoc");

if($method === 'POST'){
    $action = $_GET['accion'] ?? null;
    switch($action){
        case 'add_unidad':
            if (!isAdmin()) {
                echo json_encode(["error" => "No autorizado"]);
                exit;
            }
        
            $nom = $_POST['nom'] ?? '';
            $dir = $_POST['dir'] ?? '';
            $estado = $_POST['estado'] ?? 'Por empezar';
        
            if ($nom === '' || $dir === '') {echo json_encode(["error" => "Falta nombre o dirección"]);exit;}
            try {
            $stmt = $pdo->prepare("INSERT INTO UnidadHabit (NomLote, Direccion, EstadoUni) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $dir, $estado]);
            echo json_encode(["success" => true, "mensaje" => "Unidad agregada correctamente"]);
            } catch (PDOException $e) {echo json_encode(["error" => "Error en base de datos: " . $e->getMessage()]);}exit;            

        case 'cambiar_estado_unidad':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $id = $_POST['id'] ?? null;
            $estado = $_POST['estado'] ?? null;
            if(!$id || !$estado){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $stmt = $pdo->prepare("UPDATE UnidadHabit SET EstadoUni=? WHERE IDUni=?");
            $stmt->execute([$estado,$id]);
            echo json_encode(["success"=>true,"mensaje"=>"Estado actualizado"]); exit;

            case 'asignar_unidad':
                if (!isAdmin()) { 
                    echo json_encode(["error" => "No autorizado"]); 
                    exit; 
                }
                $ci = $_POST['ci'] ?? null;
                $iduni = $_POST['iduni'] ?? null;
                $manual_unidad = $_POST['manual_unidad'] ?? null;
                $manual_direccion = $_POST['manual_direccion'] ?? null;
                $manual_estado = $_POST['manual_estado'] ?? 'Por empezar';
                if (!$ci) { 
                    echo json_encode(["error" => "Falta CI del socio"]); 
                    exit; 
                }
                try {
                    $pdo->prepare("DELETE FROM Obtiene WHERE CIsoc=?")->execute([$ci]);
                    if ($iduni) {
                        $pdo->prepare("INSERT INTO Obtiene (CIsoc, IDUni) VALUES (?, ?)")
                            ->execute([$ci, $iduni]);
                        echo json_encode(["success" => true, "mensaje" => "Unidad existente asignada correctamente"]);
                        exit;
                    }
                    if ($manual_unidad && $manual_direccion) {
                        $pdo->prepare("INSERT INTO UnidadHabit (NomLote, Direccion, EstadoUni)
                                       VALUES (?, ?, ?)")
                            ->execute([$manual_unidad, $manual_direccion, $manual_estado]);
                        $newId = $pdo->lastInsertId();
                        $pdo->prepare("INSERT INTO Obtiene (CIsoc, IDUni) VALUES (?, ?)")
                            ->execute([$ci, $newId]);
                        echo json_encode(["success" => true, "mensaje" => "Unidad manual creada y asignada correctamente"]);
                        exit;
                    }
                    echo json_encode(["error" => "Faltan datos: seleccione unidad o ingrese datos manuales"]);
                } catch (PDOException $e) {
                    echo json_encode(["error" => "Error en base de datos: " . $e->getMessage()]);
                }
                exit;            

            case 'subir_horas':
                    $rol = $_SESSION['rol'] ?? '';
                    if($rol !== 'socio' && !isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
                
                    $horas = $_POST['horas'] ?? null;
                    $ciInput = $_POST['CIsoc'] ?? null;
                
                    if($rol === 'socio') {
                        $CIsoc = $_SESSION['CIsoc'] ?? null;
                        $CIadm = null;
                    } else if(isAdmin()) {
                        if($ciInput) {
                            $CIsoc = $ciInput;
                            $CIadm = null;
                        } else {
                            $CIadm = $_SESSION['CIadm'] ?? null;
                            $CIsoc = null;
                        }
                    }
                
                    if($horas === null || $horas === '' || (!$CIsoc && !$CIadm)){
                        echo json_encode(["error"=>"Faltan datos"]);
                        exit;
                    }
                
                    $horasFloat = floatval(str_replace(',', '.', $horas));
                
                    $stmt = $pdo->prepare("INSERT INTO HorasTrabajo (CIsoc, CIadm, Horas) VALUES (?, ?, ?)");
                    $stmt->execute([$CIsoc, $CIadm, $horasFloat]);
                
                    echo json_encode(["success"=>true,"mensaje"=>"Horas registradas"]);
                    exit;                

            case 'subir_comprobante':
                $rol = $_SESSION['rol'] ?? '';
                if ($rol !== 'socio' && !isAdmin()) {echo json_encode(["error" => "No autorizado"]);exit;}
                $descripcion = trim($_POST['descripcion'] ?? '');
                $CIsoc = $_SESSION['CIsoc'] ?? null;
                $CIadm = $_SESSION['CIadm'] ?? null;
                if (!$descripcion || (!$CIsoc && !$CIadm)) {echo json_encode(["error" => "Faltan datos"]);exit;}
                if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== 0) {echo json_encode(["error" => "No se recibió archivo"]);exit;}
                $f = $_FILES['archivo'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {echo json_encode(["error" => "Solo PDF permitido"]);exit;}
                $destDir = __DIR__ . '/../comprobantes';
                if (!is_dir($destDir)) mkdir($destDir, 0777, true);
                $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                if (!move_uploaded_file($f['tmp_name'], $destDir . '/' . $name)) {echo json_encode(["error" => "No se pudo guardar archivo"]);exit;}
                $stmt = $pdo->prepare("INSERT INTO Comprobantes (CIsoc, CIadm, Descripcion, Archivo, Estado) VALUES (?, ?, ?, ?, 'Pendiente')");
                $stmt->execute([$CIsoc, $CIadm, $descripcion, $name]);
                echo json_encode(["success" => true, "mensaje" => "Comprobante subido"]);exit;            
            
        case 'aprobar_comprobante':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $id = $_POST['id'] ?? null;
            $estado = $_POST['estado'] ?? null;
            if(!$id || !$estado){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $estado = in_array($estado, ['Aprobado','Rechazado']) ? $estado : 'Pendiente';
            $stmt = $pdo->prepare("UPDATE Comprobantes SET Estado=? WHERE id=?");
            $stmt->execute([$estado, $id]);
            echo json_encode(["success"=>true,"mensaje"=>"Comprobante actualizado"]); exit;

        case 'subir_asamblea':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $fecha = $_POST['fecha'] ?? null;
            $orden = $_POST['orden'] ?? null;
            if(!$fecha || !$orden){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $actaFilename = null;
            if(isset($_FILES['acta']) && $_FILES['acta']['error']===0){
                $f = $_FILES['acta'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if($ext !== 'pdf'){ echo json_encode(["error"=>"Solo PDF permitido"]); exit; }
                $destDir = __DIR__ . '/../actas';
                if(!is_dir($destDir)) mkdir($destDir, 0777, true);
                $actaFilename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                if(!move_uploaded_file($f['tmp_name'],$destDir . '/' . $actaFilename)){ echo json_encode(["error"=>"No se pudo guardar acta"]); exit; }
            }
            $stmt = $pdo->prepare("INSERT INTO Asamblea (CIsoc, Acta, FchAsam, Orden) VALUES (NULL,?,?,?)");
            $stmt->execute([$actaFilename, $fecha, $orden]);
            echo json_encode(["success"=>true,"mensaje"=>"Asamblea guardada"]); exit;

        case 'subir_acta':
        case 'agregar_acta':
            if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
            $id = $_POST['id'] ?? null;
            if(!$id || !isset($_FILES['acta'])){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $f = $_FILES['acta'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if($ext !== 'pdf'){ echo json_encode(["error"=>"Solo PDF permitido"]); exit; }
            $destDir = __DIR__ . '/../actas';
            if(!is_dir($destDir)) mkdir($destDir, 0777, true);
            $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if(!move_uploaded_file($f['tmp_name'],$destDir . '/' . $name)){ echo json_encode(["error"=>"No se pudo guardar acta"]); exit; }
            $stmt = $pdo->prepare("UPDATE Asamblea SET Acta=? WHERE id=?");
            $stmt->execute([$name, $id]);
            echo json_encode(["success"=>true,"mensaje"=>"Acta subida"]); exit;

        case 'eliminar_asamblea':
                if(!isAdmin()){ echo json_encode(["error"=>"No autorizado"]); exit; }
                $id = $_POST['id'] ?? null;
                if(!$id){echo json_encode(["error"=>"Falta ID de la asamblea"]); exit; }
                $stmt = $pdo->prepare("DELETE FROM Asamblea WHERE id=?");
                $stmt->execute([$id]);
                echo json_encode(["success"=>true,"mensaje"=>"Asamblea eliminada"]); exit;
            
        default:
            echo json_encode(["error"=>"Acción POST no válida"]); exit;
    }
}

if($method === 'GET'){
    $action = $_GET['accion'] ?? null;
    switch($action){
        case 'unidades':
            $stmt = $pdo->query("SELECT IDUni, NomLote, Direccion, FchAsig, EstadoUni FROM UnidadHabit ORDER BY IDUni ASC");
            echo json_encode($stmt->fetchAll()); exit;

        case 'asambleas':
            $stmt = $pdo->query("SELECT id, Acta, FchAsam, Orden FROM Asamblea ORDER BY FchAsam DESC");
            echo json_encode($stmt->fetchAll()); exit;

        case 'horas':
            case 'horas_trabajo':
                    $rol = $_SESSION['rol'] ?? '';
                    $CIsoc = $_SESSION['CIsoc'] ?? null;
                
                    if ($rol === 'socio' && $CIsoc) {
                        $stmt = $pdo->prepare("
                            SELECT h.id, h.Horas, h.Fecha,
                                   COALESCE(s.PnomS, '') AS PnomS,
                                   COALESCE(s.PapeS, '') AS PapeS,
                                   COALESCE(a.NomA, '') AS NomA,
                                   COALESCE(a.ApeA, '') AS ApeA
                            FROM HorasTrabajo h
                            LEFT JOIN Socio s ON h.CIsoc = s.CIsoc
                            LEFT JOIN Administrador a ON h.CIadm = a.CIadm
                            WHERE h.CIsoc = ?
                            ORDER BY h.Fecha DESC
                        ");
                        $stmt->execute([$CIsoc]);
                    } else {
                        $stmt = $pdo->query("
                            SELECT h.id, h.Horas, h.Fecha,
                                   COALESCE(s.PnomS, '') AS PnomS,
                                   COALESCE(s.PapeS, '') AS PapeS,
                                   COALESCE(a.NomA, '') AS NomA,
                                   COALESCE(a.ApeA, '') AS ApeA
                            FROM HorasTrabajo h
                            LEFT JOIN Socio s ON h.CIsoc = s.CIsoc
                            LEFT JOIN Administrador a ON h.CIadm = a.CIadm
                            ORDER BY h.Fecha DESC
                        ");
                    }
                
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                    foreach ($result as &$r) {
                        if ($r['PnomS'] || $r['PapeS']) {
                            $r['Usuario'] = trim($r['PnomS'] . ' ' . $r['PapeS']);
                        } elseif ($r['NomA'] || $r['ApeA']) {
                            $r['Usuario'] = trim($r['NomA'] . ' ' . $r['ApeA']);
                        } else {
                            $r['Usuario'] = 'Sin usuario';
                        }
                    }
                
                    echo json_encode($result);
                    exit;

        case 'comprobantes':
                        $rol = $_SESSION['rol'] ?? '';
                        $CIsoc = $_SESSION['CIsoc'] ?? null;
                    
                        if ($rol === 'socio' && $CIsoc) {
                            $stmt = $pdo->prepare("
                                SELECT c.*, 
                                       s.PnomS, s.PapeS,
                                       a.NomA, a.ApeA,
                                       u.NomLote AS Unidad
                                FROM Comprobantes c
                                LEFT JOIN Socio s ON c.CIsoc=s.CIsoc
                                LEFT JOIN Administrador a ON c.CIadm=a.CIadm
                                LEFT JOIN Obtiene o ON s.CIsoc=o.CIsoc
                                LEFT JOIN UnidadHabit u ON o.IDUni=u.IDUni
                                WHERE c.CIsoc=?
                                ORDER BY c.Fecha DESC
                            ");
                            $stmt->execute([$CIsoc]);
                        } else {
                            $stmt = $pdo->query("
                                SELECT c.*, 
                                       s.PnomS, s.PapeS,
                                       a.NomA, a.ApeA,
                                       u.NomLote AS Unidad
                                FROM Comprobantes c
                                LEFT JOIN Socio s ON c.CIsoc=s.CIsoc
                                LEFT JOIN Administrador a ON c.CIadm=a.CIadm
                                LEFT JOIN Obtiene o ON s.CIsoc=o.CIsoc
                                LEFT JOIN UnidadHabit u ON o.IDUni=u.IDUni
                                ORDER BY c.Fecha DESC
                            ");
                        }
                    
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                        foreach ($result as &$r) {
                            if ($r['PnomS'] || $r['PapeS']) {
                                $r['Usuario'] = trim(($r['PnomS'] ?? '') . ' ' . ($r['PapeS'] ?? ''));
                            } elseif ($r['NomA'] || $r['ApeA']) {
                                $r['Usuario'] = trim(($r['NomA'] ?? '') . ' ' . ($r['ApeA'] ?? ''));
                            } else {
                                $r['Usuario'] = 'Sin usuario';
                            }
                        }
                    
                        echo json_encode($result);
                        exit;                    

                        case 'mi_unidad':
                            if ($rol !== 'socio' && $rol !== 'admin') {echo json_encode(['error' => 'No autorizado']);exit;}
                            $CI = null;
                            if ($rol === 'socio') {
                                $CI = $_SESSION['CIsoc'] ?? null;
                            } else {
                                $CI = $_SESSION['CIadm'] ?? null;
                            }
                            if (!$CI) {
                                echo json_encode(["Unidad"=>null,"Direccion"=>null,"Estado"=>null]);
                                exit;}
                            $stmt = $pdo->prepare("
                                SELECT UNIDAD AS Unidad, Direccion, Estado 
                                FROM UnidadSocio 
                                WHERE CIsoc = ? LIMIT 1
                            ");
                            $stmt->execute([$CI]);
                            $u = $stmt->fetch();
                            if ($u) {echo json_encode($u);exit;}
                            $stmt = $pdo->prepare("
                                SELECT uh.NomLote AS Unidad, uh.EstadoUni AS Estado, uh.FchAsig AS Direccion
                                FROM Obtiene o 
                                JOIN UnidadHabit uh ON o.IDUni = uh.IDUni 
                                WHERE o.CIsoc = ? 
                                LIMIT 1
                            ");
                            $stmt->execute([$CI]);
                            $u2 = $stmt->fetch();
                            if ($u2) {
                                echo json_encode([
                                    "Unidad"    => $u2['Unidad'],
                                    "Direccion" => $u2['Direccion'],
                                    "Estado"    => $u2['Estado']
                                ]);
                            exit;}
                            echo json_encode(["Unidad"=>null,"Direccion"=>null,"Estado"=>null]);exit;

        default:
            echo json_encode(["error"=>"Listado no válido"]); exit;
    }
}

http_response_code(400);
echo json_encode(["error"=>"Ruta no válida o método incorrecto"]);
exit;
?>