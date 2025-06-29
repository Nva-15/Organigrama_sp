<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requerirAutenticacion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
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
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        .card-icon {
            font-size: 40px;
            color: #3498db;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Panel de Administración</h1>
            <div>
                <span class="welcome">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </header>

        <div class="dashboard-cards">
            <a href="colaboradores.php" class="card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h2>Gestión de Colaboradores</h2>
                <p>Administra los colaboradores del organigrama</p>
            </a>

            <a href="../index.php" target="_blank" class="card">
                <div class="card-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h2>Ver Organigrama</h2>
                <p>Visualiza cómo ven los usuarios el organigrama</p>
            </a>
        </div>
    </div>
</body>
</html>