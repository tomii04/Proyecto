<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

session_start();
if(!isset($_SESSION['usuario'])){
    echo json_encode(["error"=>"No autorizado"]);
    exit;
}

require_once '../conexion.php';

$rol = $_SESSION['rol'] ?? '';
$CIsoc = $_SESSION['CIsoc'] ?? null;  // CI del socio si es un socio

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'POST'){
    $action = $_GET['accion'] ?? null;
    $data = $_POST;

    switch($action){
        // ================= SOCIO / ADMIN POST ACTIONS =================
        case 'subir_horas':
            if($rol !== 'socio'){
                echo json_encode(["error"=>"No autorizado"]);
                exit;
            }
            if(empty($data['horas'])){
                echo json_encode(["error"=>"Faltan horas"]);
                exit;
            }
            if(!$CIsoc){
                echo json_encode(["error"=>"No se encontró CI de socio"]);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO HorasTrabajo (CIsoc,Horas) VALUES (?,?)");
            $stmt->execute([$CIsoc, $data['horas']]);
            echo json_encode(["success"=>true,"mensaje"=>"Horas registradas"]);
            exit;

        case 'subir_comprobante':
            if($rol !== 'socio'){
                echo json_encode(["error"=>"No autorizado"]);
                exit;
            }
            if(empty($data['descripcion'])){
                echo json_encode(["error"=>"Falta descripción"]);
                exit;
            }
            if(!$CIsoc){
                echo json_encode(["error"=>"No se encontró CI de socio"]);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO Comprobantes (CIsoc,Descripcion,Estado) VALUES (?,?, 'Pendiente')");
            $stmt->execute([$CIsoc, $data['descripcion']]);
            echo json_encode(["success"=>true,"mensaje"=>"Comprobante subido"]);
            exit;

        case 'aprobar_comprobante':
            if($rol !== 'admin'){
                echo json_encode(["error"=>"No autorizado"]);
                exit;
            }
            if(empty($data['id']) || empty($data['estado'])){
                echo json_encode(["error"=>"Faltan datos"]);
                exit;
            }
            $estado = in_array($data['estado'], ['Aprobado','Rechazado']) ? $data['estado'] : 'Pendiente';
            $stmt = $pdo->prepare("UPDATE Comprobantes SET Estado=? WHERE id=?");
            $stmt->execute([$estado, $data['id']]);
            echo json_encode(["success"=>true,"mensaje"=>"Comprobante actualizado"]);
            exit;

        default:
            echo json_encode(["error"=>"Acción POST no válida"]);
            exit;
    }
}

if($method === 'GET'){
    $action = $_GET['accion'] ?? null;

    switch($action){
        case 'unidades':
            $stmt = $pdo->query("SELECT * FROM UnidadHabit ORDER BY IDUni ASC");
            echo json_encode($stmt->fetchAll());
            exit;

        case 'asambleas':
            $stmt = $pdo->query("SELECT a.*, s.PnomS, s.PapeS FROM Asamblea a LEFT JOIN Socio s ON a.CIsoc=s.CIsoc ORDER BY FchAsam ASC");
            echo json_encode($stmt->fetchAll());
            exit;

        case 'horas':
            if($rol === 'socio' && $CIsoc){
                $stmt = $pdo->prepare("SELECT h.*, s.PnomS, s.PapeS FROM HorasTrabajo h 
                                       LEFT JOIN Socio s ON h.CIsoc=s.CIsoc 
                                       WHERE h.CIsoc=? ORDER BY Fecha DESC");
                $stmt->execute([$CIsoc]);
            } else {
                $stmt = $pdo->query("SELECT h.*, s.PnomS, s.PapeS FROM HorasTrabajo h 
                                     LEFT JOIN Socio s ON h.CIsoc=s.CIsoc ORDER BY Fecha DESC");
            }
            echo json_encode($stmt->fetchAll());
            exit;

        case 'comprobantes':
            if($rol === 'socio' && $CIsoc){
                $stmt = $pdo->prepare("SELECT c.*, s.PnomS, s.PapeS FROM Comprobantes c 
                                       LEFT JOIN Socio s ON c.CIsoc=s.CIsoc 
                                       WHERE c.CIsoc=? ORDER BY Fecha DESC");
                $stmt->execute([$CIsoc]);
            } else {
                $stmt = $pdo->query("SELECT c.*, s.PnomS, s.PapeS FROM Comprobantes c 
                                     LEFT JOIN Socio s ON c.CIsoc=s.CIsoc ORDER BY Fecha DESC");
            }
            echo json_encode($stmt->fetchAll());
            exit;

        default:
            echo json_encode(["error"=>"Listado no válido"]);
            exit;
    }
}

echo json_encode(["error"=>"Ruta no válida o método incorrecto"]);
exit;
?>
