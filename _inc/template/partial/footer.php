<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
        <h5>Title</h5>
        <p>Sidebar content</p>
    </div>
</aside>
<!-- /.control-sidebar -->

<footer class="main-footer">
    <div class="float-right d-none d-sm-inline">
        Copyright © <?php echo date('Y'); ?> EHR-SIUT, All rights reserved.
    </div>
    Design and developed by EHR-SIUT. <b>Version</b> <?php echo parameter("app_version"); ?>
    <div class="float-right d-none d-sm-inline-block">

    </div>
</footer>

</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="../assets/jquery/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- JQUERY UI -->
<script src="../assets/jquery-ui/jquery-ui.min.js"></script>
<!-- SlimScroll -->
<script src="../assets/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick JS -->
<script src="../assets/fastclick/fastclick.js" type="text/javascript"></script>
<!-- SweetAlert JS -->
<script src="../assets/sweetalert/sweetalert2.js" type="text/javascript"></script>
<script src="../assets/select2/js/select2.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<!-- Axios JS -->
<script src="../assets/app/js/axios.min.js"></script>
<!-- Datatables -->
<script src="../assets/datatables/datatables.min.js"></script>
<!-- Common JS -->
<script src="../assets/app/js/common.js"></script>
<!-- App -->
<script src="../assets/app/js/adminlte.js"></script>

<!-- Runtime JS -->
<?php foreach ($scripts as $script): ?>
    <script src="<?php echo $script; ?>" type="text/javascript"></script>
<?php endforeach; ?>

<noscript>
    <div class="global-site-notice noscript">
        <div class="notice-inner">
            <p><strong>JavaScript seems to be disabled in your browser.</strong><br>You must have JavaScript enabled.
            </p>
        </div>
    </div>
</noscript>
<?php insert_system_time_log(1, current_nav()); ?>
</body>
</html>