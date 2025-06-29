<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/funciones.php';

requerirAdmin();

// Procesar formulario de agregar/editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'identificador' => $_POST['identificador'],
        'nombre' => $_POST['nombre'],
        'cargo' => $_POST['cargo'],
        'descripcion' => $_POST['descripcion'],
        'hobby' => $_POST['hobby'],
        'cumpleanos' => $_POST['cumpleanos'],
        'ingreso' => $_POST['ingreso'],
        'nivel' => $_POST['nivel'],
        'activo' => isset($_POST['activo']) ? 1 : 0
    ];

    // Manejar la subida de foto
    if (!empty($_FILES['foto']['name'])) {
        $resultado = subirFoto($_FILES['foto']);
        if (isset($resultado['error'])) {
            $error = $resultado['error'];
        } else {
            $datos['foto'] = $resultado['success'];
        }
    } elseif (!empty($_POST['foto_actual'])) {
        $datos['foto'] = $_POST['foto_actual'];
    } else {
        $datos['foto'] = 'img/perfil.png';
    }

    if (!isset($error)) {
        if (!empty($_POST['id'])) {
            if (actualizarColaborador($_POST['id'], $datos)) {
                header("Location: colaboradores.php?success=editado");
                exit;
            } else {
                $error = "Error al actualizar el colaborador";
            }
        } else {
            if (agregarColaborador($datos)) {
                header("Location: colaboradores.php?success=agregado");
                exit;
            } else {
                $error = "Error al agregar el colaborador";
            }
        }
    }
}

// Procesar activar/desactivar
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    if ($_GET['accion'] === 'activar') {
        cambiarEstadoColaborador($id, 1);
    } elseif ($_GET['accion'] === 'desactivar') {
        cambiarEstadoColaborador($id, 0);
    } elseif ($_GET['accion'] === 'eliminar') {
        eliminarColaborador($id);
    }
    
    header("Location: colaboradores.php");
    exit;
}

// Función de búsqueda mejorada
function buscarColaboradores($busqueda) {
    global $db;
    
    $sql = "SELECT * FROM colaboradores 
            WHERE (nombre LIKE :busqueda 
            OR cargo LIKE :busqueda 
            OR nivel LIKE :busqueda 
            OR identificador LIKE :busqueda
            OR descripcion LIKE :busqueda
            OR hobby LIKE :busqueda)
            ORDER BY nivel, nombre";
    
    $stmt = $db->prepare($sql);
    $paramBusqueda = "%$busqueda%";
    $stmt->bindParam(':busqueda', $paramBusqueda);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener parámetro de búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Obtener colaboradores (con búsqueda si existe)
$colaboradores = $busqueda ? buscarColaboradores($busqueda) : obtenerColaboradores(false);

// Obtener colaborador para editar
$colaboradorEditar = null;
if (isset($_GET['editar'])) {
    $colaboradorEditar = obtenerColaborador($_GET['editar']);
    if (!$colaboradorEditar) {
        header("Location: colaboradores.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Colaboradores</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        h1 {
            color: #2c3e50;
            margin: 0;
        }
        .welcome {
            color: #7f8c8d;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .btn-warning {
            background-color: #f39c12;
        }
        .btn-warning:hover {
            background-color: #d35400;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .inactivo {
            opacity: 0.6;
            background-color: #f9f9f9;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            min-height: 100px;
        }
        .form-actions {
            text-align: right;
        }
        .error {
            color: #e74c3c;
            background-color: #fdecea;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            color: #2ecc71;
            background-color: #e8f8f0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
            margin-bottom: 10px;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .search-container button {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .search-container button:hover {
            background-color: #2980b9;
        }
        .no-results {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-style: italic;
        }
        .search-info {
            margin-bottom: 15px;
            color: #3498db;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestión de Colaboradores</h1>
            <div>
                <span class="welcome">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                <a href="dashboard.php" class="btn"><i class="fas fa-arrow-left"></i> Volver</a>
                <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </header>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                <?php 
                if ($_GET['success'] === 'agregado') {
                    echo "Colaborador agregado correctamente";
                } elseif ($_GET['success'] === 'editado') {
                    echo "Colaborador actualizado correctamente";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h2><?php echo $colaboradorEditar ? 'Editar' : 'Agregar'; ?> Colaborador</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $colaboradorEditar ? $colaboradorEditar['id'] : ''; ?>">
                
                <div class="form-group">
                    <label for="identificador">Identificador (único)</label>
                    <input type="text" id="identificador" name="identificador" required 
                           value="<?php echo $colaboradorEditar ? htmlspecialchars($colaboradorEditar['identificador']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo $colaboradorEditar ? htmlspecialchars($colaboradorEditar['nombre']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="cargo">Cargo</label>
                    <input type="text" id="cargo" name="cargo" required 
                           value="<?php echo $colaboradorEditar ? htmlspecialchars($colaboradorEditar['cargo']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="nivel">Nivel</label>
                    <select id="nivel" name="nivel" required>
                        <option value="jefe" <?php echo ($colaboradorEditar && $colaboradorEditar['nivel'] === 'jefe') ? 'selected' : ''; ?>>Jefe</option>
                        <option value="supervisor" <?php echo ($colaboradorEditar && $colaboradorEditar['nivel'] === 'supervisor') ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="tecnico" <?php echo ($colaboradorEditar && $colaboradorEditar['nivel'] === 'tecnico') ? 'selected' : ''; ?>>Técnico</option>
                        <option value="hd" <?php echo ($colaboradorEditar && $colaboradorEditar['nivel'] === 'hd') ? 'selected' : ''; ?>>HD</option>
                        <option value="noc" <?php echo ($colaboradorEditar && $colaboradorEditar['nivel'] === 'noc') ? 'selected' : ''; ?>>NOC</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"><?php echo $colaboradorEditar ? htmlspecialchars($colaboradorEditar['descripcion']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="hobby">Hobby</label>
                    <input type="text" id="hobby" name="hobby" 
                           value="<?php echo $colaboradorEditar ? htmlspecialchars($colaboradorEditar['hobby']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="cumpleanos">Fecha de Cumpleaños</label>
                    <input type="date" id="cumpleanos" name="cumpleanos" required 
                           value="<?php echo $colaboradorEditar ? htmlspecialchars($colaboradorEditar['cumpleanos']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="ingreso">Fecha de Ingreso</label>
                    <input type="date" id="ingreso" name="ingreso" required 
                           value="<?php echo $colaboradorEditar ? htmlspecialchars($colaboradorEditar['ingreso']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="foto">Foto</label>
                    <?php if ($colaboradorEditar && $colaboradorEditar['foto']): ?>
                        <input type="hidden" name="foto_actual" value="<?php echo htmlspecialchars($colaboradorEditar['foto']); ?>">
                        <img src="../<?php echo htmlspecialchars($colaboradorEditar['foto']); ?>" class="avatar-preview" id="avatar-preview">
                    <?php else: ?>
                        <img src="../img/perfil.png" class="avatar-preview" id="avatar-preview">
                    <?php endif; ?>
                    <input type="file" id="foto" name="foto" accept="image/*" onchange="previewImage(this)">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="activo" <?php echo ($colaboradorEditar && $colaboradorEditar['activo']) || !$colaboradorEditar ? 'checked' : ''; ?>> 
                        Activo (aparece en el organigrama)
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn"><?php echo $colaboradorEditar ? 'Actualizar' : 'Agregar'; ?></button>
                    <?php if ($colaboradorEditar): ?>
                        <a href="colaboradores.php" class="btn btn-danger">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <h2>Lista de Colaboradores</h2>
        
        <!-- Barra de búsqueda mejorada -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="text" name="busqueda" placeholder="Buscar colaboradores..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>" autocomplete="off">
                <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                <?php if ($busqueda): ?>
                    <a href="colaboradores.php" class="btn btn-warning"><i class="fas fa-times"></i> Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($busqueda): ?>
            <div class="search-info">
                Mostrando resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                <?php if (!empty($colaboradores)): ?>
                    - <?php echo count($colaboradores); ?> resultado(s) encontrado(s)
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Nivel</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($colaboradores)): ?>
                    <tr>
                        <td colspan="5" class="no-results">
                            <?php if ($busqueda): ?>
                                No se encontraron colaboradores que coincidan con "<?php echo htmlspecialchars($busqueda); ?>"
                            <?php else: ?>
                                No hay colaboradores registrados
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($colaboradores as $colab): ?>
                        <tr class="<?php echo !$colab['activo'] ? 'inactivo' : ''; ?>">
                            <td><?php echo htmlspecialchars($colab['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($colab['cargo']); ?></td>
                            <td><?php echo ucfirst($colab['nivel']); ?></td>
                            <td>
                                <span class="badge <?php echo $colab['activo'] ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo $colab['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="colaboradores.php?editar=<?php echo $colab['id']; ?>" class="btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($colab['activo']): ?>
                                    <a href="colaboradores.php?accion=desactivar&id=<?php echo $colab['id']; ?>" class="btn btn-danger" title="Desactivar">
                                        <i class="fas fa-eye-slash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="colaboradores.php?accion=activar&id=<?php echo $colab['id']; ?>" class="btn btn-success" title="Activar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="colaboradores.php?accion=eliminar&id=<?php echo $colab['id']; ?>" class="btn btn-danger" 
                                   onclick="return confirm('¿Estás seguro de eliminar este colaborador?');" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('avatar-preview');
            const file = input.files[0];
            const reader = new FileReader();

            reader.onloadend = function() {
                preview.src = reader.result;
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "../img/perfil.png";
            }
        }

        // Opcional: Focus automático en el campo de búsqueda
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="busqueda"]');
            if (searchInput) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>