<?php
ob_start();
include realpath(__DIR__ . '/../') . '/_init.php';

// Redirect, If user is not logged in
if (!is_loggedin()) {
  redirect(root_url() . '/index.php');
}

// Set Document Title
$document->setTitle("Ration Scanner");
$document->setBodyClass('');
$document->addScript('../assets/app/js/html5-qrcode.min.js');
$document->addScript('../assets/app/js/Controller/ScannedController.js?v=2');
// Include Header
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';
?>

<style>
  /* SweetAlert compact */
  .swal2-modal { padding: 1px !important; }
  .swal2-html-container { padding: 0 !important; overflow-x: hidden !important; }
  .swal2-popup { overflow-x: hidden !important; }
  .swal2-title { font-size: 1.5em; }
  /* Neutralize Bootstrap row negative margins inside Swal */
  .swal2-html-container .row { margin-left: 0 !important; margin-right: 0 !important; }

  /* Scanner viewer */
  #reader {
    width: 100%;
    max-width: 400px;
    margin: auto;
  }
  #reader video,
  #reader canvas {
    width: 100% !important;
    height: auto !important;
    object-fit: cover;
    border-radius: 8px;
  }

  /* Layout */
  .scan-wrapper {
    max-width: 480px;
    margin: 0 auto;
    padding: 0 12px 24px;
  }
  .scan-status {
    font-size: 0.9rem;
    color: #6c757d;
  }
  .scan-hint {
    font-size: 0.82rem;
    color: #6c757d;
    margin-top: 5px;
  }

  /* Action buttons: 2 per row */
  .scan-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    margin-top: 12px;
  }
  .scan-actions .btn {
    flex: 1 1 46%;
    min-width: 140px;
  }

  /* Manual input */
  .scan-input-row {
    display: flex;
    gap: 8px;
    margin-top: 20px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
  }
  .scan-input-row input {
    flex: 1;
  }

  @media (max-width: 576px) {
    h1.m-0 { font-size: 1.2rem; }
    #reader { max-width: 100%; }
    .scan-actions .btn { flex: 1 1 100%; }
  }
</style>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">Barcode &amp; QR Scanner</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <button class="btn btn-sm btn-danger" onclick="history.back()">
                            <i class="fa fa-times-circle"></i> Back
                        </button>
                    </div>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">
        <div class="scan-wrapper">

            <!-- Status -->
            <p class="scan-status mb-2 text-center" id="scanStatus">Tap <strong>Start Scanning</strong> to begin</p>

            <!-- Camera Viewer -->
            <div id="reader"></div>
            <p class="scan-hint text-center">Tap the viewfinder to refocus</p>

            <!-- Camera Control Buttons -->
            <div class="scan-actions">
                <button id="startBtn"  class="btn btn-success" onclick="startScanner()">
                    <i class="fa fa-camera"></i> Start Scanning
                </button>
                <button id="stopBtn"   class="btn btn-danger d-none" onclick="stopScanner()">
                    <i class="fa fa-stop"></i> Stop Scanning
                </button>
                <button id="flashBtn"  class="btn btn-warning d-none" onclick="toggleFlash()">
                    <i class="fa fa-bolt"></i> Switch On Torch
                </button>
                <label  id="uploadBtn" for="uploadInput" class="btn btn-primary d-none">
                    <i class="fa fa-image"></i> Scan Image
                </label>
            </div>

            <!-- Hidden file input for image scan -->
            <input type="file" id="uploadInput" accept="image/*" class="d-none">

            <!-- Manual entry -->
            <div class="scan-input-row">
                <input type="text" id="empCodeInput" placeholder="Enter Employee Code"
                    class="form-control" pattern="[0-9]*" inputmode="numeric"
                    oninput="digitsOnly(this, 6)">
                <button class="btn btn-secondary" onclick="submitManualCode()">
                    <i class="fa fa-paper-plane"></i> Submit
                </button>
            </div>

        </div>
    </section>
    <!-- Content End -->

</div>
<!-- Content Wrapper End -->

<?php
// Include Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php';
?>
