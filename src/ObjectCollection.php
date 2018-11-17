<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @desc ObjectCollection is used to provide useful interface to work
 *         with collections and it`s entries.
 * @since 1.0.0
 * @package Getrix\ObjectModel
 * @copyright Copyright (c) 2018 Sergey Petrenko <me@getrix.design>
 * @author Sergey Petrenko <me@getrix.design>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

namespace Getrix\ObjectModel;

use Getrix\ObjectModel;

/**
 * Class ObjectCollection
 * Used to represent set of ObjectModel instances and provides
 * standard iterable methods for collection.
 *
 * @package Getrix\ObjectModel
 */
class ObjectCollection implements \Countable, \Iterator {
  protected $items = [];
  protected $cursor = 0;
  protected $collection = null;

  /**
   * Magic method wrapper for getting entry by its index.
   * 
   * @param $index index value
   *
   * @return mixed|null
   */
  public function __get($index) {
    return $this->get($index);
  }

  /**
   * Counts total of entries in collection.
   *
   * @return int
   */
  public function count(): int {
    return sizeof($this->items);
  }

  /**
   * Alias for count() method.
   *
   * @return int size of collection
   */
  public function length(): int {
    return $this->count();
  }

  /**
   * Returns one entry from collection by it`s index or null
   * when entry is not exists.
   *
   * @param $index
   *
   * @return mixed|null
   */
  public function get($index) {
    return ($this->valid($index) ? $this->items[$index] : null);
  }


  /**
   * Returns all collection entries.
   * @return array
   */
  public function getAll() {
    return $this->items;
  }

  /**
   * Converts all entries to array and returns it.
   *  
   * @param bool $withComputed include computed values or not
   * @return array
   */
  public function getAllAsArray(bool $withComputed = false): array {
    $return = [];
    if($this->count() > 0) {
      foreach($this->getAll() as $item) {
        if(!is_object($item)
            || !is_subclass_of($item, "Getrix\\ObjectModel")) {
          continue;
        }
        
        $i = $item->toArray($withComputed);
        
        if(!is_array($i)) {
          continue;
        }
        
        // Clean up internal values if present:
        if(isset($i["tableName"])) {
            unset($i["tableName"]);
        }
        
        if(isset($i["keyField"])) {
            unset($i["keyField"]);
        }
        
        $return[] = $i;
      }
    }
    return $return;
  }

  /**
   * Alias for getAllAsArray.
   * 
   * @param bool $withComputed
   * @return array
   */
  public function toArray(bool $withComputed = false): array {
    return $this->getAllAsArray($withComputed);
  }

  /**
   * Returns next entry from collection using cursor.
   * 
   * {@inheritDoc}
   * @see Iterator::next()
   */
  public function next(bool $autoRewind = false) {
    
    if($this->cursor === $this->count()) {
      if($autoRewind === true) {
        $this->rewind();
      } else {
        return null;
      }
    }
        
    $return = $this->get($this->cursor);
    
    $this->cursor++;
    
    return $return;
  }

  /**
   * Adds new entry to the end of collection.
   * 
   * @param ObjectModel $entry
   */
  public function push(ObjectModel $entry): void {
    array_push($this->items, $entry);
  }

  /**
   * Sets collection name.
   * 
   * @param string $collection
   */
  public function setCollection(string $collection) {
    $this->collection = $collection;
  }

  /**
   * Returns the value of item under collection`s cursor.
   * 
   * @see Iterator::current()
   */
  public function current()
  {
    return $this->get($this->cursor);
  }

  /**
   * Returns the key of item under collection`s cursor.
   *  
   * @see Iterator::key()
   * @return int|null item index or null if failure
   */
  public function key(): mixed
  {
    return $this->cursor;
  }

  /**
   * Checks if current position is valid.
   * 
   * @return boolean The return value will be casted to boolean and then evaluated.
   * Returns true on success or false on failure.
   */
  public function valid(): bool {
    return isset($this->items[$this->cursor]);
  }

  /**
   * Rewind the Iterator to the first element.
   * 
   * @return void Any returned value is ignored.
   */
  public function rewind()
  {
    $this->cursor = 0;
  }

  /**
   * Extract only one field values to separate flat array or two fields as associative array.
   * @param string      $valueField - field used as entries values
   * @param string|null $keyField - field used as entries keys
   * @param bool        $includeNull - include null values or not (default is false)
   *
   * @return array - extracted data
   */
  public function extractField(string $valueField, string $keyField = null, bool $includeNull = false): array {
    $result = [];

    $this->rewind();

    // Loop head:
    do {
      $item = $this->next();

      if($item) {
        $val = null;
        $key = null;

        // Extract:
        $val = $item->get($valueField);
        if($keyField !== null) {
          $key = $item->get($keyField);
        }

        // Append to result:
        if($val !== null || $key !== null) {
          if($keyField !== null && $key !== null) {
            $result[$key] = $val ?? null;
          } else {
            if(($val === null && $includeNull === true) || $val !== null) {
              array_push($result, $val);
            }
          }
        }
      }
    // Iterating collection`s items as loop thah became finite only on last child
    } while($item !== null);

    // Loop end

    return $result;
  }

  /**
   * Creates collection from an plain array with set of ObjectModel.
   *
   * @param array $arr plain flat array with ObjectModel entries.
   * @return ObjectCollection new collection
   * @throws ObjectModelException
   */
  public static function fromArray(array $arr): ObjectCollection {
      
    $className = get_called_class();

    $result = new $className();

    // If not instance of self:
    if(false === $result instanceof ObjectCollection
        
        // And not an child of self:
        && false === is_subclass_of($result, "Getrix\\ObjectModel\\ObjectCollection")) {
            
      // Then throw an exception
      throw new ObjectModelException("fromArray error: created object is not " .
        "an child of ObjectCollection ({$className})");
    }

    
    if(sizeof($arr) > 0) {
      foreach ($arr as $index => $item) {
        if(false === is_subclass_of($item, "Getrix\\ObjectModel")) {
          $dataType = (is_object($item) 
            ? "Object: ".get_class($item)
            : gettype($item)
          );
          throw new ObjectModelException("fromArray error: array item with index #{$index} "
              ."is not an child of ObjectModel ({$dataType})");
        }
        
        $result->push($item);
      }
    }

    return $result;
  }

  /**
   * Creates collection from MongoDB-based structure.
   * 
   * @param string $collection
   * @param unknown $dbConn
   * @throws ObjectModelException
   * @return unknown
   */
  public static function fromStructure(string $collection, $dbConn = null) {

    if($dbConn === null) {
        //$dbConn = Flight::mongo()->getConn();
        throw new ObjectModelException("fromStructure failed: no dbConn provided.");
    }

    $c = $dbConn->$collection;
    $className = get_called_class();
    $result = new $className();
    $result->setCollection($c);
    return $result;

  }

}
