<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @copyright Copyright (c) 2018 Sergey Petrenko <spetrenko@me.com>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

namespace Getrix;

use FluentPDO;
use Getrix\ObjectModel\ObjectField;
use Getrix\ObjectModel\ObjectInterface;

require_once "ObjectModelException.php";
require_once "ObjectField.php";
require_once "ObjectInterface.php";
require_once "ObjectCollection.php";

/**
 * Class ObjectModel
 * @package GRS
 *
 * Realizes engine for accessing and modifying database data
 * as like plain PHP objects
 */
class ObjectModel implements ObjectInterface {

  /**
   * @var string table name
   */
  protected static $table = null;

  /**
   * @var string primary key field name
   */
  protected static $primaryKey = 'id';

  public static $databaseCallback = null;

  /**
   * @var mixed current entry ID
   */
  protected $key = null;

  /**
   * @var array used to store current values
   */
  protected $data = [];

  /**
   * @var array used to store computed values
   */
  protected $computed = [];

  /**
   * @var array used to store modified but not saved values
   */
  protected $changedData = [];

  /**
   * @var array used to store current data schema
   */
  protected $dataSchema = [];

  /**
   * @var bool indicates is current entry is new or exists in db
   */
  protected $new = false;

  /**
   * @var mixed stores primary key value for just inserted entry
   */
  protected $insertId = null;

  /**
   * ObjectModel constructor.
   * @param string $tableName   table name in database
   * @param string $keyField    primary key field name
   * @param array|null $schema  schema rules
   * @param array|null $row     current row to be presented like ObjectModel instance
   * @param bool $isNew         is this row is new (unsaved in db) or not
   */
  public function __construct(string $tableName, string $keyField = 'ID', array $schema = null, array $row = null, bool $isNew = false) {

    $this->tableName = $tableName;
    $this->keyField = $keyField;
    // Same for static methods:
    $className = get_called_class();

    if($className) {
      $className::$table = $tableName;
      $className::$primaryKey = $keyField;
    }

    if(is_array($schema) && sizeof($schema) > 0) {
      $this->setSchema($schema);
    }

    if(
      is_array($row) &&
      sizeof($row) > 0) {
      foreach($row as $name => $val) {
        $this->{$name} = $val;
        if($name === $keyField) {
          $this->key = $val;
        }
      }
      if($isNew === true) {
        $this->new = true;
      }
    } else {
      $this->new = true;
    }
  }

  /**
   * Sets the field value
   *
   * @param $name         field name
   * @param $val          field value
   * @return mixed|void
   * @throws \Exception
   */
  public function __set($name, $val) {

    if($name === "tableName" || $name === "keyField") {
      return true;
    }

    if(!isset($this->data[$name]) && $this->new === false) {
      $val = $this->handleFieldSchema($name, $val);

      $this->data[$name] = $val;
      if($name === $this->keyField) {
        $this->key = $val;
      }
    } else {
      $val = $this->handleFieldSchema($name, $val, true);

      $this->changedData[$name] = $val;

      // throw new \Exception("ObjectModel::__set: field values is read-only. Use setValue method");
    }
  }

  /**
   * Gets the field value (magic method for get)
   * @param $name
   * @return mixed|null
   */
  public function __get($name) {
    return $this->get($name);
  }

  /**
   * Gets the field value
   * @param $name
   * @return mixed|null
   */
  public function get($name) {
    if(isset($this->data[$name])) {
      return $this->data[$name];
    } elseif(isset($this->computed[$name])) {
      return $this->computed[$name];
    } else return null;
  }

  /**
   * Magic serialization fields collector method
   * @return array
   */
  public function __sleep() {
    $result = array_keys($this->data);
    return $result;
  }

  /**
   * Magic isset method
   * @param string $name    property name
   * @return bool           yes or no
   */
  public function __isset(string $name) {
    return (isset($this->data[$name]));
  }

  /**
   * Magic unset method
   * @param string $name
   * @throws \Exception
   */
  public function __unset(string $name) {
    $this->data[$name] = $this->handleFieldSchema($name, "", true, true);
  }

  /**
   * Changes the value of field
   * @param $name field name
   * @param $val field value
   */
  public function setValue($name, $val) {
//    if (array_key_exists($name, $this->data)) {
      $this->changedData[$name] = $val;
//    }
  }

  /**
   * Changes the values of the multiple fields (recursively calls setValue for each of them)
   * @param $arr array of field => value entries
   */
  public function setValues($arr) {
    if(is_array($arr) && sizeof($arr) > 0) {
      foreach ($arr as $name => $val) {
        $this->setValue($name, $val);
      }
    }
  }

  /**
   * Returns current data array
   * @return array
   */
  public function getData(): array
  {
    return $this->data;
  }

  /**
   * Returns current changed data array
   * @return array
   */
  public function getUnsavedData(): array
  {
    return $this->changedData;
  }

  /**
   * Returns current entry primary key value
   * @return mixed primary key value
   */
  public function getKey()
  {
    return $this->key;
  }

  /**
   * Returns current entry table name used to store in db
   * @return mixed|null table name
   */
  public function getTableName()
  {
    return $this->tableName;
  }

  /**
   * Cancels all unsaved changes
   */
  public function revertUnsavedData()
  {
    $this->changedData = [];
  }

  /**
   * Performs saving query (insert or update) to database
   * @return mixed updated class instance or false
   * @throws \Exception   in case of error
   */
  public function commit()
  {
    try {
      if (sizeof($this->changedData) > 0) {

        foreach ($this->changedData as $field => $val) {
          $val = $this->handleFieldSchema($field, $val, true);
          //old : $parts[] = "`${field}` = '".addslashes($val)."'";
          $parts[$field] = $val;
        }

        if (sizeof($parts) > 0) {

          $className = get_called_class();

          if ($this->key === null) {
            $query = self::getQueryBuilder()
              ->insertInto($className::$table)
              ->values($parts);
            //   d($parts);
          } else {
            $query = self::getQueryBuilder()
              ->update($className::$table)
              ->set($parts)
              ->where($className::$primaryKey, $this->key)
              ->limit(1);
          }

          $result = $query->execute();

          if ($this->new === true) {
            $reqId = $result;
          } else {
            $reqId = $this->key;
          }

          $data = $this->getById($reqId);

          return $data->next();
        }
      }
    } catch(\Exception $e) {
      throw $e;
    }
    return false;
  }

  /**
   * Saves computed value to object
   *
   * @param string $name   name of field
   * @param        $val    value
   */
  public function setComputedValue(string $name, $val): void
  {
    $this->computed[$name] = $val;
  }

  /**
   * Sets object schema
   * @param $arr  schema describer
   */
  public function setSchema($arr) {
   if(is_array($arr) && sizeof($arr) > 0) {
     foreach($arr as $name => $params) {
       $this->dataSchema[$name] = new ObjectField($name, $params);
     }
   }
  }

  /**
   * Transforms field value according schema options
   * @param string $name      field name
   * @param $val              (raw) field value
   * @param bool $reverse     reverse handling flag
   * @param bool $setDefault  set value to default flag
   * @return \DateTime|false|int|string
   * @throws \Exception
   */
  public function handleFieldSchema(string $name, $val, bool $reverse = false, bool $setDefault = false) {
    if(isset($this->dataSchema[$name])) {
      $fieldSchema = $this->dataSchema[$name];

      if($setDefault === true) {
        if(!isset($fieldSchema->default)) {
          throw new \Exception("Field {$name} has no defaut value.");
        }
        $val = $fieldSchema->default;
      }

      if($fieldSchema->fn && is_callable($fieldSchema->fn)) {
        $val = $fieldSchema->fn->call($fieldSchema, $val, $reverse);
        return $val;
      }

      if($fieldSchema->type) {
        switch($fieldSchema->type) {
          case "integer":
            $val = intval($val);
            break;
          case "datetime":
            if($reverse) {
              //die(get_class($val));
              if(get_class($val) === 'DateTime') {
                $val = $val->format('Y-m-d H:i:s');
              } else {
                $val = date('Y-m-d H:i:s', $val);
              }
            } else {
              if($val !== null) {
                $val = new \DateTime($val, new \DateTimeZone('UTC'));
              }
            }
            break;
          case "timestamp":
            if($reverse) {
              $val = date('Y-m-d H:i:s', $val);
            } else {
              if($val !== null) {
                $val = strtotime($val);
              }
            }
            break;
          case "money":
            if($reverse) {
              $val = floatval(round($val, 2));
            } else {
              $val = floatval(round($val, 2));
            }
            break;
          case "set":
            if($reverse) {
              if(is_array($val)) {
                $val = implode(",", $val);
              }
            } else {
              $val = explode(",", $val);
            }
            break;
          case "json":
            if($reverse) {
              $val = json_encode($val);
            } else {
              $val = json_decode($val, true);
            }
            break;
          case "enum":
            $vals = ($fieldSchema["enum"] ?? false);
            if(sizeof($vals) === 0 || $vals === false) {
              throw new \Exception("Wrong enum values for {$name} schema.");
            }
            if(!in_array($val, $vals)) {
              $val = null;
            }
            break;
          case "boolean":
            if($reverse === true) {
              $val = ($val === true || $val > 0 || $val == "true" ? 1
                      : 0);
            } else {
              $val = (in_array($val, ["Y", "1", 1, "true", "TRUE", "YES", "yes"],true) ? true
                  : ($fieldSchema->default ? $fieldSchema->default : false));
            }
        }
      }
    }
    return $val;
  }

  /**
   * Transform schema valid value to 
   * @param $name
   * @param $val
   *
   * @return \DateTime|false|int|string
   * @throws \Exception
   */
  public function normalizeField($name, $val) {
    return $this->handleFieldSchema($name, $val, true);
  }

  /**
   * Extract data fields from object to an array.
   *
   * @param bool $withComputed include computed fields or not
   * @param bool $serializeCollections serialize ObjectCollections stored
   *                                   as values (or computed values) or not
   *
   * @return array  conversion result
   */
  public function toArray(bool $withComputed = false, bool $serializeCollections = true): array {
    $result = $this->data;
    if($withComputed === true && sizeof($this->computed) > 0) {
      foreach($this->computed as $name => $val) {
        $result[$name] = $val;
      }
    }
    if($serializeCollections === true) {

    }
    return $result;
  }

  /**
   * @return mixed PDO instance
   */
  public static function getDB() {
    if(!empty(self::$databaseCallback) && is_callable(self::$databaseCallback)) {
      return self::$databaseCallback();
    } else {
      throw new ObjectModelException("databaseCallback has a wrong value");
    }
  }

  public static function createOne(array $fields) {
    $className = get_called_class();
    if(sizeof($fields) > 0) {
      $newObject = new $className();
      foreach($fields as $key => $val) {
        $newObject->setValue($key, $val);
      }
      return $newObject->commit();
    } else return null;
  }

  public static function getQueryBuilder(string $table = '', $convertTypes = true) {
    $qb = new FluentPDO(self::getDB());
    $qb->convertTypes = $convertTypes;
    if(strlen($table) > 0) {
      $qb = $qb->from($table);
    }
    return $qb;
  }

  public static function lastInsertId() {
    $result = self::getDB()->lastInsertId();
    return $result;
  }

  public static function query($q, $limit = false) {

    $className = get_called_class();

    $query = "
        SELECT * FROM ".$className::$table."
        WHERE ".$q."
    ";

    if($limit !== false) {
      $query.=" LIMIT ";
      if(is_array($limit) && sizeof($limit) === 2) {
        $query.=intval($limit[0]).", ".intval($limit[1]);
      } else {
        $query.=intval($limit);
      }
    }


    try {
      $rows = self::getDB()->query($query)->fetchAll();
    } catch(Exception $e) {
      echo "<h1>Failed to execute this:</h1>";
      echo "<pre>".$query."</pre>";
    }

    $return = [];

    if($rows) {
      foreach($rows as $row) {
        $return[] = new $className($row);
      }
    }

    $return = ObjectCollection::fromArray($return);

    return $return;
  }

  public static function find($filters = [], $options = []) {
    if(sizeof($filters) > 0) {
      $rows = [];
      foreach($filters as $field => $val) {
        $row = "";
        if(is_array($val)) {
          $operator = $val[0];
          if (!in_array($operator, [
              "=", ">", "<", "<=", ">=", "!=", "IN"
          ])) {
            throw new \Exception("Unknown operator for " . $field);
          }

          $value = $val[1];
        } else {
          $operator = "=";
          $value = $val;
        }

        if($operator === "IN") {
          if(is_array($value)) {
            $value = implode(",", $value);
          }
          $row = "`{$field}` IN(".$value.")";
        } else {
          if(is_string($value)) {
            $value = "'{$value}'";
          }
          $row = "`{$field}` {$operator} ".$value;
        }

        $rows[] = $row;
      }
    }
  }

  public function getOwnedBy($user, $fieldName = "UserID") {
    $user = intval($user);
    if($user <= 0) {
      throw new \Exception("Wrong user for getOwnedBy: ".$user);
    }

    $className = get_called_class();

    return $className::query("`".$fieldName."` = ".$user);
  }

  public static function getByOwner(int $user, int $limit = 0, string $orderBy = "ID ASC") {
    return self::simpleQuery(
      ["UserID" => $user ],
      $limit,
      [ "orderBy" => $orderBy ]
    );
  }

  public static function getById(int $id, $orderBy = null) {
    return self::simpleQuery(["ID" => $id ], 1, [
      "orderBy" => $orderBy
    ]);
  }

  public static function getOne(array $criterias = []) {
    $className = get_called_class();

    $arr = self::getQueryBuilder($className::$table);
    if (sizeof($criterias) > 0) {
      $arr->where($criterias);
    }
    $row = $arr->limit(1)->fetch();

    if (is_array($row) && sizeof($row) > 0) {
      return new $className($row);
    }
  }

  public static function simpleQuery(array $criterias, int $limit = 0, array $options = []) {
    $className = get_called_class();

    $q = self::getQueryBuilder()
      ->from($className::$table)
      ->where($criterias);
    if($limit > 0) {
      $q->limit($limit);
    }

    if(!empty($options["orderBy"]) && $options["orderBy"] !== null ) {
      $q->orderBy($options["orderBy"]);
    }

    $result = $q->fetchAll();

    $items = [];
    if(sizeof($result) > 0) {
      foreach($result as $row) {
        $items[] = new $className($row);
      }
    }

    return ObjectCollection::fromArray($items);

  }

  /**
   * Populates object with computed value having record from object of another type
   * 
   * Usage: 
   * $obj = new MyObject($data);
   * $obj->populate();
   * 
   * Must have an field with ["populate"] => "ObjectName"
   */
  public function populate($fields = []) {
    foreach($this->dataSchema as $field => $schema) {
      if(sizeof($fields) === 0 || in_array($field, $fields)) {
        if ($schema->populate !== false) {
        
          if(is_string($schema->populate)) {
            $fieldName = "_".$field;
            $foreignClass = $schema->populate;
          } elseif(is_array($schema->populate)) {
            $fieldName = ($schema->populate["field"]
                ? $schema->populate["field"]
                : "_".$field);
            $foreignClass = $schema->populate["model"];
            
          }
          $populateClass = "\\GRS\\Objects\\" . $foreignClass;
          $pop = $populateClass::getById($this->get($field));
          if($pop->count() > 0) {
            $val = $pop->next();
          } else {
            $val = null;
          }


          $this->setComputedValue($fieldName, $val);

        }
      }
    }
  }
}
