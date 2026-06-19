<?php if ($scan): ?>
    <div class="col-sm-12">
        <div class="card" style="height: 100vh;"> <!-- Ensure the card takes the full viewport height -->
            <div class="card-header">
                <h3 class="card-title">
                    <?php if (!isset($results)) : ?>
                    <button class="btn btn-danger btn-xs back-button">
                        <i class="fas fa-backward"></i> Back
                    </button> |
                    <?php endif; ?>
                    <?php $emp = $model->getPdfByID($scan); ?>
                    <b><?php echo $emp->Employee_ID . " - " . $emp->Name ?></b>
                </h3>
                <div class="card-tools pull-right">
                    <a href="javascript:void(0)" onclick="$('#pdf_screen').toggleClass('fullscreen');"
                        class="btn btn-sm btn-icon float-right" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </a>
                    <a href="JavaScript:void(0);" class="btn btn-primary btn-xs update-button" data-file="<?php echo $scan ?>"><i class="fa-solid fa-arrows-rotate"></i> Update Demographic</a>
                </div>
            </div>

            <?php
                insert_system_time_log(5, $scan);
                $file = parameter("file_path") . $scan;
                $proxyUrl = root_url() . "/app/proxy.php?file=" . urlencode($file); ?>
            <div class="card-body m-0 p-0" id="pdf_screen" style="height: calc(100vh - 56px);"> <!-- Set height to full, subtract header height -->
                <iframe id="pdf-js-viewer"
                    src="../_inc/vendor/pdfjs/web/pdf.html?file=<?php echo urlencode($proxyUrl); ?>&page=1"
                    title="webviewer" frameborder="0" name="myiframename" width="100%" height="100%" allowfullscreen
                    webkitallowfullscreen></iframe>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="d-flex justify-content-center align-items-center" style="height: 56vh;">
        <div style="border: 2px solid #ccc; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <h1 style="margin: 0; color: #555;">PDF NOT FOUND!</h1>
        </div>
    </div>
<?php endif; ?>
