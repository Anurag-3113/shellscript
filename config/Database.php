<?php
class Database {
//192.168.0.104

    private $ini_array= array();
    private $db_host = '192.168.33.20';
  //  private $db_host = 'localhost';
    private $db_user = 'root';
    private $db_pass = 'malli123';
//    private $db_name = 'ThinkExam_v1_3_testing_13Jan';

	private $db_name = 'malli';
    private $con = false;         // Checks to see if the connection is active // Database



            // Database


    /*
     * End edit
     */


    private $result = array();          // Results that are returned from the query

    /*
     * Connects to the database, only one connection
     * allowed
     */
    private $logger;



    public function __construct() {

       $this->logger = Logger::getLogger(__CLASS__);
       $this->logger->debug('Hello!');
    }

    // $this->logger = Logger::getLogger(__CLASS__);
    // $this->logger->debug('Hello!');




    public function connect() {
        if (!$this->con) {
            $myconn = @mysql_connect($this->db_host, $this->db_user, $this->db_pass);
            $this->logger->info("connection set: " . $myconn);
            if ($myconn) {
                $seldb = @mysql_select_db($this->db_name, $myconn);
                $this->logger->info("db select: " . $seldb);
                if ($seldb) {
                    $this->logger->info("db selected");
                    $this->con = true;
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /*
     * Changes the new database, sets all current results
     * to null
     */

    public function setDatabase($name) {
        if ($this->con) {
            if (@mysql_close()) {
                $this->con = false;
                $this->results = null;
                $this->db_name = $name;
                $this->connect();
            }
        }
    }

    /*
     * Check`s to see if the table exists when performing
     * queries
     */

public function tableExists($table) {
        $query='SHOW TABLES FROM ' . $this->db_name . ' LIKE "' . $table . '"';
        $this->logger->info("Executing Query tableExists: " . $query);
        $query=$this->protect($query);
        $tablesInDb = @mysql_query($query);
        mysql_close($this->con);
        if ($tablesInDb) {
            if (mysql_num_rows($tablesInDb) == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    /*
     * Selects information from the database.
     * Required: table (the name of the table)
     * Optional: rows (the columns requested, separated by commas)
     *           where (column = value as a string)
     *           order (column DIRECTION as a string)
     */

    public function select($table, $rows = '*', $where = null, $order = null) {
        $q = 'SELECT ' . $rows . ' FROM ' . $table;
        if ($where != null)
            $q .= ' WHERE ' . $where;
        if ($order != null)
            $q .= ' ORDER BY ' . $order;
        $q=$this->protect($q);
        $query = @mysql_query($q);
        if ($query) {
            $this->numResults = mysql_num_rows($query);
            for ($i = 0; $i < $this->numResults; $i++) {
                $r = mysql_fetch_array($query);
                $key = array_keys($r);
                for ($x = 0; $x < count($key); $x++) {
                    // Sanitizes keys so only alphavalues are allowed
                    if (!is_int($key[$x])) {
                        if (mysql_num_rows($query) > 1)
                            $this->result[$i][$key[$x]] = $r[$key[$x]];
                        else if (mysql_num_rows($query) < 1)
                            $this->result = null;
                        else
                            $this->result[$key[$x]] = $r[$key[$x]];
                    }
                }
            }
            mysql_close($this->con);
            return true;
        }
        else {
            return false;
        }
    }

    /*
     * Insert values into the table
     * Required: table (the name of the table)
     *           values (the values to be inserted)
     * Optional: rows (if values don't match the number of rows)
     */

    public function insert($table, $values, $rows = null) {
        if ($this->tableExists($table)) {
            $insert = 'INSERT INTO ' . $table;
            if ($rows != null) {
                $insert .= ' (' . $rows . ')';
            }

            for ($i = 0; $i < count($values); $i++) {
                if (is_string($values[$i]))
                    $values[$i] = '"' . $values[$i] . '"';
            }
            $values = implode(',', $values);
            $insert .= ' VALUES (' . $values . ')';
            $insert=$this->protect($insert);
            $ins = @mysql_query($insert);
            mysql_close($this->con);

            if ($ins) {
                return true;
            } else {
                return false;
            }
        }
    }

    /*
     * Insert values into the table
     * Required: table (the name of the table)
     *           values (the values to be inserted)
     * Optional: rows (if values don't match the number of rows)
     */

    public function insertAssociativeArray($table, $userList) {
       $this->connect();

        if ($this->tableExists($table)) {

            $i = 0;
            foreach ($userList as $key => $value) {
                if ($i == 0) {

                  $ins = "INSERT INTO $table set $key='$value'";

                } else {
                    $ins = $ins . ",$key='$value'";
                }
                $i = $i + 1;
            }
            $ins;

            $this->logger->info("Executing Query for insertAssociativeArray: " . $ins);
            $ins=$this->protect($ins);
            $result = @mysql_query($ins) or die(header('location: source/util/error/error.html'));
            mysql_close($this->con);


            if ($result) {
                return;
            } else {
                return;
            }
        }
    }

    /* --------------------------- maxrow---------------------------------------------- */

    public function maxId($table, $fieldName, $where = null) {
        $this->connect();
        $query = "SELECT max($fieldName) as maxid  FROM $table";
        if ($where != null)
            $query .= " WHERE $where";
        $query=$this->protect($query);
        $sqlRs = mysql_query($query)  ;
        $maxrow = mysql_fetch_array($sqlRs);
        mysql_close($this->con);
        return $maxrow['maxid'];
    }

    public function duplicateField($table, $fieldName, $where = null) {
        $this->connect();
        $query = "SELECT $fieldName  FROM  $table";
        if ($where != null)
            $query.=" WHERE  $where";

        $query=trim($this->protect($query));
       $sqlRs = mysql_query($query)  ;
       $maxrow = mysql_num_rows($sqlRs);
        mysql_close($this->con);
        return $maxrow;
    }

    /*

      field addUpdateMenu
     */

    public function addUpdate($table, $listValue, $updatefield, $menuOrder) {

        $this->connect();

        foreach ($updatefield as $key => $value) {
            $query = "update $table set  $key='$value' where $key='$menuOrder'";
            $this->logger->info("Executing Query addUpdate: " . $query);
            mysql_query($query) or die(header('location: source/util/error/error.html'));
            mysql_close($this->con);
        }



        foreach ($listValue as $key => $value) {
            if ($i == 0) {
                $ins = "INSERT INTO $table set $key='$value'";
            } else {
                $ins = $ins . ",$key='$value'";
            }
            $i = $i + 1;
        }

        $ins=$this->protect($ins);
        $ins = @mysql_query($ins);
        mysql_close($this->con);
    }

    /*
     * Deletes table or records where condition is true
     * Required: table (the name of the table)
     * Optional: where (condition [column =  value])
     */

    public function delete($table, $where = null) {

        $this->connect();

        if ($this->tableExists($table)) {
            if ($where == null) {
                $delete = 'DELETE ' . $table;
            } else {
                $delete = 'DELETE FROM ' . $table . ' WHERE ' . $where;
            }
            $delete=$this->protect($delete);
            $del = @mysql_query($delete);
            mysql_close($this->con);

            if ($del) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * Updates the database with the values sent
     * Required: table (the name of the table to be updated
     *           rows (the rows/values in a key/value array
     *           where (the row/condition in an array (row,condition) )
     */

    public function update($table, $rows, $where) {



        $this->connect();
        if ($this->tableExists($table)) {


            // Parse the where values
            // even values (including 0) contain the where rows
            // odd values contain the clauses for the row
            for ($i = 0; $i < count($where); $i++) {

                if ($i % 2 != 0) {
                    if (is_string($where[$i])) {
                        if (($i + 1) != null)
                            $where[$i] = '"' . $where[$i] . '" AND ';
                        else
                            $where[$i] = '"' . $where[$i] . '"';
                    }
                }
            }
             $where = implode('=', $where);


            $update = 'UPDATE ' . $table . ' SET ';
            $keys = array_keys($rows);
            for ($i = 0; $i < count($rows); $i++) {
                if (is_string($rows[$keys[$i]])) {
                    $update .= $keys[$i] . '="' . $rows[$keys[$i]] . '"';
                } else {
                    $update .= $keys[$i] . '=' . $rows[$keys[$i]];
                }

                // Parse to add commas
                if ($i != count($rows) - 1) {
                    $update .= ',';
                }
            }

                

            if ($update) {
               $update .= ' WHERE ' . $where;
                $update=$this->protect($update);
                $this->logger->info("Executing Query for Update: " . $update);
                $query = mysql_query($update) or die(header('location: source/util/error/error.html'));
                mysql_close($this->con);

                if ($query) {

                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /*
     * Returns the result set
     */

    public function getResult() {
        return $this->result;
    }

    /*
     * Returns the result set
     */

    function executeQuery($sql) {
        $sql=$this->protect($sql);
        $this->logger->info("Executing Query : " . $sql);
        $this->connect();
        $this->protect($sql);
        $result = @mysql_query($sql) or die(header('location: source/util/error/error.html'));

        return $result;
    }

    /*
     * Get Single Result.
     */

    function getSingleResult($sql) {
        $response = "";
        $sql=$this->protect($sql);
        $result = mysql_query($sql)  ;
        if ($line = mysql_fetch_array($result)) {
            $response = $line[0];
        }
        mysql_close($this->con);
        return $response;
    }

    public function getListBox($table, $rows = '*', $where = null, $order = null, $selected) {
        $this->connect();
        $q = 'SELECT ' . $rows . ' FROM ' . $table;
        if ($where != null)
            $q .= ' WHERE ' . $where;
        if ($order != null)
           $q .= ' ORDER BY ' . $order;
       $q=$this->protect($q);
        $query = $this->executeQuery($q);
        while ($result = mysql_fetch_array($query)) {
           echo $selected;

            if ($selected == $result[0]) {
                $sal = " selected";
            } else {
                $sal = "";
            }

            echo "<option $sal value=\"" . $result[0] . "\">" . $result[1] . "</option>\n";
        }
    }

    public function Pagination($tbl_name, $limit, $path, $column=NULL) {
        if ($column == NULL)
            $query = "SELECT COUNT(*) as num FROM $tbl_name";
        else
            $query = "SELECT COUNT($column) as num FROM $tbl_name";

        $this->logger->info("Executing Query for pagination: " . $query);
        $query=$this->protect($query);
        $row = mysql_fetch_array(mysql_query($query));
        mysql_close($this->con);
        $total_pages = $row['num'];

        $adjacents = "2";

        $page = (int) (!isset($_GET["page"]) ? 1 : $_GET["page"]);
        $page = ($page == 0 ? 1 : $page);

        if ($page)
            $start = ($page - 1) * $limit;
        else
            $start = 0;

        //$sql = "SELECT id FROM $tbl_name LIMIT $start, $limit";
        //$result = mysql_query($sql);

        $prev = $page - 1;
        $next = $page + 1;
        if ($limit != '' or $limit != 0) {
            $lastpage = ceil($total_pages / $limit);
        }
        $lpm1 = $lastpage - 1;
        $from=$start+1;
        $to=$start+$limit;
        if($to>=$total_pages)
            $to=$total_pages;
        $pagination = "";
        if ($lastpage > 1) {
            $pagination .= "<div class='pagination'>";
            if ($page > 1)
                $pagination.= "<div id='displayitem4'>Item <b>".$from."-".$to."</b> of $total_pages </div><a href='" . $path . "page=$prev'>« previous</a>";
            else
                $pagination.= "<div id='displayitem4'>Item <b>".$from."-".$to."</b> of $total_pages </div> <span class='disabled'>« previous</span>";

            if ($lastpage < 7 + ($adjacents * 2)) {
                for ($counter = 1; $counter <= $lastpage; $counter++) {
                    if ($counter == $page)
                        $pagination.= "<span class='current'>$counter</span>";
                    else
                        $pagination.= "<a href='" . $path . "page=$counter'>$counter</a>";
                }
            }
            elseif ($lastpage > 5 + ($adjacents * 2)) {
                if ($page < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        if ($counter == $page)
                            $pagination.= "<span class='current'>$counter</span>";
                        else
                            $pagination.= "<a href='" . $path . "page=$counter'>$counter</a>";
                    }
                    $pagination.= "...";
                    $pagination.= "<a href='" . $path . "page=$lpm1'>$lpm1</a>";
                    $pagination.= "<a href='" . $path . "page=$lastpage'>$lastpage</a>";
                }
                elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                    $pagination.= "<a href='" . $path . "page=1'>1</a>";
                    $pagination.= "<a href='" . $path . "page=2'>2</a>";
                    $pagination.= "...";
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                        if ($counter == $page)
                            $pagination.= "<span class='current'>$counter</span>";
                        else
                            $pagination.= "<a href='" . $path . "page=$counter'>$counter</a>";
                    }
                    $pagination.= "..";
                    $pagination.= "<a href='" . $path . "page=$lpm1'>$lpm1</a>";
                    $pagination.= "<a href='" . $path . "page=$lastpage'>$lastpage</a>";
                }
                else {
                    $pagination.= "<a href='" . $path . "page=1'>1</a>";
                    $pagination.= "<a href='" . $path . "page=2'>2</a>";
                    $pagination.= "..";
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                        if ($counter == $page)
                            $pagination.= "<span class='current'>$counter</span>";
                        else
                            $pagination.= "<a href='" . $path . "page=$counter'>$counter</a>";
                    }
                }
            }

            if ($page < $counter - 1)
                $pagination.= "<a href='" . $path . "page=$next'>next »</a>";
            else
                $pagination.= "<span class='disabled'>next »</span>";
            $pagination.= "</div>\n";
        }


        return $pagination;
    }

    /* Pagination Useing Multiple table */

    public function PaginationMultipleTable($sqlQuery, $limit, $path, $column=NULL) {


        if ($column == NULL)
            $query = $sqlQuery;
        else
            $query = $sqlQuery;
        $this->logger->info("Executing Query for pagination: " . $query);
        $query=$this->protect($query);
        $row = mysql_num_rows(mysql_query($query));
        mysql_close($this->con);
        $total_pages = $row;

        $adjacents = "2";

        $page = (int) (!isset($_GET["page"]) ? 1 : $_GET["page"]);
        $page = ($page == 0 ? 1 : $page);

        if ($page)
            $start = ($page - 1) * $limit;
        else
            $start = 0;

        //$sql = "SELECT id FROM $tbl_name LIMIT $start, $limit";
        //$result = mysql_query($sql);

        $prev = $page - 1;
        $next = $page + 1;
        if ($limit != '' or $limit != 0) {
            $lastpage = ceil($total_pages / $limit);
        }
        $lpm1 = $lastpage - 1;
        $from=$start+1;
        $to=$start+$limit;
        if($to>=$total_pages)
            $to=$total_pages;
        $pagination = "";
        if ($lastpage > 1) {
            $pagination .= "<div class='pagination'>";
            if ($page > 1)
                $pagination.= "<div id='displayitem4'>Item <b>".$from."-".$to."</b> of $total_pages </div><a href='" . $path . "page=$prev'>« previous</a>";
            else
                $pagination.= "<div id='displayitem4'>Item <b>".$from."-".$to."</b> of $total_pages </div> <span class='disabled'>« previous</span>";

            if ($lastpage < 7 + ($adjacents * 2)) {
                for ($counter = 1; $counter <= $lastpage; $counter++) {
                    if ($counter == $page)
                        $pagination.= "<span class='current'>$counter</span>";
                    else
                        $pagination.= "<a href='" . $path . "page=$counter'>$counter</a>";
                }
            }
            elseif ($lastpage > 5 + ($adjacents * 2)) {
                if ($page < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        if ($counter == $page)
                            $pagination.= "<span class='current'>$counter</span>";
                        else
                            $pagination.= "<a href='" . $path . "page=$counter'>$counter</a>";
                    }
                    $pagination.= "...";
                    $pagination.= "<a href='" . $path . "page=$lpm1'>$lpm1</a>";
                    $pagination.= "<a href='" . $path . "page=$lastpage'>$lastpage</a>";
                }
                elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                    $pagination.= "<a href='" . $path . "page=1'>1</a>";
                    $pagination.= "<a href='" . $path . "page=2'>2</a>";
                    $pagination.= "...";
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                        if ($counter == $page)
                            $pagination.= "<span class='current'>$counter</span>";
                        else
                            $pagination.= "<a href='" . $path . "page=$counter'>$counter</a>";
                    }
                    $pagination.= "..";
                    $pagination.= "<a href='" . $path . "page=$lpm1'>$lpm1</a>";
                    $pagination.= "<a href='" . $path . "page=$lastpage'>$lastpage</a>";
                }
                else {
                    $pagination.= "<a href='" . $path . "page=1'>1</a>";
                    $pagination.= "<a href='" . $path . "page=2'>2</a>";
                    $pagination.= "..";
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                        if ($counter == $page)
                            $pagination.= "<span class='current'>$counter</span>";
                        else
                            $pagination.= "<a href='" . $path . "page=$counter'>$counter</a>";
                    }
                }
            }

            if ($page < $counter - 1)
                $pagination.= "<a href='" . $path . "page=$next'>next »</a>";
            else
                $pagination.= "<span class='disabled'>next »</span>";
            $pagination.= "</div>\n";
        }


        return $pagination;
    }

    public function DisplayCategory($catid='', $sep='', $pcatid=0, $selected='', $tableNeme) {

        $this->connect();
        $sql = "select Id ,MenuName,ParentID from $tableNeme where ParentID=$pcatid";
        $sql=$this->protect($sql);
        $result = mysql_query($sql) or die(header('location: source/util/error/error.html'));
        mysql_close($this->con);


        while ($line = mysql_fetch_array($result)) {


            $parent_cat_id = $line['ParentID'];
            $combo = "<option value=" . $line['Id'];
            if ($line['Id'] == $selected) {
                $combo.=" selected";
            }
            $combo.=">$sep" . $line['MenuName'] . "</option>";

            echo $combo;
            $this->DisplaySubCategory($line['Id'], $sep, $parent_cat_id, $selected, $tableNeme);
        }
    }

    public function DisplaySubCategory($subid, $sep, $pcid, $subselected='', $tableNeme) {
        $this->connect();
        $sep.="&raquo;";
        $sql = "select Id ,MenuName  from $tableNeme where ParentID='$subid'";
        $sql=$this->protect($sql);
        $r1 = mysql_query($sql);

        while ($lr = mysql_fetch_array($r1)) {
            $pcid = $lr[ParentID];
            $nu = "<option value='" . $lr['Id'] . "'";
            if ($lr['Id'] == $subselected) {
                $nu.= " selected ";
            }
            $nu.=">$sep&nbsp;" . $lr['MenuName'] . "&nbsp;</option>";
            echo $nu;
            $this->DisplaySubCategory($lr['Id'], $sep, $pcid, $subselected, $tableNeme);
        }
        mysql_close($this->con);
    }

    function backup_table($table_name, $backup_table_name) {

        db_query("CREATE TABLE $backup_table_name LIKE $table_name");

        db_query("ALTER TABLE $backup_table_name DISABLE KEYS");

        db_query("INSERT INTO $backup_table_name SELECT * FROM $table_name");

        db_query("ALTER TABLE $backup_table_name ENABLE KEYS");
    }


        function get_Id(){



        $this->connect();
        $id = mysql_insert_id();
        return $id;
    }
    
    
    //added by sunil
    
    //get all information return in object
    public function getAllInformation($table,$condition)
    {
       $this->connect();
       $data=array(); 
       $sql="SELECT * FROM ". $table." ". $condition;  
       $sql=$this->protect($sql);   
       $this->logger->info("Excuting query:",$sql);
       $qry=@mysql_query($sql)or die(header('location: source/util/error/error.html'));       
       while($result=mysql_fetch_object($qry))
       {
        $data[]=$result;           
       }      
       mysql_close($this->con);
       return $data;       
        
    }
    
      //get single row information return in object
    public function getSingleRowInformation($table,$condition)
    {
       $this->connect();
       $data=array();
       $sql="SELECT * FROM ". $table." ".$condition; 
       $sql=$this->protect($sql);   
       $this->logger->info("Excuting query:",$sql);
       $qry=@mysql_query($sql)or die(header('location: source/util/error/error.html'));      
       $result=mysql_fetch_object($qry);                 
        mysql_close($this->con);
       return $result;       
        
    }
    
    //update Information
    
    function updateInformation($data,$table,$cond){	 
		$this->connect();
                $dbval='';      	
		foreach($data as $index => $val)
		{	
            $val=$this->protect($val);
			$dbval.= ' `' . $index . '`=\'' .$val. '\',';
		}
		$dbval = substr($dbval, 0, -1);
		 $qry = "UPDATE `".$table."` SET {$dbval} ".$cond." ";	                
                 $this->logger->info("Excuting query:",$qry);                 
                 $upd=@mysql_query($qry);
                 mysql_close($this->con);
                 return $upd;            
                 
    }        
    
    //end //added by sunil

    public function protect($string) 
    { 
         return $string;
//      if (ini_get('magic_quotes_gpc') == 'off') // check if magic_quotes_gpc is on and if not add slashes
//            { 
//             $string = addslashes($string); 
//            }  
//            // move html tages from inputs
//            //$string = htmlentities($string, ENT_QUOTES);
//            //removing most known vulnerable words
//           $codes = array("script","java","applet","iframe","meta","object","html",  ";", "%",
//            "sleep","wakeup"
//            
//            );
//            $string = str_replace($codes,"",$string);
//            //return clean string
//            return $string; 
            }

   }

?>
