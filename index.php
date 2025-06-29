<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/funciones.php';

// Obtener colaboradores por niveles
$gerentes = obtenerColaboradoresPorNivel('gerente');
$jefes = obtenerColaboradoresPorNivel('jefe');
$supervisores = obtenerColaboradoresPorNivel('supervisor');
$tecnicos = obtenerColaboradoresPorNivel('tecnico');
$backoffice = obtenerColaboradoresPorNivel('backoffice');
$noc = obtenerColaboradoresPorNivel('noc');
$hd = obtenerColaboradoresPorNivel('hd');

// Preparar datos para JavaScript
$equipo = [];
$todosColaboradores = array_merge($gerentes, $jefes, $supervisores, $tecnicos, $backoffice, $noc, $hd);
foreach ($todosColaboradores as $colab) {
    $equipo[$colab['identificador']] = [
        'nombre' => $colab['nombre'],
        'cargo' => $colab['cargo'],
        'descripcion' => $colab['descripcion'],
        'hobby' => $colab['hobby'],
        'cumpleanos' => date('d/m/Y', strtotime($colab['cumpleanos'])),
        'ingreso' => date('d/m/Y', strtotime($colab['ingreso'])),
        'foto' => $colab['foto'] ?: 'img/fotos/default.png'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organigrama de Soporte - HD - NOC</title>
  <link rel="stylesheet" href="css/estilo.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
  

</head>
<body>
  <!-- Menú de login con ícono -->
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1000;">
      <div class="dropdown">
          <!-- Botón combinado con ícono de login -->
          <button class="btn btn-primary dropdown-toggle d-flex align-items-center" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-user me-2"></i> <!-- Ícono de login -->
              <span class="navbar-toggler-icon ms-1"></span> <!-- Ícono hamburguesa -->
          </button>
          
          <!-- Menú desplegable -->
          <ul class="dropdown-menu dropdown-menu-end">
              <?php if (estaAutenticado()): ?>
                  <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Panel Admin</a></li>
                  <li><a class="dropdown-item" href="admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
              <?php else: ?>
                  <li><a class="dropdown-item" href="admin/login.php"><i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión</a></li>
              <?php endif; ?>
          </ul>
      </div>
  </div>

  <div class="container">
    <header>
      <h1>EQUIPO DE SOPORTE - HD - NOC</h1>
      <?php if (estaAutenticado()): ?>
        <a href="admin/dashboard.php" class="admin-link"><i class="fas fa-cog"></i> Panel Admin</a>
      <?php endif; ?>
      <div class="proximos-cumples">
        <h3><i class="fas fa-birthday-cake"></i> Próximos cumpleaños</h3>
        <div id="cumpleanos-notificacion"></div>
      </div>
    </header>

    <main>
      <!-- Nivel Gerente -->
      <?php if (!empty($gerentes)): ?>
      <div class="nivel">
        <div class="nivel-titulo">Gerente de Infraestructura y Soporte</div>
        <div class="miembros-horizontal">
          <?php foreach ($gerentes as $colab): ?>
          <div class="miembro" onclick="mostrarPerfil('<?= $colab['identificador'] ?>')">
            <div class="avatar"><i class="fas fa-user-tie"></i></div>
            <div class="nombre"><?= htmlspecialchars($colab['nombre']) ?></div>
            <div class="cargo"><?= htmlspecialchars($colab['cargo']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Nivel Jefe -->
      <?php if (!empty($jefes)): ?>
      <div class="nivel">
        <div class="nivel-titulo">JEFE SOP - HD - NOC</div>
        <div class="miembros-horizontal">
          <?php foreach ($jefes as $colab): ?>
          <div class="miembro" onclick="mostrarPerfil('<?= $colab['identificador'] ?>')">
            <div class="avatar"><i class="fas fa-user-tie"></i></div>
            <div class="nombre"><?= htmlspecialchars($colab['nombre']) ?></div>
            <div class="cargo"><?= htmlspecialchars($colab['cargo']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Nivel Supervisores -->
      <?php if (!empty($supervisores)): ?>
      <div class="nivel">
        <div class="nivel-titulo">SUPERVISORES</div>
        <div class="miembros-horizontal">
          <?php foreach ($supervisores as $colab): ?>
          <div class="miembro" onclick="mostrarPerfil('<?= $colab['identificador'] ?>')">
            <div class="avatar"><i class="<?= obtenerIconoPorNivel($colab['nivel']) ?>"></i></div>
            <div class="nombre"><?= htmlspecialchars($colab['nombre']) ?></div>
            <div class="cargo"><?= htmlspecialchars($colab['cargo']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Nivel Técnicos -->
      <?php if (!empty($tecnicos)): ?>
      <div class="nivel">
        <div class="nivel-titulo">TÉCNICOS DE SOPORTE</div>
        <div class="miembros-horizontal">
          <?php foreach ($tecnicos as $colab): ?>
          <div class="miembro" onclick="mostrarPerfil('<?= $colab['identificador'] ?>')">
            <div class="avatar"><i class="<?= obtenerIconoPorNivel($colab['nivel']) ?>"></i></div>
            <div class="nombre"><?= htmlspecialchars($colab['nombre']) ?></div>
            <div class="cargo"><?= htmlspecialchars($colab['cargo']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <hr>

      <!-- Back Office -->
      <?php if (!empty($backoffice)): ?>
      <div class="nivel">
        <div class="nivel-titulo">Back Office (BO)</div>
        <div class="miembros-horizontal">
          <?php foreach ($backoffice as $colab): ?>
          <div class="miembro" onclick="mostrarPerfil('<?= $colab['identificador'] ?>')">
            <div class="avatar"><i class="<?= obtenerIconoPorNivel($colab['nivel']) ?>"></i></div>
            <div class="nombre"><?= htmlspecialchars($colab['nombre']) ?></div>
            <div class="cargo"><?= htmlspecialchars($colab['cargo']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Equipo NOC -->
      <?php if (!empty($noc)): ?>
      <div class="nivel">
        <div class="nivel-titulo">EQUIPO NOC</div>
        <div class="miembros-horizontal">
          <?php foreach ($noc as $colab): ?>
          <div class="miembro" onclick="mostrarPerfil('<?= $colab['identificador'] ?>')">
            <div class="avatar"><i class="<?= obtenerIconoPorNivel($colab['nivel']) ?>"></i></div>
            <div class="nombre"><?= htmlspecialchars($colab['nombre']) ?></div>
            <div class="cargo"><?= htmlspecialchars($colab['cargo']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Equipo HD -->
      <?php if (!empty($hd)): ?>
      <div class="nivel">
        <div class="nivel-titulo">EQUIPO HD</div>
        <div class="miembros-horizontal">
          <?php foreach ($hd as $colab): ?>
          <div class="miembro" onclick="mostrarPerfil('<?= $colab['identificador'] ?>')">
            <div class="avatar"><i class="<?= obtenerIconoPorNivel($colab['nivel']) ?>"></i></div>
            <div class="nombre"><?= htmlspecialchars($colab['nombre']) ?></div>
            <div class="cargo"><?= htmlspecialchars($colab['cargo']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </main>
  </div>

  <!-- Ventana modal de perfil -->
  <div id="perfil-modal" class="modal">
    <div class="modal-contenido">
      <span class="cerrar" onclick="cerrarModal()">&times;</span>
      <div class="perfil-header">
        <div class="avatar-grande">
          <img id="perfil-foto" src="" alt="Foto del colaborador">
        </div>
        <div>
          <h2 id="perfil-nombre"></h2>
          <h3 id="perfil-cargo"></h3>
        </div>
      </div>
      <div class="perfil-detalle">
        <p><strong>Descripción:</strong> <span id="perfil-descripcion"></span></p>
        <p><strong>Hobby:</strong> <span id="perfil-hobby"></span></p>
        <p><strong>Cumpleaños:</strong> <span id="perfil-cumpleanos"></span></p>
        <p><strong>Ingreso:</strong> <span id="perfil-ingreso"></span></p>
      </div>
      <div class="notificacion-tiempo" id="tiempo-empresa"></div>
    </div>
  </div>

  <script>
  // Convertir el array PHP a JSON para JavaScript
  const equipoData = <?php echo json_encode($equipo); ?>;
  </script>
  <script src="js/script.js"></script>
</body>
</html>