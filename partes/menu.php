<?php
$nivelUsuario = $_SESSION['Usuario_Id']; // Ej: 1 = admin, 2 = operador
$rolesFuncionales = $_SESSION['Usuario_Roles_Funcionales']; // Array con roles, ej: ['Encargado de Personal', 'Encargado de Licencias']

// Función para validar acceso según nivel y rol
function TieneAcceso($nivelUsuario, $rolesFuncionales, $rolesNecesarios = [])
{
  // Si es admin, tiene acceso a todo
  if ($nivelUsuario == 1) return true;

  // Si no es admin y no hay roles requeridos, no tiene acceso
  if (empty($rolesNecesarios)) return false;

  // Verificar si tiene al menos un rol requerido
  return count(array_intersect($rolesFuncionales, $rolesNecesarios)) > 0;
}
?>

<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="index.php">
        <i class="bi bi-grid"></i>
        <span>Panel</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <!-- Gestor de Personal - Solo para administradores y RRHH -->
    <?php if (TieneAcceso($nivelUsuario, $rolesFuncionales, ['Encargado de Personal', 'Administrador', 'Gerente de Departamento'])): ?>
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
              <i class="bi bi-file-earmark-plus"></i><span>Registrar Familiar</span>
            </a>
          </li>
        </ul>
      </li><!-- End Gestor de Personal -->
      <!-- End Gestor de Personal -->
    <?php endif; ?>

    <?php if (TieneAcceso($nivelUsuario, $rolesFuncionales, ['Encargado de Movimientos', 'Administrador', 'Gerente de Departamento'])): ?>
      <!-- Gestor Movimientos -->
      <li class="nav-item">

        <a href="#forms-nav-movimientos" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                            'asistencia.php',
                                                            'Viaticos.php',
                                                            'Anticipo.php',
                                                            'HorasExtras.php',
                                                            'ObraSocial.php',
                                                            'Embargos.php',
                                                            'Sanciones.php'
                                                          ]) ? 'active' : ''; ?>" data-bs-toggle="collapse" aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                                                                                              'asistencia.php',
                                                                                                                              'Viaticos.php',
                                                                                                                              'Anticipo.php',
                                                                                                                              'HorasExtras.php',
                                                                                                                              'ObraSocial.php',
                                                                                                                              'Embargos.php',
                                                                                                                              'Sanciones.php'
                                                                                                                            ]) ? 'true' : 'false'; ?>">
          <i class="bi bi-globe2"></i><span>Gestor Movimientos</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav-movimientos" class="nav-content collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                                      'asistencia.php',
                                                                      'Viaticos.php',
                                                                      'Anticipo.php',
                                                                      'HorasExtras.php',
                                                                      'ObraSocial.php',
                                                                      'Embargos.php',
                                                                      'Sanciones.php'
                                                                    ]) ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
          <li>
            <a href="asistencia.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'asistencia.php' ? 'active' : ''; ?>">
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
        </ul>
      </li>
    <?php endif; ?>
    
    <?php if (TieneAcceso($nivelUsuario, $rolesFuncionales, ['Encargado de Licencias', 'Administrador', 'Gerente de Departamento'])): ?>
      <!-- Gestor Licencias-->
      <li class="nav-item">

        <a href="#forms-nav-licencias" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                            'Vacaciones.php',
                                                            'Licencias.php'
                                                          ]) ? 'active' : ''; ?>" data-bs-toggle="collapse" aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                                                                                              'Vacaciones.php',
                                                                                                                              'Licencias.php'
                                                                                                                            ]) ? 'true' : 'false'; ?>">
          <i class="bi bi-globe2"></i><span>Gestor Licencias</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav-licencias" class="nav-content collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), [
                                                                      'Vacaciones.php',
                                                                      'Licencias.php'
                                                                    ]) ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
          <li>
            <a href="Vacaciones.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Vacaciones.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Vacaciones</span>
            </a>
          </li>
          <li>
            <a href="Licencias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Licencias.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Licencias</span>
            </a>
          </li>
        </ul>
      </li>
    <?php endif; ?>

    <!-- Gestor de Reportes -->
    <?php if (TieneAcceso($nivelUsuario, $rolesFuncionales, ['Gerente de Departamento', 'Administrador'])): ?>
      <!-- Gestor de Reportes -->
      <li class="nav-item">
        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), [ 'Reportes.php','Preliquidacion.php', 'reporte_estadistico.php', 'Reporte_Organigrama.php', 'Reporte_asistencia_global.php','Reporte_rotacion_personal.php','Informe_Ultimos_e.php' ]) ? 'active' : ''; ?>"
          href="#forms-nav-reportes"
          data-bs-toggle="collapse"
          aria-expanded="<?php echo in_array(basename($_SERVER['PHP_SELF']), ['Reportes.php','Preliquidacion.php', 'reporte_estadistico.php', 'Reporte_Organigrama.php', 'Reporte_asistencia_global.php','Reporte_rotacion_personal.php','Informe_Ultimos_e.php' ]) ? 'true' : 'false'; ?>">
          <i class="bi bi-file-earmark"></i><span>Gestor de Reportes</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="forms-nav-reportes" class="nav-content collapse <?php echo in_array(basename($_SERVER['PHP_SELF']), ['Reportes.php','Preliquidacion.php', 'reporte_estadistico.php', 'Reporte_Organigrama.php', 'Reporte_asistencia_global.php','Reporte_rotacion_personal.php','Informe_Ultimos_e.php']) ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">

          <li>
            <a href="Reportes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Reportes.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Reportes por empleado </span>
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
            <a href="Reporte_Organigrama.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Reporte_Organigrama.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Reporte Estructural de Personal </span>
            </a>
          </li>
          <li>
            <a href="Reporte_asistencia_global.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Reporte_asistencia_global.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Reporte Asistencia </span>
            </a>
          </li>
          <li>
            <a href="Reporte_rotacion_personal.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Reporte_rotacion_personal.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Reporte de Rotación</span>
            </a>
          </li>
          <li>
            <a href="Informe_Ultimos_e.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Informe_Ultimos_e.php' ? 'active' : ''; ?>">
              <i class="bi bi-file-earmark-plus"></i><span>Últimos empleados registrados</span>
            </a>
          </li>
        </ul>
      </li>
    <?php endif; ?>

    <?php if ($nivelUsuario == 1): ?>
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