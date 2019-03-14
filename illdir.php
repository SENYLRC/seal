<?php
###illdir_search.php####
require '../seal_script/seal_function.php';

#check if an action has been requested
if (isset($_REQUEST['action'])) {
    #set the pageaction to what has been requested
    $pageaction = $_REQUEST['action'];
} else {
    $pageaction = '0';
}

if (isset($_REQUEST['libname'])) {
    $libname = $_REQUEST['libname'];
}
if (isset($_REQUEST['system'])) {
    $system = $_REQUEST['system'];
}

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);


if (($_SERVER['REQUEST_METHOD'] == 'POST')   || (isset($_GET{'page'}))) {
    #Display the searched results
    $libname = mysqli_real_escape_string($db, $libname);
    $libemail = mysqli_real_escape_string($db, $libemail);
    $GETLISTSQL="SELECT * FROM `$sealLIB` WHERE `Name` LIKE '%$libname%' and `system` LIKE '%$system%' and participant = '1' ORDER BY Name Asc";
    $retval = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNTwhole = mysqli_num_rows($retval);
    $rec_limit = 50;
    $rowpage = mysqli_fetch_array($retval, MYSQLI_NUM);
    $rec_count = $rowpage[0];
    $GETLIST = mysqli_query($db, $GETLISTSQL1);
    $GETLISTCOUNT = mysqli_num_rows($GETLIST);
    #echo " $GETLISTCOUNTwhole  results";
    if (isset($_GET{'page'})) {
        $page = $_GET{'page'} + 1;
        $offset = $rec_limit * $page ;
    } else {
        $page = 0;
        $offset = 0;
    }
    $left_rec = $rec_count - ($page * $rec_limit);
    $GETLISTSQL="$GETLISTSQL LIMIT $offset, $rec_limit";
    #echo $GETLISTSQL;
    $GETLIST = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNT = mysqli_num_rows($GETLIST);
} else {
    $GETLISTSQL="SELECT * FROM `$sealLIB` where participant = '1' ORDER BY Name Asc";
    $retval = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNTwhole = mysqli_num_rows($retval);
    $rec_limit = 50;
    $rowpage = mysqli_fetch_array($retval, MYSQLI_NUM);
    $rec_count = $rowpage[0];

    if (isset($_GET{'page'})) {
        $page = $_GET{'page'} + 1;
        $offset = $rec_limit * $page ;
    } else {
        $page = 0;
        $offset = 0;
    }
    $left_rec = $rec_count - ($page * $rec_limit);
    $GETLISTSQL="$GETLISTSQL LIMIT $offset, $rec_limit";
    #echo $GETLISTSQL;
    $GETLIST = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNT = mysqli_num_rows($GETLIST);
}
  ?>
  <h3>Search the directory</h3>
  <form action="<?php echo "".$_SERVER['REDIRECT_URL']."?". $_SERVER['QUERY_STRING']."";?>" method="post">
  <B>Library Name:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libname"><br>
  <B>Library System</b> <select name="system">
    <option value=""></option>
    <option value="DU">Dutchess BOCES</option>
    <option value="MH">Mid-Hudson Library System</option>
    <option value="OU">Orange Ulster BOCES</option>
    <option value="RC">Ramapo Catskill Library System</option>
    <option value="RB">Rockland BOCES</option>
    <option value="SE">SENYLRC</option>
    <option value="SB">Sullivan BOCES</option>
    <option value="UB">Ulster BOCES</option>
  </select>
  <br>
  <input type="submit" value="Submit">
  </form>
  <?php
  #List All Libraries
  echo "$GETLISTCOUNTwhole results<bR>";
  echo "<div class='illDirTable'>";
  echo "<div class='illDirTableRow'>";
  $count = 1;
  $rowcount =1;
  while ($row = mysqli_fetch_assoc($GETLIST)) {
      $libaddress2 = $row["address2"];
      $libaddress3 = $row["address3"];
      $libname = $row["Name"];
      $libphone = $row["phone"];
      $illemail = $row["ILL Email"];
      $libparticipant = $row["participant"];
      $oclc = $row["oclc"];
      $loc = $row["loc"];
      $libsuspend = $row["suspend"];
      $system = $row["system"];
      $book = $row["book"];
      $journal = $row["journal"];
      $av = $row["av"];
      $reference = $row["reference"];
      $ebook = $row["ebook"];
      if ($libsuspend=="0") {
          $libsuspend="Yes";
      } else {
          $libsuspend="No";
      }
      if ($libparticipant =="1") {
          $libparticipant ="Yes";
      } else {
          $libparticipant ="No";
      }
      if ($book =="1") {
          $book ="Yes";
      } else {
          $book ="No";
      }
      if ($journal =="1") {
          $journal ="Yes";
      } else {
          $journal ="No";
      }
      if ($av =="1") {
          $av ="Yes";
      } else {
          $av ="No";
      }
      if ($reference =="1") {
          $reference ="Yes";
      } else {
          $reference ="No";
      }
      if ($ebook =="1") {
          $ebook ="Yes";
      } else {
          $ebook ="No";
      }

      echo "<div class='illDirTableCell'>";
      echo "Name: <strong> $libname</strong><br>";
      echo "Address: <strong> $libaddress2 </strong><br>";
      echo "&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> $libaddress3 </strong><br>";
      echo "Phone: <strong> $libphone</strong><br>";
      if ($user->uid) {
          echo "ILL Email(s): <a href='mailto:$illemail' target='_blank'>$illemail</a><br>";
      }

      echo "OCLC Symbol: <strong> $oclc</strong><br>";
      echo "ILL Code: <strong> $loc</strong><br>";
      echo "Accepting Requests: <strong> $libsuspend </strong>";
      echo "<br><br>";
      echo "<button onclick='showHide($count)'>Show loaning options</button>";
      echo "<span class='loadoptions' id='showhide-$count' style='display: none'>";
      echo "Loaning Books: <strong>$book</strong><br>";
      echo "Loaning Journals or Articles: <strong>$journal</strong><br>";
      echo "Loaning Audio/Video: <strong>$av</strong><br>";
      echo "Loaning Reference: <strong>$reference</strong><br>";
      echo "Loaning E-Books: <strong>$ebook</strong><br><br>";
      echo "</div>"; #end the illDirTableCell
      if ($count++ % 2 == 0) {
          echo "</div>"; #end the illDirTableRow
          if ($rowcount++ % 2 == 0) {
              echo "<div class='illDirTableRow'>"; #Start the next illDirTableRow
          } else {
              echo "<div class='illDirTableRowGrey'>"; #Start the next illDirTableRow
          }
          $no++;
      }
  }
  echo "</div>";  #end the illDirTable
  if (($page > 0) && (($offset +  $rec_limit)<$GETLISTCOUNTwhole)) {
      $last = $page - 2;
      echo "<a href='".$_SERVER['REDIRECT_URL']."?page=$last\'>Last 50 Records</a> |";
      echo "<a href='".$_SERVER['REDIRECT_URL']."?page=$page\'>Next 50 Records</a>";
  } elseif (($page == 0) && ($GETLISTCOUNTwhole  > $rec_limit)) {
      echo "<a href='".$_SERVER['REDIRECT_URL']."?page=$page\'>Next 50 Records</a>";
  } elseif (($left_rec < $rec_limit)  && ($GETLISTCOUNTwhole > $rec_limit)) {
      $last = $page - 2;
      echo "<a href='".$_SERVER['REDIRECT_URL']."?page=$last\'>Last 50 Records</a>";
  }


?>
