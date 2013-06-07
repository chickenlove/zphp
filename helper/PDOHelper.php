<?php

namespace framework\helper;

use \PDO;

/**
 * PDO数据处理类
 *
 */
class PDOHelper {

    private $pdo;
    private $dbName;
    private $tableName;
    private $className;

    public function __construct($className, $dbName = null) {
        $this->className = $className;

        if (!empty($dbName)) {
            $this->dbName = $dbName;
        }
    }


    public function getDBName() {
        return $this->dbName;
    }


    public function setDBName($dbName) {
        $this->dbName = $dbName;
    }

    public function getTableName() {
        if (empty($this->tableName)) {
            $classRef = new \ReflectionClass($this->className);
            $this->tableName = $classRef->getConstant('TABLE_NAME');
        }

        return $this->tableName;
    }

    public function setClassName($className) {
        if($this->className != $className) {
            $this->className = $className;
            $this->tableName = null;
        }
    }

    public function getClassName() {
        return $this->className;
    }

    public function getLibName() {
        return "`{$this->getDBName()}`.`{$this->getTableName()}`";
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function setPdo($pdo) {
        $this->pdo = $pdo;
    }

    public function add($entity, $fields, $onDuplicate = null) {
        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);

        $query = "INSERT INTO {$this->getLibName()} ({$strFields}) VALUES ({$strValues})";

        if (!empty($onDuplicate)) {
            $query .= 'ON DUPLICATE KEY UPDATE ' . $onDuplicate;
        }

        $statement = $this->pdo->prepare($query);
        $params = array();

        foreach ($fields as $field) {
            $params[$field] = $entity->$field;
        }

        $statement->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function addMulti($entitys, $fields) {
        $items = array();
        $params = array();

        foreach ($entitys as $index => $entity) {
            $items[] = '(:' . implode($index . ', :', $fields) . $index . ')';

            foreach ($fields as $field) {
                $params[$field . $index] = $entity->$field;
            }
        }

        $query = "INSERT INTO {$this->getLibName()} (`" . implode('`,`', $fields) . "`) VALUES " . implode(',', $items);
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    public function replace($entity, $fields) {
        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);

        $query = "REPLACE INTO {$this->getLibName()} ({$strFields}) VALUES ({$strValues})";
        $statement = $this->pdo->prepare($query);
        $params = array();

        foreach ($fields as $field) {
            $params[$field] = $entity->$field;
        }

        $statement->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function update($fields, $params, $where, $change = false) {
        if ($change) {
            $updateFields = array_map(__CLASS__ . '::changeFieldMap', $fields);
        } else {
            $updateFields = array_map(__CLASS__ . '::updateFieldMap', $fields);
        }

        $strUpdateFields = implode(',', $updateFields);
        $query = "UPDATE {$this->getLibName()} SET {$strUpdateFields} WHERE {$where}";
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    public function fetchValue($where = '1', $params = null, $fields = '*') {
        $query = "SELECT {$fields} FROM {$this->getLibName()} WHERE {$where} limit 1";
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        return $statement->fetchColumn();
    }

    public function fetchArray($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null) {
        $query = "SELECT {$fields} FROM {$this->getLibName()} WHERE {$where}";

        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $query .= " limit {$limit}";
        }

        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        return $statement->fetchAll();
    }

    public function fetchCol($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null) {
        $results = $this->fetchArray($where, $params, $fields, $orderBy, $limit);
        return empty($results) ? array() : array_map('reset', $results);
    }

    public function fetchAll($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null) {
        $query = "SELECT {$fields} FROM {$this->getLibName()} WHERE {$where}";

        if ($orderBy) {
            $query .= " order by {$orderBy}";
        }

        if ($limit) {
            $query .= " limit {$limit}";
        }

        $statement = $this->pdo->prepare($query);

        if (!$statement->execute($params)) {
            throw new \Exception('data base error');
        }

        $statement->setFetchMode(PDO::FETCH_CLASS, $this->className);
        return $statement->fetchAll();
    }

    public function fetchEntity($where = '1', $params = null, $fields = '*', $orderBy = null) {
        $query = "SELECT {$fields} FROM {$this->getLibName()} WHERE {$where}";

        if ($orderBy) {
            $query .= " order by {$orderBy}";
        }

        $query .= " limit 1";
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        $statement->setFetchMode(PDO::FETCH_CLASS, $this->className);
        return $statement->fetch();
    }

    public function fetchCount($where = '1', $pk = "*") {
        $query = "SELECT count({$pk}) as count FROM {$this->getLibName()} WHERE {$where}";
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        $result = $statement->fetch();
        return $result["count"];
    }

    public function remove($where, $params=[]) {
        if (empty($where)) {
            return false;
        }

        $query = "DELETE FROM {$this->getLibName()} WHERE {$where}";
        $statement = $this->pdo->prepare($query);
        return $statement->execute($params);
    }

    public function flush() {
        $query = "TRUNCATE {$this->getLibName()}";
        $statement = $this->pdo->prepare($query);
        return $statement->execute();
    }

    public static function updateFieldMap($field) {
        return '`' . $field . '`=:' . $field;
    }

    public static function changeFieldMap($field) {
        return '`' . $field . '`=`' . $field . '`+:' . $field;
    }

    public function fetchBySql($sql) {
        $statement = $this->pdo->prepare($sql);
        $statement->execute();
        return $statement->fetch();
    }

}
