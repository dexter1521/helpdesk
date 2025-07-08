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
            <span class="text-uppercase page-subtitle">Asignación Automática</span>
            <h3 class="page-title">Staff por Departamento</h3>
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

<div class="card">
    <div class="card-header">
        <h5 class="m-0">Configurar Agentes por Departamento</h5>
        <small class="text-muted">Configure qué agentes pueden recibir tickets automáticamente de cada departamento</small>
    </div>
    <div class="card-body">
        <?php echo form_open('', [], ['do' => 'save_assignments']); ?>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th width="200">Agente</th>
                        <?php foreach($departments as $dept): ?>
                        <th class="text-center" width="150">
                            <?php echo esc($dept->name); ?>
                            <br>
                            <small class="text-muted">Activo / Peso</small>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($staff_list as $staff): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc($staff->fullname); ?></strong>
                            <br>
                            <small class="text-muted">@<?php echo esc($staff->username); ?></small>
                            <?php if($staff->admin == 1): ?>
                                <span class="badge badge-warning">Admin</span>
                            <?php endif; ?>
                        </td>
                        <?php foreach($departments as $dept): ?>
                        <td class="text-center">
                            <?php 
                            $is_active = isset($assignments[$staff->id][$dept->id]['active']) && $assignments[$staff->id][$dept->id]['active'];
                            $weight = $assignments[$staff->id][$dept->id]['weight'] ?? 1;
                            ?>
                            
                            <div class="form-check mb-2">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       name="staff_departments[<?php echo $staff->id; ?>][<?php echo $dept->id; ?>][active]" 
                                       value="1"
                                       id="staff_<?php echo $staff->id; ?>_dept_<?php echo $dept->id; ?>"
                                       <?php echo $is_active ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="staff_<?php echo $staff->id; ?>_dept_<?php echo $dept->id; ?>">
                                    Activo
                                </label>
                            </div>
                            
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Peso:</span>
                                </div>
                                <input type="number" 
                                       class="form-control" 
                                       name="staff_departments[<?php echo $staff->id; ?>][<?php echo $dept->id; ?>][weight]" 
                                       value="<?php echo $weight; ?>"
                                       min="1" 
                                       max="10"
                                       style="width: 60px;">
                            </div>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-md-8">
                <div class="alert alert-info">
                    <strong>Información sobre Pesos:</strong>
                    <ul class="mb-0 mt-2">
                        <li>El peso determina la probabilidad de asignación en el método "Ponderado"</li>
                        <li>Peso 1 = probabilidad normal, Peso 5 = 5 veces más probabilidad</li>
                        <li>Los administradores pueden recibir tickets de cualquier departamento automáticamente</li>
                        <li>Si no hay agentes específicos para un departamento, se asignará a administradores disponibles</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar Configuración
                    </button>
                    <a href="<?php echo site_url('staff/auto-assignment'); ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
        
        <?php echo form_close(); ?>
    </div>
</div>

<?php
$this->endSection();
$this->section('script_block');
?>
<script>
$(document).ready(function() {
    // Funcionalidad para seleccionar/deseleccionar todos los checkboxes por columna
    $('.table thead th').each(function(index) {
        if (index > 0) { // Saltar la primera columna (nombres)
            $(this).click(function() {
                var column = $(this).index();
                var checkboxes = $('.table tbody tr').find('td:eq(' + column + ') input[type="checkbox"]');
                var allChecked = checkboxes.length === checkboxes.filter(':checked').length;
                
                checkboxes.prop('checked', !allChecked);
            });
            
            $(this).css('cursor', 'pointer').attr('title', 'Click para seleccionar/deseleccionar todos');
        }
    });
});
</script>
<?php
$this->endSection();
?>
