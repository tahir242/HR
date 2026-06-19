<!-- Main Sidebar Container -->
<aside class="main-sidebar main-sidebar-custom sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="javascript:void(0);" class="brand-link">
    <img src="<?php echo root_url(); ?>/assets/app/img/icon.jpg" alt="SIMS-SIUT Logo" class="brand-image elevation-3"
      style="opacity: .8">
    <span class="brand-text font-weight-bold">
      <?php echo parameter("app_code"); ?>
    </span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
        <li class="nav-header">MAIN NAVIGATION</li>
        <?php
        $data = array(
          "sort" => "Sort"
        );
        $modules = get_modules($data);
        foreach ($modules as $module): ?>
          <?php if ($module->Has_Sub_Menu == 1): ?>
            <?php
            $data = array(
              "filter_module" => $module->Module_ID,
              "filter_menu" => 1,
              "sort" => "SubModule"
            );
            $submodules = get_submodules($data);
            foreach ($submodules as $submodule): ?>
              <?php
              $data = array(
                "filter_submodule" => $submodule->Sub_Module_ID,
                "sort" => 'Permission'
              );
              $permissions = get_permissions($data) ?>
              <?php foreach ($permissions as $permission): ?>
                <?php if (user_group_id() == 1 || has_permission(1, $permission->Permission_ID)): ?>


                  <li class="nav-item <?php
                  $data = array(
                    "filter_module" => $module->Module_ID,
                    "sort" => "SubModule"
                  );
                  $submodules = get_submodules($data);
                  foreach ($submodules as $submodule) {
                    echo current_nav() == basename($submodule->Url, ".php") ? 'menu-open' : null;
                  }
                  ?>">
                    <a href="<?php echo $module->Url !== NULL ? $module->Url : "javascript:void(0);" ?>" class="nav-link 
                            <?php //foreach ($submodules as $submodule) {
                               //echo current_nav() == basename($submodule->Url, ".php") ? 'active' : null;
                             //} ?>">
                      <i class="nav-icon <?php echo $module->Icon ?>"></i>
                      <p>
                        <?php echo $module->Module ?>
                        <i class="fas fa-angle-left right"></i>
                      </p>
                    </a>
                    <ul class="nav nav-treeview">
                      <?php
                      $data = array(
                        "filter_module" => $module->Module_ID,
                        "filter_menu" => 1,
                        "sort" => "SubModule"
                      );
                      $submodules = get_submodules($data);
                      foreach ($submodules as $submodule): ?>
                        <?php
                        $data = array(
                          "filter_submodule" => $submodule->Sub_Module_ID,
                          "sort" => 'Permission'
                        );
                        $permissions = get_permissions($data) ?>
                        <?php foreach ($permissions as $permission): ?>
                          <?php if (user_group_id() == 1 || has_permission(1, $permission->Permission_ID)): ?>
                            <li class="nav-item">
                              <a href="<?php echo $submodule->Url ?>"
                                class="nav-link <?php //echo current_nav() == basename($submodule->Url, ".php") ? 'active' : null; ?>">
                                <i class="far fa-arrow-right nav-icon"></i>
                                <p>
                                  <?php echo $submodule->Sub_Module ?>
                                </p>
                              </a>
                            </li>
                            <?php break; ?>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      <?php endforeach; ?>
                    </ul>
                  </li>
                  <?php break 2; ?>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endforeach; ?>
          <?php else: ?>
            <?php
            $data = array(
              "filter_submodule" => $module->Module_ID,
              "sort" => 'Permission'
            );
            $permissions = get_permissions($data) ?>
            <?php foreach ($permissions as $permission): ?>
              <?php if (user_group_id() == 1 || has_permission(1, $permission->Permission_ID)): ?>

                <li class="nav-item">
                  <a href="<?php echo $module->Url !== NULL ? $module->Url : "javascript:void(0);" ?>"
                    class="nav-link <?php //echo current_nav() == basename($module->Url, ".php") ? 'active' : null; ?>">
                    <i class="nav-icon <?php echo $module->Icon ?>"></i>
                    <p>
                      <?php echo $module->Module ?>
                    </p>
                  </a>
                </li>

                <?php break; ?>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        <?php endforeach; ?>
        <?php if (isset($response->hosts) && $response->hosts > 1): ?>
          <li class="nav-item">
            <a href="<?php echo SSOURL ? SSOURL . "/select_host.php" : "javascript:void(0);" ?>" class="nav-link">
              <i class="nav-icon fa fa-sync-alt"></i>
              <p class="text">Switch Application</p>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
  <div class="sidebar-custom">
    <a href="#" class="btn btn-link support"><i class="fas fa-headset"></i></a>
    <a href="#" class="btn btn-secondary hide-on-collapse pos-right logout">Logout</a>
  </div>
  <!-- /.sidebar-custom -->
</aside>