<?php

$folder = '../roundcube';


$oldversion = NULL;
if (array_key_exists('oldversion', $_GET)) {
  $oldversion = $_GET['oldversion'];
}
$newversion = NULL;
if (array_key_exists('newversion', $_GET)) {
  $newversion = $_GET['newversion'];
}

if (array_key_exists('action', $_GET)) {
  switch ($_GET['action']) {
    case 'backup':
      if ($oldversion != NULL) {
        backupAsZip($folder, $oldversion);
        echo "Backup done.";      
      } else {
        echo "'oldversion' parameter required!";
      }
      break;
    case 'download':
      if ($newversion != NULL) {
        getLatest($newversion);
        echo "Got latest version.";
      } else {
        echo "'newversion' parameter required!";
      }
      break;
    case 'update':
      if ($newversion != NULL && $oldversion != NULL) {
        extractTarGz($newversion);
        echo "Extracted.. ";
        move($oldversion, $newversion);
        echo "Moved.. ";
        copyConfig($oldversion);
        echo "Copied config.. ";
        cleanup($oldversion);
        echo "Cleanup done.. ";
        echo "Done updating!";
      } else {
        echo "'newversion' or 'oldversion' parameter missing!";
      }
      break;
    default:
      echo "Valid actions: backup, download, update";
  }
} else {
  echo "'action' parameter required!";
}



function backupAsZip($folder, $oldversion) {
  // Get real path for our folder
  $rootPath = realpath($folder);

  // Initialize archive object
  $zip = new ZipArchive;
  $zip->open('roundcubemail-' . $oldversion . '.zip', ZipArchive::CREATE);

  // Create recursive directory iterator
  $files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($rootPath),
      RecursiveIteratorIterator::LEAVES_ONLY
  );

  foreach ($files as $name => $file) {
      // Get real path for current file
      $filePath = $file->getRealPath();

      // Add current file to archive
      $zip->addFile($filePath);
  }

  // Zip archive will be created only after closing object
  $zip->close();
}


function getLatest($newversion) {
  $url = 'http://garr.dl.sourceforge.net/project/roundcubemail/roundcubemail/' . $newversion . '/roundcubemail-' . $newversion . '.tar.gz';
  
  $path = __DIR__ . '/roundcubemail-' . $newversion . '.tar.gz';
  
  echo "Downloading " . $url . " to " . $path . '<br>';
  
  # open file to write
  $fp = fopen ($path, 'w+');
  # start curl
  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, $url );
  # set return transfer to false
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
  curl_setopt( $ch, CURLOPT_BINARYTRANSFER, true );
  curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
  # increase timeout to download big file
  curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
  # follow redirects
  //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  # write data to local file
  curl_setopt( $ch, CURLOPT_FILE, $fp );
  # execute curl
  curl_exec( $ch );
  # close curl
  curl_close( $ch );
  # close local file
  fclose( $fp );
}

function extractTarGz($newversion) {
  rename(__DIR__ . '/roundcubemail-' . $newversion . '.tar.gz', __DIR__ . '/roundcubemail.tar.gz');
  
  $p = new PharData(__DIR__ . '/roundcubemail.tar.gz');
  $p->decompress();
  $phar = new PharData(__DIR__ . '/roundcubemail.tar');
  $phar->extractTo('../');
}

function move($oldversion, $newversion) {
  rename($folder, $folder . '-' . $oldversion);
  rename('../roundcubemail-' . $newversion, $folder);
}

function copyConfig($oldversion) {
  copy($folder . '-' . $oldversion . '/config/db.inc.php', $folder . '/config/db.inc.php');
  copy($folder . '-' . $oldversion . '/config/main.inc.php', $folder . '/config/main.inc.php');
}

function cleanup($oldversion) {
  unlink(__DIR__ . '/roundcubemail.tar.gz');
  unlink(__DIR__ . '/roundcubemail.tar');
  delTree($folder . '-' . $oldversion);
}

function delTree($dir) { 
  $files = array_diff(scandir($dir), array('.','..')); 
  foreach ($files as $file) { 
    (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
  } 
  return rmdir($dir); 
} 

?>
