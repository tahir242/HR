<?php
ob_start();
include realpath(__DIR__ . '/../') . '/_init.php';

// Redirect, If user is not logged in
if (!is_loggedin()) {
    redirect(root_url() . '/index.php');
}

// Redirect, If User has not Read Permission
if (user_role_id() != 1 && !has_permission(1, 'read_dashboard')) {
    exit("You Have Not Permission to view this page");
}

// Set Document Title
$document->setTitle("Ex-Employee Record Dashboard");
$document->setBodyClass('');
$document->addScript('../assets/chartjs/chart.js?v=1');
$document->addScript('../assets/chartjs/chartjs-plugin-datalabels.js');
$document->addScript('../assets/app/js/Controller/DashboardController.js?v=1');

// Include Header and Footer
include realpath(__DIR__ . '/../') . '/_inc/template/partial/header.php';
include realpath(__DIR__ . '/../') . '/_inc/template/partial/sidebar.php';
?>

<!-- Content Wrapper Start -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $title ?></h1>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Content Start -->
    <section class="content">

        <?php
        $query = "
            SELECT 
                COUNT(1) AS Total, 
                COUNT(CASE WHEN Employee_ID IS NOT NULL THEN 1 END) AS IndexedTotal,
                COUNT(CASE WHEN Employee_ID IS NULL THEN 1 END) AS NonIndexedTotal,
                COUNT(CASE WHEN Employee_ID LIKE '%MEID%' THEN 1 END) AS MissingID
            FROM [HR].[dbo].[Employee_PDF]
            WHERE Active = ?";
        $row1 = $db->get_row($query, ["Y"]);
        ?>

        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-file-pdf"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Ex-Employee Record</span>
                        <span class="info-box-number">
                            <?php echo number_format($row1->Total, 0, ",", ","); ?>
                        </span>
                    </div>

                </div>

            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-address-book"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Indexed</span>
                        <span class="info-box-number">
                            <script>var totalIndexed = <?php echo $row1->IndexedTotal ?>;</script>
                            <?php
                            echo number_format($row1->IndexedTotal, 0, ",", ",");
                            ?>
                        </span>
                    </div>

                </div>

            </div>


            <div class="clearfix hidden-md-up"></div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-border-none"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Non Index</span>
                        <span class="info-box-number">
                            <script>var nonIndexed = <?php echo $row1->NonIndexedTotal ?>;</script>
                            <?php echo number_format($row1->NonIndexedTotal, 0, ",", ","); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-circle-question"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Missing Employee IDs</span>
                        <span class="info-box-number">
                            <?php echo number_format($row1->MissingID, 0, ",", ","); ?>
                        </span>
                    </div>

                </div>

            </div>

        </div>

        <?php
        $query = "
    SELECT 
        COUNT(CASE WHEN Name IS NULL THEN 1 END) AS NonName,
        COUNT(CASE WHEN Designation IS NULL THEN 1 END) AS NonDesignation,
        COUNT(CASE WHEN Department IS NULL THEN 1 END) AS NonDepartment,
        COUNT(CASE WHEN Date_of_Joining IS NULL THEN 1 END) AS NonDoj
    FROM [HR].[dbo].[Employee_PDF]
    WHERE Active = ?";
        $row2 = $db->get_row($query, ["Y"]);
        ?>

        <div class="row">
            <!-- Missing Names -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fa-solid fa-signature"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Missing Names</span>
                        <span class="info-box-number">
                            <?php echo number_format($row2->NonName, 0, ",", ","); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Missing Designations -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fa-solid fa-briefcase"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Missing Designations</span>
                        <span class="info-box-number">
                            <?php echo number_format($row2->NonDesignation, 0, ",", ","); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Missing Departments -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-info elevation-1"><i class="fa-solid fa-building"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Missing Departments</span>
                        <span class="info-box-number">
                            <?php echo number_format($row2->NonDepartment, 0, ",", ","); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Missing Dates of Joining -->
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-primary elevation-1"><i class="fa-solid fa-calendar-days"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Missing Dates of Joining</span>
                        <span class="info-box-number">
                            <?php echo number_format($row2->NonDoj, 0, ",", ","); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <script>
            var missingData = {
                "Missing Employee IDs": <?php echo $row1->MissingID; ?>,
                "Missing Names": <?php echo $row2->NonName; ?>,
                "Missing Designations": <?php echo $row2->NonDesignation; ?>,
                "Missing Departments": <?php echo $row2->NonDepartment; ?>,
                "Missing Dates of Joining": <?php echo $row2->NonDoj; ?>
            };
        </script>

        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body p-1">
                        <canvas id="indexChart" width="400" height="340"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body p-1">
                        <canvas id="missingDataChart" width="400" height="340"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body p-1">
                        <?php
                        // $files = readDirectoryFiles(parameter("file_path"));
                        // if (is_array($files)) {
                        //     foreach ($files as $file) {
                        //         $stmt = "SELECT Scan FROM [Employee_PDF] WHERE Scan = ?";
                        //         $row = $db->get_row($stmt, [trim($file)]);
                        //         if (!$row) {
                        //             $db->insert("[Employee_PDF]", ["Scan", "Created_By"], [trim($file), user_id()]);
                        //             if (!$db->rows_effected) {
                        //                 echo $file . " is not inserted";
                        //                 exit();
                        //             }
                        
                        //         }
                        //     }
                        // } else {
                        //     echo $files . "<br>";
                        // }
                        
                        // $oldName = 'path/to/old-file-name.pdf';
                        // $newName = 'path/to/new-file-name.pdf';
                        
                        // if (rename($oldName, $newName)) {
                        //     echo "File renamed successfully!";
                        // } else {
                        //     echo "Failed to rename file.";
                        // }
                        ?>
                        <canvas id="dailyReport" width="400" height="340"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body p-1">
                        <canvas id="hourlyReport"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content End -->

</div>
<!-- Content Wrapper End -->

<?php
// Prepare data arrays
$dailydata = [];
$hourlydata = [];

$stmt = "SELECT 
    DateTable.ReportDate AS [Date], 
    DateTable.ReportHour AS [Hour], 
    ISNULL(Indexing.IndexCount, 0) AS index_Record,
    ISNULL(Uploading.UploadCount, 0) AS upload_Record
FROM 
    (SELECT DISTINCT 
        CAST(Created_DtTm AS DATE) AS ReportDate, 
        DATEPART(HOUR, Created_DtTm) AS ReportHour 
     FROM [HR].[dbo].[Employee_PDF]
     WHERE Created_DtTm >= DATEADD(DAY, -3, CAST(GETDATE() AS DATE)) AND Active = 'Y'
     UNION 
     SELECT DISTINCT 
        CAST(Modified_DtTm AS DATE), 
        DATEPART(HOUR, Modified_DtTm) 
     FROM [HR].[dbo].[Employee_PDF]
     WHERE Modified_DtTm >= DATEADD(DAY, -3, CAST(GETDATE() AS DATE))) AS DateTable
LEFT JOIN 
    (SELECT 
        CAST(Modified_DtTm AS DATE) AS IndexDate, 
        DATEPART(HOUR, Modified_DtTm) AS IndexHour, 
        COUNT(*) AS IndexCount
     FROM [HR].[dbo].[Employee_PDF] 
     WHERE Modified_DtTm >= DATEADD(DAY, -3, CAST(GETDATE() AS DATE)) AND Active = 'Y'
     GROUP BY CAST(Modified_DtTm AS DATE), DATEPART(HOUR, Modified_DtTm)) AS Indexing
ON DateTable.ReportDate = Indexing.IndexDate AND DateTable.ReportHour = Indexing.IndexHour
LEFT JOIN 
    (SELECT 
        CAST(Created_DtTm AS DATE) AS UploadDate, 
        DATEPART(HOUR, Created_DtTm) AS UploadHour, 
        COUNT(*) AS UploadCount
     FROM [HR].[dbo].[Employee_PDF] 
     WHERE Created_DtTm >= DATEADD(DAY, -3, CAST(GETDATE() AS DATE)) AND Active = 'Y'
     GROUP BY CAST(Created_DtTm AS DATE), DATEPART(HOUR, Created_DtTm)) AS Uploading
ON DateTable.ReportDate = Uploading.UploadDate AND DateTable.ReportHour = Uploading.UploadHour
ORDER BY DateTable.ReportDate, DateTable.ReportHour;";

$results = $db->get_results($stmt, []);

foreach ($results as $row) {
    $hourlydata[] = [
        'date' => date_normalizer($row->Date, "d-m"),
        'hour' => $row->Hour,
        'index_Record' => $row->index_Record,
        'upload_Record' => $row->upload_Record
    ];
}

$stmt = "SELECT 
    DateTable.ReportDate AS [Date], 
    ISNULL(Indexing.IndexCount, 0) AS Indexs,
    ISNULL(Uploading.UploadCount, 0) AS Uploads
FROM 
    (SELECT DISTINCT CONVERT(Date, Created_DtTm) AS ReportDate FROM [HR].[dbo].[Employee_PDF] WHERE Active = 'Y'
     UNION 
     SELECT DISTINCT CONVERT(Date, Modified_DtTm) FROM [HR].[dbo].[Employee_PDF]) AS DateTable
LEFT JOIN 
    (SELECT CONVERT(Date, Modified_DtTm) AS IndexDate, COUNT(*) AS IndexCount
     FROM [HR].[dbo].[Employee_PDF] WHERE Active = 'Y'
     GROUP BY CONVERT(Date, Modified_DtTm)) AS Indexing
ON DateTable.ReportDate = Indexing.IndexDate
LEFT JOIN 
    (SELECT CONVERT(Date, Created_DtTm) AS UploadDate, COUNT(*) AS UploadCount
     FROM [HR].[dbo].[Employee_PDF] WHERE Active = 'Y'
     GROUP BY CONVERT(Date, Created_DtTm)) AS Uploading
ON DateTable.ReportDate = Uploading.UploadDate
ORDER BY DateTable.ReportDate;";

$results = $db->get_results($stmt, []);
$dailydata = [];

foreach ($results as $row) {
    $dailydata[] = [
        'date' => date_normalizer($row->Date, "d-m"),
        'File_Index' => $row->Indexs,
        'File_Upload' => $row->Uploads,
    ];
}
?>

<script>
    var hourlydata = <?php echo json_encode($hourlydata); ?>;
    var dailydata = <?php echo json_encode($dailydata); ?>;
</script>

<?php include realpath(__DIR__ . '/../') . '/_inc/template/partial/footer.php'; ?>
