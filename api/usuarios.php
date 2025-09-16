<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if(!isset($_SESSION['usuario'])){
    echo json_encode(["error"=>"No autorizado"]); exit;
}

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'POST'){
    $action = $_GET['accion'] ?? null;
    $data = $_POST;

    switch($action){
        case 'aprobar_aspirante':
            if(empty($data['ci'])){ echo json_encode(["error"=>"Falta ci"]); exit; }
            $stmt = $pdo->prepare("SELECT * FROM Aspirante WHERE CIaspi=?");
            $stmt->execute([$data['ci']]);
            $asp = $stmt->fetch();
            if(!$asp){ echo json_encode(["error"=>"Aspirante no encontrado"]); exit; }

            $stmt2 = $pdo->prepare("INSERT INTO Socio (CIsoc,PnomS,PapeS,EmailS,PassS,TelS,EstadoSoc,FchIngr)
                                    VALUES (?,?,?,?,?,?, 'Activo', NOW())");
            $stmt2->execute([$asp['CIaspi'],$asp['PnomA'],$asp['PapeA'],$asp['EmailA'],$asp['PassA'],$asp['TelA']]);

            $stmt3 = $pdo->prepare("DELETE FROM Aspirante WHERE CIaspi=?");
            $stmt3->execute([$data['ci']]);

            echo json_encode(["success"=>true,"mensaje"=>"Aspirante aprobado"]); exit;

        case 'eliminar_aspirante':
            if(empty($data['ci'])){ echo json_encode(["error"=>"Falta ci"]); exit; }
            $stmt = $pdo->prepare("DELETE FROM Aspirante WHERE CIaspi=?");
            $stmt->execute([$data['ci']]);
            echo json_encode(["success"=>true,"mensaje"=>"Aspirante eliminado"]); exit;

        case 'eliminar_socio':
            if(empty($data['ci'])){ echo json_encode(["error"=>"Falta ci"]); exit; }
            $stmt = $pdo->prepare("DELETE FROM Socio WHERE CIsoc=?");
            $stmt->execute([$data['ci']]);
            echo json_encode(["success"=>true,"mensaje"=>"Socio eliminado"]); exit;

        case 'cambiar_estado_socio':
            if(empty($data['ci']) || empty($data['estado'])){ echo json_encode(["error"=>"Faltan datos"]); exit; }
            $stmt = $pdo->prepare("UPDATE Socio SET EstadoSoc=? WHERE CIsoc=?");
            $stmt->execute([$data['estado'],$data['ci']]);
            echo json_encode(["success"=>true,"mensaje"=>"Estado actualizado"]); exit;

        default:
            echo json_encode(["error"=>"Acción POST no válida"]); exit;
    }
}

if($method==='GET' && isset($_GET['accion'])){
    switch($_GET['accion']){
        case 'aspirantes':
            $stmt = $pdo->query("SELECT CIaspi,PnomA,PapeA,EmailA,TelA,FchSoli FROM Aspirante");
            echo json_encode($stmt->fetchAll()); exit;
        case 'socios':
            $stmt = $pdo->query("SELECT CIsoc,PnomS,PapeS,EmailS,TelS,EstadoSoc,FchIngr FROM Socio");
            echo json_encode($stmt->fetchAll()); exit;
        default:
            echo json_encode(["error"=>"Listado no válido"]); exit;
    }
}
echo json_encode(["error"=>"Ruta no válida o método incorrecto"]); exit;
