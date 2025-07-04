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
            <span class="text-uppercase page-subtitle">HelpDeskZ</span>
            <h3 class="page-title">Importar Usuarios</h3>
        </div>
    </div>
    <!-- End Page Header -->

<?php
if(isset($error_msg)){
    echo '<div class="alert alert-danger"><pre>'.$error_msg.'</pre></div>';
}
if(isset($success_msg)){
    echo '<div class="alert alert-success">'.$success_msg.'</div>';
}
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0"><i class="fa fa-upload"></i> Importar Usuarios desde Excel</h6>
            </div>
            <div class="card-body">
                <?php echo form_open_multipart('', ['id' => 'importForm'], ['do' => 'upload']); ?>
                
                <div class="form-group">
                    <label for="excel_file">Archivo Excel <span class="text-danger">*</span></label>
                    <input type="file" class="form-control-file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" required>
                    <small class="form-text text-muted">
                        Formatos permitidos: .xlsx, .xls, .csv (máximo 5MB)
                    </small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload"></i> Importar Usuarios
                    </button>
                    <a href="<?php echo site_url(route_to('staff_users')); ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Volver
                    </a>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0"><i class="fa fa-download"></i> Plantilla de Excel</h6>
            </div>
            <div class="card-body">
                <p>Descarga la plantilla de Excel para conocer el formato correcto:</p>
                <a href="<?php echo site_url('staff/users/download-template'); ?>" class="btn btn-success btn-block">
                    <i class="fa fa-download"></i> Descargar Plantilla
                </a>
                
                <hr>
                
                <h6>Instrucciones:</h6>
                <ul class="small">
                    <li><strong>Columna A:</strong> Nombre Completo (obligatorio)</li>
                    <li><strong>Columna B:</strong> Email (obligatorio, único)</li>
                    <li><strong>Columna C:</strong> Estado (1=Activo, 0=Inactivo)</li>
                    <li><strong>Columna D:</strong> Contraseña (opcional, se genera automáticamente si está vacía)</li>
                </ul>
                
                <div class="alert alert-info small mt-3">
                    <strong>Nota:</strong> Si no se especifica una contraseña, se generará automáticamente una contraseña de 8 caracteres.
                </div>
                
                <div class="alert alert-success small mt-2">
                    <i class="fa fa-lock"></i> <strong>Seguridad:</strong> Todas las contraseñas se cifran automáticamente con bcrypt antes de almacenarse en la base de datos.
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="m-0"><i class="fa fa-info-circle"></i> Información Importante</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning small">
                    <ul class="mb-0">
                        <li>Los emails duplicados serán omitidos</li>
                        <li>La primera fila debe contener los encabezados</li>
                        <li>Las filas vacías serán ignoradas</li>
                        <li>Se mostrará un resumen al finalizar la importación</li>
                        <li><strong>🔐 Las contraseñas se hashean automáticamente - elimina el archivo después de importar</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('importForm').addEventListener('submit', function(e) {
    var fileInput = document.getElementById('excel_file');
    if (!fileInput.files[0]) {
        e.preventDefault();
        alert('Por favor selecciona un archivo Excel');
        return false;
    }
    
    var allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                       'application/vnd.ms-excel', 
                       'text/csv'];
    
    if (allowedTypes.indexOf(fileInput.files[0].type) === -1) {
        e.preventDefault();
        alert('Tipo de archivo no válido. Usa archivos .xlsx, .xls o .csv');
        return false;
    }
    
    if (fileInput.files[0].size > 5 * 1024 * 1024) {
        e.preventDefault();
        alert('El archivo es muy grande. Máximo 5MB');
        return false;
    }
    
    // Mostrar mensaje de carga
    var submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Procesando...';
    submitBtn.disabled = true;
});
</script>

<?php $this->endSection(); ?>
