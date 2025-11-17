<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';

require_once __DIR__ . '/../conexion.php';
global $pdo;

if (!isset($_SESSION['usuario']) && $method !== 'CLI') {
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

if ($method === 'CLI') {
    echo json_encode(["success" => true, "mensaje" => "usuarios.php listo para pruebas CLI"]);
    exit;
}

if ($method === 'POST') {
    $action = $_GET['accion'] ?? null;
    $data = $_POST;

    switch ($action) {
        case 'aprobar_aspirante':
            if (empty($data['ci'])) { echo json_encode(["error" => "Falta CI"]); exit; }
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT * FROM Aspirante WHERE CIaspi=?");
                $stmt->execute([$data['ci']]);
                $asp = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$asp) { $pdo->rollBack(); echo json_encode(["error" => "Aspirante no encontrado"]); exit; }
                $stmt2 = $pdo->prepare("
                    INSERT INTO Socio (CIsoc, PnomS, PapeS, EmailS, PassS, TelS, EstadoSoc, FchIngr)
                    VALUES (?, ?, ?, ?, ?, ?, 'Activo', NOW())
                ");
                $stmt2->execute([
                    $asp['CIaspi'],
                    $asp['PnomA'],
                    $asp['PapeA'],
                    $asp['EmailA'],
                    $asp['PassA'],
                    $asp['TelA']
                ]);
                $stmtU = $pdo->prepare("SELECT IDUni, NomLote FROM UnidadHabit WHERE EstadoUni='Por empezar' ORDER BY IDUni ASC LIMIT 1");
                $stmtU->execute();
                $uni = $stmtU->fetch(PDO::FETCH_ASSOC);
                if ($uni) {
                    $stmtIns = $pdo->prepare("INSERT IGNORE INTO Obtiene (CIsoc, IDUni) VALUES (?, ?)");
                    $stmtIns->execute([$asp['CIaspi'], $uni['IDUni']]);
                    $stmtUpdUni = $pdo->prepare("UPDATE UnidadHabit SET EstadoUni='En construcción', FchAsig=NOW() WHERE IDUni=?");
                    $stmtUpdUni->execute([$uni['IDUni']]);
                }
                $stmtDelAdmin = $pdo->prepare("DELETE FROM Administra WHERE CIaspi=?");
                $stmtDelAdmin->execute([$data['ci']]);
                $stmt3 = $pdo->prepare("DELETE FROM Aspirante WHERE CIaspi=?");
                $stmt3->execute([$data['ci']]);
                $pdo->commit();
                echo json_encode(["success" => true, "mensaje" => "Aspirante aprobado correctamente"]);
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode(["error" => "Error al aprobar aspirante: " . $e->getMessage()]);
            }
            exit;
        
        case 'eliminar_aspirante':
            if (empty($data['ci'])) { echo json_encode(["error" => "Falta CI"]); exit; }
            try {
                $stmtDelAdmin = $pdo->prepare("DELETE FROM Administra WHERE CIaspi=?");
                $stmtDelAdmin->execute([$data['ci']]);
                $stmt = $pdo->prepare("DELETE FROM Aspirante WHERE CIaspi=?");
                $stmt->execute([$data['ci']]);
                echo json_encode(["success" => true, "mensaje" => "Aspirante eliminado correctamente"]);
            } catch (PDOException $e) {
                echo json_encode(["error" => "Error al eliminar aspirante: " . $e->getMessage()]);
            }
            exit;                  

            case 'eliminar_socio':
                $data = json_decode(file_get_contents("php://input"), true);
                if (!is_array($data)) $data = [];
                $ci = $data['ci'] ?? $_POST['ci'] ?? $_GET['ci'] ?? null;
                error_log("DEBUG eliminar_socio => CI recibida: " . var_export($ci, true));
                if (!$ci) {
                    echo json_encode(["error" => "Falta CI"]);
                    exit;
                }
                try {
                    $pdo->beginTransaction();
                    $pdo->prepare("DELETE FROM Obtiene WHERE CIsoc=?")->execute([$ci]);
                    $pdo->prepare("DELETE FROM Comprobantes WHERE CIsoc=?")->execute([$ci]);
                    $pdo->prepare("DELETE FROM Paga WHERE CIsoc=?")->execute([$ci]);
                    $pdo->prepare("DELETE FROM CuotaSocio WHERE CIsoc=?")->execute([$ci]);
                    $pdo->prepare("DELETE FROM HorasTrabajo WHERE CIsoc=?")->execute([$ci]);
                    $pdo->prepare("DELETE FROM Participa WHERE CIsoc=?")->execute([$ci]);
                    $pdo->prepare("DELETE FROM Vota WHERE CIsoc=?")->execute([$ci]);
                    $pdo->prepare("DELETE FROM Administrador WHERE CIadm=?")->execute([$ci]);
                    $pdo->prepare("DELETE FROM Socio WHERE CIsoc=?")->execute([$ci]);
                    $pdo->commit();
                    echo json_encode(["mensaje" => "Socio eliminado correctamente"]);
                    exit;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log("ERROR eliminar_socio: " . $e->getMessage());
                    echo json_encode(["error" => "Error al eliminar socio: " . $e->getMessage()]);
                    exit;
                }                             

        case 'cambiar_estado_socio':
            if (empty($data['ci']) || empty($data['estado'])) {
                echo json_encode(["error" => "Faltan datos"]); exit;
            }
            $stmt = $pdo->prepare("UPDATE Socio SET EstadoSoc=? WHERE CIsoc=?");
            $stmt->execute([$data['estado'], $data['ci']]);
            echo json_encode(["success" => true, "mensaje" => "Estado actualizado"]);
            exit;

            case 'hacer_admin':
                $data = json_decode(file_get_contents("php://input"), true);
                $ci = $data['ci'] ?? null;
                if (!$ci) { echo json_encode(['error'=>'CI requerida']); exit; }
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("SELECT PnomS, PapeS, EmailS, PassS FROM Socio WHERE CIsoc = ?");
                    $stmt->execute([$ci]);
                    $socio = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$socio) {
                        $pdo->rollBack();
                        echo json_encode(['error'=>'Socio no encontrado']);
                        exit;
                    }
                    $stmt = $pdo->prepare("SELECT 1 FROM Administrador WHERE CIadm = ?");
                    $stmt->execute([$ci]);
                    if ($stmt->fetchColumn()) {
                        $pdo->rollBack();
                        echo json_encode(['error'=>'Este socio ya es administrador']);
                        exit;
                    }
                    $nextStmt = $pdo->query("SELECT COALESCE(MAX(IDAdmin), 0) + 1 AS nextId FROM Administrador");
                    $nextId = (int) $nextStmt->fetchColumn();
                    $ins = $pdo->prepare("
                        INSERT INTO Administrador (IDAdmin, CIadm, Rol, NomA, ApeA, EmailAd, PassAd, FchDesig)
                        VALUES (?, ?, 'Administrador', ?, ?, ?, ?, CURDATE())
                    ");
                    $ins->execute([
                        $nextId,
                        $ci,
                        $socio['PnomS'],
                        $socio['PapeS'],
                        $socio['EmailS'],
                        $socio['PassS']
                    ]);
                    $pdo->commit();
                    echo json_encode(['ok'=>true, 'mensaje'=>'Socio convertido en administrador']);
                    exit;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    echo json_encode(['error'=>'Error SQL: '.$e->getMessage()]);
                    exit;
                }                                  

                case 'administradores':
                    $stmt = $pdo->query("SELECT IDAdmin, PnomA, PapeA, EmailAd, FchDesig FROM Administrador");
                    echo json_encode($stmt->fetchAll());exit;
                
        default:
            echo json_encode(["error" => "Acción POST no válida"]);
            exit;
    }
}

if ($method === 'GET' && isset($_GET['accion'])) {
    switch ($_GET['accion']) {
        case 'aspirantes':
            $stmt = $pdo->query("SELECT CIaspi,PnomA,PapeA,EmailA,TelA,FchSoli FROM Aspirante");
            echo json_encode($stmt->fetchAll());
            exit;

            case 'socios':
                $stmt = $pdo->query("
                    SELECT 
                        s.CIsoc,
                        s.PnomS,
                        s.PapeS,
                        s.EmailS,
                        s.TelS,
                        s.EstadoSoc,
                        s.FchIngr,
                        -- concatenamos posibles unidades (si hay más de una)
                        TRIM(IFNULL(GROUP_CONCAT(DISTINCT u.NomLote SEPARATOR ', '), '')) AS Unidad,
                        CASE WHEN a.CIadm IS NOT NULL THEN 'Admin' ELSE 'Socio' END AS Rol
                    FROM Socio s
                    LEFT JOIN Obtiene o ON s.CIsoc = o.CIsoc
                    LEFT JOIN UnidadHabit u ON o.IDUni = u.IDUni
                    LEFT JOIN Administrador a ON s.CIsoc = a.CIadm
                    GROUP BY s.CIsoc, s.PnomS, s.PapeS, s.EmailS, s.TelS, s.EstadoSoc, s.FchIngr, a.CIadm
                    ORDER BY s.PnomS, s.PapeS
                ");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                exit;                                

        default:
            echo json_encode(["error" => "Listado no válido"]);
            exit;
    }
}

echo json_encode(["error" => "Ruta no válida o método incorrecto"]);
exit;
