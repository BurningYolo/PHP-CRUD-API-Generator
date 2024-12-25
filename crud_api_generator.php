<?php
// Dynamic CRUD API Generator

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tableName = htmlspecialchars($_POST['table_name'] ?? '');
    $columns = htmlspecialchars($_POST['columns'] ?? '');
    $primaryKey = htmlspecialchars($_POST['primary_key'] ?? '');

    if (empty($tableName) || empty($columns) || empty($primaryKey)) {
        die('Error: Table name, columns, and primary key are required.');
    }

    $apiCode = generateCRUD($tableName, $columns, $primaryKey);
    $fileName = $tableName . "_crud_api.php";

    if (file_put_contents($fileName, $apiCode)) {
        echo "File '$fileName' created successfully.";
    } else {
        echo "Error: Unable to create file.";
    }
    exit;
}

function generateCRUD($tableName, $columns, $primaryKey)
{
    $columnsArray = explode(',', $columns);
    $columnsArray = array_map('trim', $columnsArray);
    $columnsList = implode(', ', $columnsArray);
    $placeholders = implode(', ', array_fill(0, count($columnsArray), '?'));

    $code = <<<PHP
<?php
// CRUD API for table: $tableName

require 'db_config.php';

header('Content-Type: application/json');

try {
    \$pdo = new PDO(\$dsn, \$username, \$password, \$options);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException \$e) {
    echo json_encode(['error' => 'Database connection failed', 'details' => \$e->getMessage()]);
    exit;
}

// CREATE
function create(\$pdo) {
    \$data = json_decode(file_get_contents('php://input'), true);

    if (!\$data) {
        echo json_encode(['error' => 'Invalid input']);
        return;
    }

    \$sql = "INSERT INTO $tableName ($columnsList) VALUES ($placeholders)";
    \$stmt = \$pdo->prepare(\$sql);

    try {
        \$stmt->execute(array_values(\$data));
        echo json_encode(['id' => \$pdo->lastInsertId()]);
    } catch (PDOException \$e) {
        echo json_encode(['error' => 'Insert failed', 'details' => \$e->getMessage()]);
    }
}

// READ ALL
function readAll(\$pdo) {
    \$stmt = \$pdo->query("SELECT * FROM $tableName");
    echo json_encode(\$stmt->fetchAll(PDO::FETCH_ASSOC));
}

// READ SINGLE
function readSingle(\$pdo, \$id) {
    \$stmt = \$pdo->prepare("SELECT * FROM $tableName WHERE $primaryKey = ?");
    \$stmt->execute([\$id]);
    echo json_encode(\$stmt->fetch(PDO::FETCH_ASSOC));
}

// UPDATE
function update(\$pdo, \$id) {
    \$data = json_decode(file_get_contents('php://input'), true);

    if (!\$data) {
        echo json_encode(['error' => 'Invalid input']);
        return;
    }

    \$setString = implode(', ', array_map(fn(\$col) => "\$col = ?", array_keys(\$data)));
    \$sql = "UPDATE $tableName SET \$setString WHERE $primaryKey = ?";
    \$stmt = \$pdo->prepare(\$sql);

    try {
        \$stmt->execute([...array_values(\$data), \$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException \$e) {
        echo json_encode(['error' => 'Update failed', 'details' => \$e->getMessage()]);
    }
}

// DELETE
function delete(\$pdo, \$id) {
    \$stmt = \$pdo->prepare("DELETE FROM $tableName WHERE $primaryKey = ?");
    try {
        \$stmt->execute([\$id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException \$e) {
        echo json_encode(['error' => 'Delete failed', 'details' => \$e->getMessage()]);
    }
}

\$action = \$_GET['action'] ?? '';
\$id = \$_GET['id'] ?? null;

switch (\$action) {
    case 'create':
        create(\$pdo);
        break;
    case 'read':
        if (\$id) {
            readSingle(\$pdo, \$id);
        } else {
            readAll(\$pdo);
        }
        break;
    case 'update':
        if (\$id) {
            update(\$pdo, \$id);
        } else {
            echo json_encode(['error' => 'ID is required for update']);
        }
        break;
    case 'delete':
        if (\$id) {
            delete(\$pdo, \$id);
        } else {
            echo json_encode(['error' => 'ID is required for delete']);
        }
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
PHP;

    return $code;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD API Generator</title>
</head>
<body>
    <h1>CRUD API Generator</h1>
    <form method="POST">
        <label for="table_name">Table Name:</label>
        <input type="text" name="table_name" id="table_name" required><br><br>

        <label for="columns">Columns (comma-separated):</label>
        <input type="text" name="columns" id="columns" required><br><br>

        <label for="primary_key">Primary Key:</label>
        <input type="text" name="primary_key" id="primary_key" required><br><br>

        <button type="submit">Generate API</button>
    </form>
</body>
</html>
