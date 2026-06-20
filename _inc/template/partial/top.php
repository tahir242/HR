<!-- Navbar -->
<nav class="main-header navbar navbar-expand" style="background-color: #3fb7cc;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <?php if(current_nav() != 'dashboard') : ?>
      <li class="nav-item">
        <a href="JavaScript:void(0);" onclick="history.back();" style="margin-top: 8px; margin-right: 8px;" class="btn btn-danger btn-xs"><i class="fa-solid fa-backward"></i> Back</a>
      </li>
      <li class="nav-item">
        <a href="JavaScript:void(0);" onclick="location.reload(true);" style="margin-top: 8px;" class="btn btn-primary btn-xs"><i class="fa-solid fa-arrows-rotate"></i> Refresh</a>
      </li>
      <?php endif; ?>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="javascript:void(0);" class="nav-link"><?php echo parameter("environment"); ?></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="javascript:void(0);" class="nav-link" id="livedatetime"></a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
    
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
          <img src="<?php echo user("Photo") ? SSOURL . "/storage/users/" . user("Photo") : "../assets/app/img/avatar6.png" ?>" class="user-image img-circle" alt="User Image">
          <span class="d-none d-md-inline"><?php echo ucfirst(limit_char(user("Fullname"), 15)); ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
          <span class="dropdown-item dropdown-header"><b>Welcome</b><br><?php echo user_role(); ?></span>
          <div class="dropdown-divider"></div>
            <a href="javascript:void(0);" style="color: black;" data-userid="<?php echo user_id() ?>" class="dropdown-item dropdown-footer user-profile"><b>My Profile</b></a>
            <a href="javascript:void(0);" style="color: black;" class="dropdown-item dropdown-footer logout"><b>Log Out</b></a>
        </div>
      </li>

      <!-- <li class="nav-item d-none d-sm-inline-block">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li> -->

      <!-- <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li> -->

    </ul>
  </nav>
  <!-- /.navbar -->
