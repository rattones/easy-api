<?php
namespace core\system;

use PDO;
use PDOException;
use stdClass;

class Dao extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function generateUid() : string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
    
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
    
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
    
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
      );
    }

    public function create(stdClass $obj) : ?stdClass 
    {
        if (isset($obj->uid) and in_array('uid', $this->keys)) {
            $obj->uid= $this->generateUid();
        }

        $strFields= [];
        $strValues= [];
        foreach ($obj as $field=>$value) {
            $strFields[]= $field;
            $strValues[]= "'{$value}'";
        }


        $strFields= implode(',', $strFields);
        $strValues= implode(',', $strValues);

        $query= "insert into {$this->tableName} ( {$strFields} ) values ( {$strValues} ) ";
        $stmt= $this->execute($query);

        $arr= [];
        if ($stmt->rowCount()) {
            foreach ($this->keys as $key) {
                $arr[$key]= $obj->{$key};
            }
        }

        return $this->get($arr);
    }

    public function delete(stdClass $obj) : bool
    {
        $aux= [];
        for ($i=0; $i<count($this->keys); $i++) {
            $aux[]= " {$this->keys[$i]} = '{$obj->{$this->keys[$i]}}' ";
        }
        $condition= implode(" and ", $aux);

        $query= "delete from {$this->tableName} where {$condition} ";
        $stmt= $this->execute($query);

        return ($stmt->rowCount());
    }

    public function update(stdClass $obj, array $condition= null) : ?array 
    {
        if (!is_null($condition)) {
            $aux= [];
            foreach ($condition as $field=>$value) {
                if (strpos($field, ' ')) {
                    $aux[]= " {$field} {$value} ";
                } else {
                    $aux[]= " {$field} = {$value} ";
                }
            }
        } else {
            $aux= [];
            for ($i=0; $i<count($this->keys); $i++) {
                $aux[]= " {$this->keys[$i]} = '{$obj->{$this->keys[$i]}}' ";
            }
        }
        $condition= implode(" and ", $aux);

        $sets= [];
        foreach ($obj as $field=>$value) {
            if (!in_array($field, $this->keys)) {
                $sets[]= " {$field} = '{$value}' ";
            }
        }
        $sets= implode(",", $sets);

        $query= "update {$this->tableName} set {$sets} where {$condition} ";
        $this->execute($query);

        $cadastro= $this->search(null, $condition);

        return $cadastro;
    }
    
    public function get(array $values) : ?stdClass 
    {
        $condition= [];
        for ($i=0; $i<count($this->keys); $i++) {
            $condition[]= " {$this->keys[$i]} = '{$values[$this->keys[$i]]}' ";
        }
        $condition= implode(" and ", $condition);
        $query= "select * from {$this->tableName} where {$condition}";
        
        try {
            $stmt= $this->execute($query);
            $result= $stmt->fetchObject();
        } catch (PDOException $e) {
            throw new PDOException($e);
        }

        return ($result)? $result: null;
    }

    public function search(string $fields= null,
                        string $condition= null, 
                        string $order= null, 
                        int $limit= null, 
                        int $offSet= null) : array 
    {
        $query= "select ";
        $query.= !is_null($fields)? " {$fields} ": " * "." from {$this->tableName} ";
        $query.= !is_null($condition)? " where {$condition} ": "";
        $query.= !is_null($order)? " order by {$order} ": "";
        $query.= !is_null($limit)? " limit {$limit} ": (!is_null($offSet)? " offset {$offSet} ": "");

        $stmt= $this->execute($query);
        $result= $stmt->fetchAll(PDO::FETCH_OBJ);

        return ($result)? $result: [];

    }
}