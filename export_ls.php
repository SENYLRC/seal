<?php
ob_end_clean();
// export.php###

//start session for export feature
session_start();
$allsqlresults= $_SESSION['query2'];
//for testing
//print_r($_SESSION['query2']);

require '/var/www/seal_script/seal_function.php';
// Connect to database
require '/var/www/seal_script/seal_db.inc';
//check if the person is logged in
if (strlen($display_username)>1) {

    //for testing
    //echo "the query".$allsqlresults."<br>";
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);


    $GETLIST = mysqli_query($db, $allsqlresults);
    $row = mysqli_fetch_assoc($GETLIST);

    $delimiter=",";
    $filename= "eform_export_".$lastname."_". date('Ymd').".csv";

    $f= fopen('php://memory', 'w');
    $fields = array('ID', 'NAME', 'System','ILL EMAIL', 'PHONE', 'Dept', 'Street Address', 'City, State, Zip', 'LOC Location', 'Email Alert', 'Participant', 'Syspend');

    fputcsv($f, $fields, $delimiter);

    while ($row=$GETLIST->fetch_assoc()) {
        $lineData = array($row['recnum'], $row['Name'], $row['system'], $row['ill_email'], $row['phone'], $row['address1'], $row['address2'], $row['address3'],$row['loc'], $ealert, $status,$suspend);

        fputcsv($f, $lineData, $delimiter);
    }




    fseek($f, 0);

    $file = "./sites/seal.senylrc.org/files/".$filename; // File to Save

    chmod($file, 0777);
    file_put_contents($file, $f);
    echo "<a href='/sites/seal.senylrc.org/files/".$filename."'>Export Ready For Download</a>";

}else{
    echo "Please log into the system";
}
