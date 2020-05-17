<?php
    App::uses('Mysql', 'Model/Datasource/Database');
    /**          
     * Logs MySQL queries into debug file
     */
    class MysqlEx extends Mysql {

        function logQuery($sql) {
            parent::logQuery($sql);
            if (Configure::read('Cake.logQuery')) {
               // debug( $this->_queriesLog . ':' . $sql);
               $this->saveQueryLog($this->_queriesLog);
            }
        }
        function saveQueryLog($sql){
          //log error into a txt file and every day has unique file that contains the errors.
             //save file to tmp\logs\sql folder
             $log_dir_path = LOGS.'sql';
             $res1 = is_dir($log_dir_path);
             if($res1 != 1)
             {
               $res2= mkdir($log_dir_path, 0777, true);
             }
             $file = $log_dir_path.'/'.date('d-m-Y').".log";
             $message = date('Y-m-d G:i:s') . ' - ' . var_dump($sql);
            // $message = $time . ' - ' . $sql;
             $handle = fopen($file, 'a+');
             if($handle !== false)
             {
               fwrite($handle, $message . "\n");
               fclose($handle); 
             }
         }

    }