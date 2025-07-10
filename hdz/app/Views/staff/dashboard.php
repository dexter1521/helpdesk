<?php
/**
 * Vista Dashboard para Staff Admin
 */
$this->extend('staff/template');
$this->section('content');
?>
<div class="page-header row no-gutters py-4">
    <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
        <span class="text-uppercase page-subtitle">HelpDeskZ</span>
        <h3 class="page-title"><?php echo lang('Admin.dashboard.title'); ?></h3>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="card mb-4 shadow-sm border-primary">
            <div class="card-body text-center">
                <h5 class="text-primary"><i class="fas fa-ticket-alt"></i> <?php echo lang('Admin.dashboard.total'); ?></h5>
                <h2 class="display-4 text-primary"><?= $total_tickets ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="row">
            <?php foreach($status_counts as $status => $count): ?>
            <div class="col-md-3 col-6">
                <div class="card mb-4 border-info">
                    <div class="card-body text-center">
                        <h6 class="text-info"><i class="fas fa-circle"></i> <?php echo lang('Admin.tickets.status_' . $status); ?></h6>
                        <h3 class="text-info"><?= $count ?></h3>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success text-white"><strong><?php echo lang('Admin.dashboard.by_department'); ?></strong></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach($tickets_by_dept as $dept => $count): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-building"></i> <?= $dept ?></span>
                            <span class="badge badge-success badge-pill"><?= $count ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark"><strong><?php echo lang('Admin.dashboard.by_agent'); ?></strong></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach($tickets_by_agent as $agent => $count): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user"></i> <?= $agent ?></span>
                            <span class="badge badge-warning badge-pill"><?= $count ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>
