<?php
// CRUD API for table: tbl_hishuanigami

require 'db_config.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed', 'details' => $e->getMessage()]);
    exit;
}

// CREATE
function create($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['error' => 'Invalid input']);
        return;
    }

    $sql = "INSERT INTO tbl_hishuanigami (username, email) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute(array_values($data));
        echo json_encode(['id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Insert failed', 'details' => $e->getMessage()]);
    }
}

// READ ALL
function readAll($pdo) {
    $stmt = $pdo->query("SELECT * FROM tbl_hishuanigami");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// READ SINGLE
function readSingle($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM tbl_hishuanigami WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}

// UPDATE
function update($pdo, $id) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['error' => 'Invalid input']);
        return;
    }

    $setString = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
    $sql = "UPDATE tbl_hishuanigami SET $setString WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([...array_values($data), $id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Update failed', 'details' => $e->getMessage()]);
    }
}

// DELETE
function delete($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM tbl_hishuanigami WHERE id = ?");
    try {
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Delete failed', 'details' => $e->getMessage()]);
    }
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'create':
        create($pdo);
        break;
    case 'read':
        if ($id) {
            readSingle($pdo, $id);
        } else {
            readAll($pdo);
        }
        break;
    case 'update':
        if ($id) {
            update($pdo, $id);
        } else {
            echo json_encode(['error' => 'ID is required for update']);
        }
        break;
    case 'delete':
        if ($id) {
            delete($pdo, $id);
        } else {
            echo json_encode(['error' => 'ID is required for delete']);
        }
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>