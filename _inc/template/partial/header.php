<?php
$body_class = $document->getBodyClass();
$title = $document->getTitle();
$description = $document->getDescription();
$keywords = $document->getKeywords();
$styles = $document->getStyles();
$scripts = $document->getScripts();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="google" content="notranslate">
  <meta name="robots" content="noindex" />
  <meta http-equiv='cache-control' content='no-cache'>
  <meta http-equiv='expires' content='0'>
  <meta http-equiv='pragma' content='no-cache'>

  <!-- Favicon -->
  <link rel="shortcut icon" type="image/x-icon" href="../assets/app/img/icon.jpg">
  <title>
    <?php echo $title ? $title . ' &raquo; ' : null; ?>
    <?php echo APPNAME; ?>
  </title>

  <!-- Font Awesome Icons -->
  <link href="../assets/fontawesome/css/all.min.css" rel="stylesheet" type="text/css">
  <!-- Jquery UI -->
  <link href="../assets/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css">
  <!-- SweetAlert UI -->
  <link href="../assets/sweetalert/bootstrap4_sweetalert.css" rel="stylesheet" type="text/css">
  <!-- Datatables -->
  <link href="../assets/datatables/datatables.min.css" rel="stylesheet" type="text/css">
  <link href="../assets/select2/css/select2.min.css" rel="stylesheet" type="text/css">
  <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap4.min.css" rel="stylesheet" type="text/css">
  <!-- Add Runtime CSS -->
  <?php foreach ($styles as $style): ?>
    <link type="text/css" href="<?php echo $style['href']; ?>" rel="<?php echo $style['rel']; ?>"
      media="<?php echo $style['media']; ?>">
  <?php endforeach; ?>

  <link rel="stylesheet" href="../assets/app/css/adminlte.css">
  <!-- Responsive CSS -->
  <link href="../assets/app/css/responsive.css" type="text/css" rel="stylesheet">
  <!-- <script disable-devtool-auto src='../assets/app/js/disable-devtools.js'></script> -->
  <!-- JavaScript Variables -->
  <script type="text/javascript">
    var baseUrl = "<?php echo root_url(); ?>";
    var adminDir = "<?php echo APPDIRNAME; ?>";
    var sso = "<?php echo SSOURL ?>";
    var deviceType = '<?php echo device_type(); ?>';
  </script>

</head>

<body
  class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed <?php echo $body_class; ?>">

  <div id="ajaxWait">
    <div class="divWaiting">
    </div>
    <div id="Layer1"
      style="left: 50%; vertical-align: middle; position: absolute; top: 40%; background-color: transparent; text-align: center; z-index: 20000 !important;">
      <table style="opacity: 1; border: none; width: 80px; height: 100px;">
        <tr style="width: 100%;">
          <td style="text-align: center; width: 100%;">
            <img id="Image1" src="<?php echo root_url(); ?>/assets/app/img/loading.gif" align="absmiddle"
              style="border-width:0px;width: 100%;" />
            <span id="Span1" style="font-family: Arial; font-size: 10pt; color: #011664">Please
              wait..</span>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <!-- Site wrapper -->
  <div class="wrapper">

    <?php include realpath(__DIR__ . '') . '/top.php'; ?>
