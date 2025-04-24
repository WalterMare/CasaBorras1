<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="index.php">
        <i class="bi bi-grid"></i>
        <span>Panel</span>
      </a>
    </li><!-- End Dashboard Nav -->
    <?php
    // Obtener el nivel del usuario desde la sesión
    $nivelUsuario = $_SESSION['Usuario_Id'];
    ?>
    <!-- Gestor de Personal - Solo para administradores y RRHH -->
    
      <!-- Gestor de Personal -->
      <li class="nav-item">
        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'Empleado_carga.php' || basename($_SERVER['PHP_SELF']) == 'Familiar_carga.php' || basename($_SERVER['PHP_SELF']) == 'Usuario.php' || basename($_SERVER['PHP_SELF']) == 'Listado_empleados.php') ? 'active' : ''; ?>" data-bs-target="#forms-nav-personal" data-bs-toggle="collapse" href="#">
          <i class="bi bi-truck"></i><span>Gestor de personal</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav-personal" class="nav-content collapse <?php echo (basename($_SERVER['PHP_SELF']) == 'Empleado_carga.php' || basename($_SERVER['PHP_SELF']) == 'Familiar_carga.php' || basename($_SERVER['PHP_SELF']) == 'Usuario.php' || basename($_SERVER['PHP_SELF']) == 'Listado_empleados.php') ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
          <li>
            <a href="Listado_empleados.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listado_empleados.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Listado de Empleados</span>
            </a>
          </li>
          <li>
            <a href="Empleado_carga.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Empleado_carga.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Registrar Empleado</span>
            </a>
          </li>
          <li>
            <a href="Familiar_carga.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Familiar_carga.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>RegistrarFamiliar</span>
            </a>
          </li>
        </ul>
      </li><!-- End Gestor de Personal -->
  <!-- End Gestor de Personal -->

      <!-- Gestor Movimientos -->
      <li class="nav-item">

        <a href="#forms-nav-movimientos" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                            'Asistencia_Empleados.php',
                                                            'Viaticos.php',
                                                            'Anticipo.php',
                                                            'HorasExtras.php',
                                                            'ObraSocial.php',
                                                            'Embargos.php',
                                                            'Sanciones.php',
                                                            'Registrar_Vacaciones.php',
                                                            'Licencias.php'
                                                          ]) ? 'active' : ''; ?>" data-bs-toggle="collapse" aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                                                                                              'Asistencia_Empleados.php',
                                                                                                                              'Viaticos.php',
                                                                                                                              'Anticipo.php',
                                                                                                                              'HorasExtras.php',
                                                                                                                              'ObraSocial.php',
                                                                                                                              'Embargos.php',
                                                                                                                              'Sanciones.php',
                                                                                                                              'Registrar_Vacaciones.php',
                                                                                                                              'Licencias.php'
                                                                                                                            ]) ? 'true' : 'false'; ?>">
          <i class="bi bi-globe2"></i><span>Gestor Movimientos</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav-movimientos" class="nav-content collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                                      'Asistencia_Empleados.php',
                                                                      'Viaticos.php',
                                                                      'Anticipo.php',
                                                                      'HorasExtras.php',
                                                                      'ObraSocial.php',
                                                                      'Embargos.php',
                                                                      'Sanciones.php',
                                                                      'Registrar_Vacaciones.php',
                                                                      'Licencias.php'
                                                                    ]) ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
          <li>
            <a href="asistencia_listado.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'asistencia_listado.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Asistencia de Empleados</span>
            </a>
          </li>
          <li>
            <a href="Viaticos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Viaticos.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Viáticos</span>
            </a>
          </li>
          <li>
            <a href="Anticipo.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Anticipo.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Anticipos</span>
            </a>
          </li>
          <li>
            <a href="HorasExtras.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'HorasExtras.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Horas Extras</span>
            </a>
          </li>
          <li>
            <a href="ObraSocial.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ObraSocial.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Obra Social</span>
            </a>
          </li>
          <li>
            <a href="Embargos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Embargos.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Embargos</span>
            </a>
          </li>
          <li>
            <a href="Sanciones.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Sanciones.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Sanciones</span>
            </a>
          </li>
          <li>
            <a href="Registrar_Vacaciones.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Registrar_Vacaciones.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Registrar Vacaciones</span>
            </a>
          </li>
          <li>
            <a href="Licencias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Licencias.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Licencias</span>
            </a>
          </li>
        </ul>
      </li>

    <!-- Gestor de Reportes -->
    <?php if ($nivelUsuario == 1): ?>
      <!-- Gestor de Reportes -->
      <li class="nav-item">
        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['Informe_Ultimos_e.php', 'Reportes.php', 'reporte_estadistico.php', 'Reporte_Ausencias_Empleados.php', 'Preliquidacion.php']) ? 'active' : ''; ?>"
          href="#forms-nav-reportes"
          data-bs-toggle="collapse"
          aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['Informe_Ultimos_e.php', 'Reportes.php', 'reporte_estadistico.php', 'Reporte_Ausencias_Empleados.php', 'Preliquidacion.php']) ? 'true' : 'false'; ?>">
          <i class="bi bi-file-earmark"></i><span>Gestor de Reportes</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav-reportes" class="nav-content collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['Informe_Ultimos_e.php', 'Reportes.php', 'reporte_estadistico.php', 'Reporte_Ausencias_Empleados.php', 'Preliquidacion.php']) ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
          
          <li>
            <a href="Reportes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Reportes.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Generar Reportes por empleado </span>
            </a>
          </li>
          <li>
            <a href="Preliquidacion.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Preliquidacion.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Preliquidación </span>
            </a>
          </li>
          <li>
            <a href="reporte_estadistico.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reporte_estadistico.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Reporte Estadístico de Licencias </span>
            </a>
          </li>
          <li>
            <a href="Reporte_Ausencias_Empleados.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Reporte_Ausencias_Empleados.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Reporte Asistencia </span>
            </a>
          </li>
          <li>
            <a href="Informe_Ultimos_e.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Informe_Ultimos_e.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Últimos empleados registrados</span>
            </a>
          </li>
        </ul>
      </li>
      <!-- Gestor de Usuarios -->
      <li class="nav-item">
        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'Usuario.php') ? 'active' : ''; ?>" data-bs-target="#forms-nav-usuario" data-bs-toggle="collapse" href="#">
          <i class="bi bi-truck"></i><span>Gestor de Usuarios</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav-usuario" class="nav-content collapse <?php echo (basename($_SERVER['PHP_SELF'])  == 'Usuario.php') ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
        <li>
            <a href="Usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Usuarios.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Usuarios</span>
            </a>
          </li>
        </ul>
    <?php endif; ?>
  </ul>
</aside>