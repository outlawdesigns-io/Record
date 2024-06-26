<?php

require_once __DIR__ . '/Db/db.php';
require_once __DIR__ . '/RecordInterface.php';

if(!isset($GLOBALS['db'])){
    $db = new \DB();
}

abstract class Record implements RecordBehavior{
    protected $id;

    protected $suite;
    protected $driver;
    protected $database;
    protected $table;
    protected $primaryKey;

    public function __construct($database,$table,$primaryKey,$id){
        $this->database = $database;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        if(!is_null($id)){
            $this->id = $id;
            $this->_build();
        }
    }
    protected function _build(){
        $results = $GLOBALS['db']
            ->database($this->database)
            ->table($this->table)
            ->select("*")
            ->where($this->primaryKey,"=",$this->id)
            ->get();
        if(!mysqli_num_rows($results)){
            throw new \Exception('Invalid UID');
        }
        while($row = mysqli_fetch_assoc($results)){
            foreach($row as $key=>$value){
                if(is_array($this->$key)){
                  $this->$key = $this->_toArray($value);
                }else{
                  $this->$key = $value;
                }
            }
        }
        return $this;
    }
    protected function _buildId(){
        $results = $GLOBALS['db']
            ->database($this->database)
            ->table($this->table)
            ->select("$this->primaryKey")
            ->orderBy("$this->primaryKey desc limit 1")
            ->get();
        while($row = mysqli_fetch_assoc($results)){
            $this->id = $row[$this->primaryKey];
        }
        return $this;
    }
    protected function _toArray($string){
      return explode(',',$string);
    }
    protected function _toString($array){
      return implode(',',$array);
    }
    public function create(){
        $reflection = new \ReflectionObject($this);
        $data = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $upData = array();
        foreach($data as $obj){
            $key = $obj->name;
            if($key == 'created_date' || $key == 'updated_date'){
                $upData[$key] = date("Y-m-d H:i:s");
            }elseif(is_array($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->cleanString($this->_toString($this->$key));
            }elseif(!is_null($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->cleanString($this->$key);
            }
        }
        unset($upData[$this->primaryKey]);
        $results = $GLOBALS['db']
            ->database($this->database)
            ->table($this->table)
            ->insert($upData)
            ->put();
        $this->_buildId()->_build();
        return $this;
    }
    public function update(){
        $reflection = new \ReflectionObject($this);
        $data = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $upData = array();
        foreach($data as $obj){
            $key = $obj->name;
            if($key == 'updated_date'){
                $upData[$key] = date("Y-m-d H:i:s");
            }elseif(is_array($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->cleanString($this->_toString($this->$key));
            }elseif(!is_null($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->cleanString($this->$key);
            }
        }
        if(isset($upData['created_date'])){
            unset($upData['created_date']);
        }
        $key = $this->primaryKey;
        $results = $GLOBALS['db']
            ->database($this->database)
            ->table($this->table)
            ->update($upData)
            ->where($this->primaryKey,"=",$this->$key)
            ->put();
        return $this;
    }
    public function delete(){
      $key = $this->primaryKey;
      $results = $GLOBALS['db']
         ->database($this->database)
         ->table($this->table)
         ->delete()
         ->where($this->primaryKey,"=",$this->$key)
         ->put();
      return $this;
    }
    public function setFields($updateObj){
        if(!is_object($updateObj)){
            throw new Exception('Trying to perform object method on non object.');
        }
        foreach($updateObj as $key=>$value){
            $this->$key = $value;
        }
        return $this;
    }
    public static function cleanString($string){
      $string = preg_replace("/'/","''",$string);
      $string = preg_replace("/\\\\/","\\\\\\",$string);
      return $string;
    }
    protected static function _search($db,$table,$primaryKey,$key,$value){
        $data = array();
        $results = $GLOBALS['db']
            ->database($db)
            ->table($table)
            ->select($primaryKey)
            ->where($key,"like","'%" . self::cleanString($value) . "%'")
            ->get();
        while($row = mysqli_fetch_assoc($results)){
            $data[] = $row[$primaryKey];
        }
        return $data;
    }
    protected static function _getAll($db,$table,$primaryKey){
        $data = array();
        $results = $GLOBALS['db']
            ->database($db)
            ->table($table)
            ->select($primaryKey)
            ->get();
        while($row = mysqli_fetch_assoc($results)){
            $data[] = $row[$primaryKey];
        }
        return $data;
    }
    protected static function _getRecent($db,$table,$primaryKey,$limit){
        $data = array();
        $results = $GLOBALS['db']
            ->database($db)
            ->table($table)
            ->select($primaryKey)
            ->orderBy($primaryKey . " desc limit " . $limit)
            ->get();
        while($row = mysqli_fetch_assoc($results)){
            $data[] = $row[$primaryKey];
        }
        return $data;
    }
    protected static function _browse($db,$table,$key){
        $data = array();
        $results = $GLOBALS['db']
            ->database($db)
            ->table($table)
            ->select("distinct " . $key)
            ->get();
        while($row = mysqli_fetch_assoc($results)){
            $data[] = $row[$key];
        }
        return $data;
    }
    protected static function _count($db,$table){
        $data = array();
        $results = $GLOBALS['db']
            ->database($db)
            ->table($table)
            ->select("count(*) as count")
            ->get();
        while($row = mysqli_fetch_assoc($results)){
          $data = $row;
        }
        return $data;
    }
    protected static function _countOf($db,$table,$key){
        $data = array();
        $results = $GLOBALS['db']
            ->database($db)
            ->table($table)
            ->select($key . ", count(*) as count")
            ->groupBy($key)
            ->get();
        while($row = mysqli_fetch_assoc($results)){
            $data[] = $row;
        }
        return $data;
    }
}
