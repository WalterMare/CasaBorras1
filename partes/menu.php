<?php ?>
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <li class="nav-item">
      <a class="nav-link collapsed" href="index.php">
        <i class="bi bi-grid"></i>
        <span>Panel</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <li class="nav-item">
      <a class="nav-link " data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-truck"></i><span>Gestor de personal</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>

      <ul id="forms-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
        <li>
          <a href="Empleado_carga.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Cargar nuevo Empleado</span>
          </a>
        </li>

        <li>
          <a href="Familiar_carga.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Cargar Familiar</span>
          </a>
        </li>

        <li>
          <a href="Usuario.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Usuario</span>
          </a>
        </li>

        <li>
          <a href="Listado_empleados.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Listado de Empleados</span>
          </a>
        </li>
      </ul>
    </li>


    <li class="nav-item">
      <a class="nav-link " data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-globe2"></i><span>Gestor Movimientos</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="forms-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">

        <li>
          <a href="Registrar_viatico.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Viáticos</span>
          </a>
        </li>
        <li>
          <a href="Listado_Viatico_Empleado.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Consultar Viaticos</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Anticipo.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Anticipos</span>
          </a>
        </li>

        <li>
          <a href="Listado_Anticipo_Empleado.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Anticipo</span>
          </a>
        </li>

        <li>
          <a href="RegistrarHoraExtra.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Horas Extras</span>
          </a>
        </li>

        <li>
          <a href="Listado_HorasExtra_Empleado.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Horas Extras</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Obrasocial.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Registrar Obra Social</span>
          </a>
        </li>
        <li>
          <a href="Listado_Obrasocial_Empleado.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Prepaga</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Embargo.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Registrar Embargos Judiciales</span>
          </a>
        </li>
        <li>
          <a href="Listado_Embargo_empleado.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Embargo Judicial</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Sanciones.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Registrar Sanciones</span>
          </a>
        </li>
        <li>
          <a href="Listar_sanciones_empleado.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Consultar Sanciones</span>
          </a>
        </li>
        <li>
          <a href="Registrar_Licencia.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Registrar Licencias</span>
          </a>
        </li>
        <li>
          <a href="Listar_Licencias.php" class="active">
            <i class="bi bi-layout-text-window-reverse"></i><span>Listar Licencias</span>
          </a>
        </li>

      </ul>
    </li>
    <li class="nav-item">
      <a class="nav-link " data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-globe2"></i><span>Gestor de Reportes</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="forms-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">

        <li>
          <a href="Informe_Ultimos_e.php" class="active">
            <i class="bi bi-file-earmark-plus"></i><span>Últimos empleados registrados</span>
          </a>
        </li>
        

      </ul>
    </li>
  </ul>
</aside>