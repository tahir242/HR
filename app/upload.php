<?php
ob_start();
include("../_init.php");

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_upload')) {
    redirect(root_url() . '/' . APPDIRNAME . '/dashboard.php');
}

// Add Script and Style
$document->addScript('../assets/dropzone/dropzone-min.js?v=1');
$document->addStyle('../assets/dropzone/dropzone.css?v=1');
$document->addScript('../assets/app/js/Controller/UploadController.js?v=1');

// Set Document Title
$document->setTitle("Upload");
// ADD BODY CLASS
// $document->setBodyClass('sidebar-collapse');

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';

?>

<style>
    .content-wrapper {
        background-color: #f4f7fc;
    }

    .dropzone {
        border: 3px dashed #3bafda;
        background: #ffffff;
        padding: 30px;
        text-align: center;
        border-radius: 15px;
        transition: 0.3s;
    }

    .dropzone:hover {
        background: #eaf2ff;
    }

    .progress {
        height: 10px;
    }

    .btn-group .btn {
        flex-grow: 1;
    }

    .preview img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }

    .file-status {
        display: flex;
        align-items: center;
    }

    .file-status strong {
        margin-left: 10px;
    }
</style>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <!-- <h1 class="m-0">Uploading PDF</h1> -->
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">
        <div class="container mt-3">
            <h2 class="text-center text-info mb-2">Bulk PDF Upload</h2>
            <form action="upload.php" class="dropzone fileinput-button" id="pdf-dropzone"></form>
            <div class="table table-striped files mt-4" id="previews">
                <div id="template" class="row mt-2 p-2 border rounded shadow-sm bg-white align-items-center">
                    <div class="col-auto">
                        <span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span>
                    </div>
                    <div class="col file-status">
                        <p class="mb-0 font-weight-bold">
                            <span data-dz-name></span> (<span data-dz-size></span>)
                        </p>
                    </div>
                    <div class="col">
                        <strong class="error text-danger" data-dz-errormessage></strong>
                        <strong class="success text-success" data-dz-successmessage></strong>
                    </div>
                    <div class="col-3">
                        <div class="progress progress-striped active w-100" role="progressbar" aria-valuemin="0"
                            aria-valuemax="100" aria-valuenow="0">
                            <div class="progress-bar bg-success" style="width:0%;" data-dz-uploadprogress></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button class="btn btn-primary start">
                                <i class="fas fa-upload"></i>
                            </button>
                            <button data-dz-remove class="btn btn-danger delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="actions" class="row mt-4">
                <div class="col-lg-6">
                    <div class="btn-group w-100">
                        <span class="btn btn-success col fileinput-button">
                            <i class="fas fa-plus"></i> Add files
                        </span>
                        <button type="submit" class="btn btn-primary col start">
                            <i class="fas fa-upload"></i> Start upload
                        </button>
                        <button type="reset" class="btn btn-warning col cancel">
                            <i class="fas fa-times-circle"></i> Cancel upload
                        </button>
                    </div>
                </div>
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="fileupload-process w-100">
                        <div id="total-progress" class="progress progress-striped active" role="progressbar"
                            aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                            <div class="progress-bar bg-success" style="width:0%;" data-dz-uploadprogress></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content End -->

</div>
<!-- Content Wrapper End -->
<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
