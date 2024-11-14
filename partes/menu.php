<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <!-- Dashboard -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="index.php">
        <i class="bi bi-grid"></i>
        <span>Panel</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <!-- Gestor de Personal -->
    <li class="nav-item">
      <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'Empleado_carga.php' || basename($_SERVER['PHP_SELF']) == 'Familiar_carga.php' || basename($_SERVER['PHP_SELF']) == 'Usuario.php' || basename($_SERVER['PHP_SELF']) == 'Listado_empleados.php') ? 'active' : ''; ?>" data-bs-target="#forms-nav-personal" data-bs-toggle="collapse" href="#">
        <i class="bi bi-truck"></i><span>Gestor de personal</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="forms-nav-personal" class="nav-content collapse <?php echo (basename($_SERVER['PHP_SELF']) == 'Empleado_carga.php' || basename($_SERVER['PHP_SELF']) == 'Familiar_carga.php' || basename($_SERVER['PHP_SELF']) == 'Usuario.php' || basename($_SERVER['PHP_SELF']) == 'Listado_empleados.php') ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
        <li>
          <a href="Empleado_carga.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Empleado_carga.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Cargar nuevo Empleado</span>
          </a>
        </li>
        <li>
          <a href="Familiar_carga.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Familiar_carga.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Cargar Familiar</span>
          </a>
        </li>
        <li>
          <a href="Usuario.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Usuario.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Usuario</span>
          </a>
        </li>
        <li>
          <a href="Listado_empleados.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listado_empleados.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Listado de Empleados</span>
          </a>
        </li>
      </ul>
    </li><!-- End Gestor de Personal -->

    <!-- Gestor Movimientos -->
    <li class="nav-item">
      <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'Registrar_viatico.php' || basename($_SERVER['PHP_SELF']) == 'Listado_Viatico_Empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Anticipo.php' || basename($_SERVER['PHP_SELF']) == 'Listado_Anticipo_Empleado.php' || basename($_SERVER['PHP_SELF']) == 'RegistrarHoraExtra.php' || basename($_SERVER['PHP_SELF']) == 'Listado_HorasExtra_Empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Obrasocial.php' || basename($_SERVER['PHP_SELF']) == 'Listado_Obrasocial_Empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Embargo.php' || basename($_SERVER['PHP_SELF']) == 'Listado_Embargo_empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Sanciones.php' || basename($_SERVER['PHP_SELF']) == 'Listar_sanciones_empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Licencia.php' || basename($_SERVER['PHP_SELF']) == 'Listar_Licencias.php') ? 'active' : ''; ?>" data-bs-target="#forms-nav-movimientos" data-bs-toggle="collapse" href="#">
        <i class="bi bi-globe2"></i><span>Gestor Movimientos</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="forms-nav-movimientos" class="nav-content collapse <?php echo (basename($_SERVER['PHP_SELF']) == 'Registrar_viatico.php' || basename($_SERVER['PHP_SELF']) == 'Listado_Viatico_Empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Anticipo.php' || basename($_SERVER['PHP_SELF']) == 'Listado_Anticipo_Empleado.php' || basename($_SERVER['PHP_SELF']) == 'RegistrarHoraExtra.php' || basename($_SERVER['PHP_SELF']) == 'Listado_HorasExtra_Empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Obrasocial.php' || basename($_SERVER['PHP_SELF']) == 'Listado_Obrasocial_Empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Embargo.php' || basename($_SERVER['PHP_SELF']) == 'Listado_Embargo_empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Sanciones.php' || basename($_SERVER['PHP_SELF']) == 'Listar_sanciones_empleado.php' || basename($_SERVER['PHP_SELF']) == 'Registrar_Licencia.php' || basename($_SERVER['PHP_SELF']) == 'Listar_Licencias.php') ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
        <li>
          <a href="Registrar_viatico.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Registrar_viatico.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Viáticos</span>
          </a>
        </li>
        <li>
          <a href="Listado_Viatico_Empleado.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listado_Viatico_Empleado.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Consultar Viaticos</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Anticipo.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Registrar_Anticipo.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Anticipos</span>
          </a>
        </li>
        <li>
          <a href="Listado_Anticipo_Empleado.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listado_Anticipo_Empleado.php' ? 'active' : ''; ?>">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Anticipo</span>
          </a>
        </li>
        <li>
          <a href="RegistrarHoraExtra.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'RegistrarHoraExtra.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Horas Extras</span>
          </a>
        </li>
        <li>
          <a href="Listado_HorasExtra_Empleado.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listado_HorasExtra_Empleado.php' ? 'active' : ''; ?>">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Horas Extras</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Obrasocial.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Registrar_Obrasocial.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Obra Social</span>
          </a>
        </li>
        <li>
          <a href="Listado_Obrasocial_Empleado.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listado_Obrasocial_Empleado.php' ? 'active' : ''; ?>">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Prepaga</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Embargo.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Registrar_Embargo.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Embargos Judiciales</span>
          </a>
        </li>
        <li>
          <a href="Listado_Embargo_empleado.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listado_Embargo_empleado.php' ? 'active' : ''; ?>">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Embargo Judicial</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Sanciones.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Registrar_Sanciones.php' ? 'active' : ''; ?>">
            <i class="bi bi-layout-text-window-reverse"></i><span>Registrar Sanciones</span>
          </a>
        </li>
        <li>
          <a href="Listar_sanciones_empleado.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listar_sanciones_empleado.php' ? 'active' : ''; ?>">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Sanciones</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Licencia.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Registrar_Licencia.php' ? 'active' : ''; ?>">
            <i class="bi bi-layout-text-window-reverse"></i><span>Registrar Licencias</span>
          </a>
        </li>
        <li>
          <a href="Listar_Licencias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Listar_Licencias.php' ? 'active' : ''; ?>">
            <i class="bi bi-layout-text-window-reverse"></i><span>Listar Licencias</span>
          </a>
        </li>
      </ul>
    </li><!-- End Gestor Movimientos -->

    <!-- Gestor de Reportes -->
    <li class="nav-item">
      <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'Informe_Ultimos_e.php' ? 'active' : ''; ?>" data-bs-target="#forms-nav-reportes" data-bs-toggle="collapse" href="#">
        <i class="bi bi-file-earmark"></i><span>Gestor de Reportes</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="forms-nav-reportes" class="nav-content collapse <?php echo basename($_SERVER['PHP_SELF']) == 'Informe_Ultimos_e.php' ? 'show' : ''; ?>" data-bs-parent="#sidebar-nav">
        <li>
          <a href="Informe_Ultimos_e.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Informe_Ultimos_e.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Últimos empleados registrados</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Reporte.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Informe_Ultimos_e.php' ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Reporte</span>
          </a>
        </li>
      </ul>
    </li><!-- End Gestor de Reportes -->
  </ul>
</aside>
