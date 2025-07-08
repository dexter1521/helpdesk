<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('staff/template');
$this->section('content');
?>
    <!-- Page Header -->
    <div class="page-header row no-gutters py-4">
        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
            <span class="text-uppercase page-subtitle">Sistema</span>
            <h3 class="page-title">Asignación Automática de Tickets</h3>
        </div>
    </div>
    <!-- End Page Header -->

<?php
if(isset($error_msg)){
    echo '<div class="alert alert-danger">'.$error_msg.'</div>';
}
if(isset($success_msg)){
    echo '<div class="alert alert-success">'.$success_msg.'</div>';
}
?>

<div class="row">
    <!-- Configuración General -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0">Configuración de Asignación Automática</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="do" value="update_settings">
                
                <div class="form-group">
                    <label>Estado de Asignación Automática</label>
                    <select name="auto_assignment" class="form-control custom-select" id="auto_assignment_toggle">
                        <option value="0" <?php echo !$auto_assignment_enabled ? 'selected' : ''; ?>>Deshabilitado</option>
                        <option value="1" <?php echo $auto_assignment_enabled ? 'selected' : ''; ?>>Habilitado</option>
                    </select>
                    <small class="form-text text-muted">
                        Cuando está habilitado, los tickets nuevos se asignarán automáticamente a los agentes disponibles del departamento.
                    </small>
                </div>

                <div class="form-group" id="assignment_method_group">
                    <label>Método de Asignación</label>
                    <select name="auto_assignment_method" class="form-control custom-select">
                        <option value="balanced" <?php echo $assignment_method == 'balanced' ? 'selected' : ''; ?>>
                            Balanceado (Round Robin)
                        </option>
                        <option value="random" <?php echo $assignment_method == 'random' ? 'selected' : ''; ?>>
                            Aleatorio
                        </option>
                        <option value="weighted" <?php echo $assignment_method == 'weighted' ? 'selected' : ''; ?>>
                            Ponderado por Prioridad
                        </option>
                    </select>
                    <small class="form-text text-muted">
                        <strong>Balanceado:</strong> Asigna tickets de manera equitativa entre agentes.<br>
                        <strong>Aleatorio:</strong> Asigna tickets de forma aleatoria.<br>
                        <strong>Ponderado:</strong> Asigna más tickets a agentes con mayor peso de prioridad.
                    </small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar Configuración
                    </button>
                    <a href="<?php echo site_url(route_to('staff_auto_assignment')); ?>" class="btn btn-secondary ml-2">
                        <i class="fa fa-refresh"></i> Recargar Estado
                    </a>
                    <button type="button" class="btn btn-info ml-2" onclick="checkDatabaseStatus()">
                        <i class="fa fa-database"></i> Verificar BD
                    </button>
                    <button type="button" class="btn btn-warning ml-2" onclick="runMigration()">
                        <i class="fa fa-wrench"></i> Ejecutar Migración
                    </button>
                </div>
                
                </form>
            </div>
        </div>
    </div>

    <!-- Panel de Estado -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0">Estado Actual</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Asignación Automática:</strong>
                    <?php if($auto_assignment_enabled): ?>
                        <span class="badge badge-success">Habilitado</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Deshabilitado</span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <strong>Método Actual:</strong>
                    <span class="badge badge-info">
                        <?php
                        switch($assignment_method) {
                            case 'balanced': echo 'Balanceado'; break;
                            case 'random': echo 'Aleatorio'; break;
                            case 'weighted': echo 'Ponderado'; break;
                            default: echo ucfirst($assignment_method); break;
                        }
                        ?>
                    </span>
                </div>

                <hr>
                
                <div class="text-center">
                    <a href="<?php echo site_url('staff/auto-assignment/staff-departments'); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-users"></i> Gestionar Staff por Departamento
                    </a>
                </div>
            </div>
        </div>

        <!-- Panel de Ayuda -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="m-0">Información</h5>
            </div>
            <div class="card-body">
                <p class="small">
                    <strong>Nota:</strong> La asignación automática no reemplaza la asignación manual. 
                    Los tickets siempre pueden ser reasignados manualmente después de ser creados.
                </p>
                <p class="small">
                    Los administradores y agentes con acceso a todos los departamentos pueden recibir 
                    tickets de cualquier departamento cuando no hay agentes específicos disponibles.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas por Departamento -->
<?php if($departments && $auto_assignment_enabled): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0">Estadísticas por Departamento</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Departamento</th>
                                <th>Agentes Disponibles</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($departments as $dept): ?>
                            <tr>
                                <td><?php echo esc($dept->name); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <!-- Este número se calculará dinámicamente -->
                                        <i class="fa fa-users"></i> Ver agentes
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo site_url('staff/auto-assignment/department-stats/' . $dept->id); ?>" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fa fa-chart-bar"></i> Ver Estadísticas
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal para información de debug -->
<div class="modal fade" id="debugModal" tabindex="-1" role="dialog" aria-labelledby="debugModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="debugModalLabel">Información de Base de Datos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- El contenido se carga dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$this->endSection();
$this->section('script_block');
?>
<script>
$(document).ready(function() {
    function toggleAssignmentMethod() {
        if ($('#auto_assignment_toggle').val() == '1') {
            $('#assignment_method_group').show();
        } else {
            $('#assignment_method_group').hide();
        }
    }
    
    // Ejecutar al cargar la página
    toggleAssignmentMethod();
    
    // Ejecutar cuando cambie el valor
    $('#auto_assignment_toggle').on('change', toggleAssignmentMethod);
    
    // Función para verificar estado de la base de datos
    window.checkDatabaseStatus = function() {
        $.ajax({
            url: '<?php echo site_url(route_to('staff_auto_assignment_debug')); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let message = '<h5>Estado de la Base de Datos:</h5>';
                
                if (data.error) {
                    message += '<div class="alert alert-danger">Error: ' + data.error + '</div>';
                } else {
                    // Columnas en config
                    message += '<h6>Columnas en hdzfv_config:</h6>';
                    if (data.config_columns && data.config_columns.length > 0) {
                        message += '<ul>';
                        data.config_columns.forEach(function(col) {
                            message += '<li>' + col.Field + ' (' + col.Type + ')</li>';
                        });
                        message += '</ul>';
                    } else {
                        message += '<p class="text-danger">¡Columnas auto_assignment no encontradas!</p>';
                    }
                    
                    // Valores actuales
                    message += '<h6>Valores actuales:</h6>';
                    if (data.config_values) {
                        message += '<ul>';
                        message += '<li>auto_assignment: ' + (data.config_values.auto_assignment || 'null') + '</li>';
                        message += '<li>auto_assignment_method: ' + (data.config_values.auto_assignment_method || 'null') + '</li>';
                        message += '</ul>';
                    } else {
                        message += '<p class="text-warning">No se pudieron obtener los valores</p>';
                    }
                    
                    // Estado de tablas
                    message += '<h6>Estado de tablas:</h6>';
                    message += '<ul>';
                    message += '<li>hdzfv_staff_departments: ' + (data.tables.hdzfv_staff_departments ? '✓ Existe' : '✗ No existe') + '</li>';
                    message += '<li>hdzfv_department_assignments: ' + (data.tables.hdzfv_department_assignments ? '✓ Existe' : '✗ No existe') + '</li>';
                    message += '<li>tickets.staff_id: ' + (data.tickets_staff_id ? '✓ Existe' : '✗ No existe') + '</li>';
                    message += '</ul>';
                }
                
                // Mostrar en modal
                $('#debugModal .modal-body').html(message);
                $('#debugModal').modal('show');
            },
            error: function() {
                alert('Error al obtener información de la base de datos');
            }
        });
    };
    
    // Función para ejecutar migración
    window.runMigration = function() {
        if (confirm('¿Está seguro de que desea ejecutar la migración de base de datos? Esto agregará las columnas y tablas necesarias para la asignación automática.')) {
            // Crear un formulario temporal para enviar la solicitud POST
            var form = $('<form>', {
                'method': 'POST',
                'action': '<?php echo site_url(route_to('staff_auto_assignment_migration')); ?>'
            });
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'do',
                'value': 'run_migration'
            }));
            $('body').append(form);
            form.submit();
        }
    };
});
</script>
<?php
$this->endSection();
?>
