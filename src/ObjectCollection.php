<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @copyright Copyright (c) 2018 Sergey Petrenko <spetrenko@me.com>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

namespace Getrix\ObjectModel;

class ObjectCollection implements \Countable, \Iterator {
  protected $items = [];
  protected $cursor = 0;
  protected $collection = null;

  public function __get($index) {
    return $this->get($index);
  }

  public function count() {
    return sizeof($this->items);
  }

  public function length() {
    return $this->count();
  }

  public function get($index) {
    return $this->valid($index) ? $this->items[$index] : null;
  }

  public function getAll() {
    return $this->items;
  }

  public function getAllAsArray($withComputed = false): array {
    $return = [];
    if(sizeof($this->items) > 0) {
      foreach($this->items as $item) {
        $i = $item->toArray($withComputed);
        unset($i["tableName"]);
        unset($i["keyField"]);
        $return[] = $i;
      }
    }
    return $return;
  }

  public function toArray(bool $withComputed = false): array {
    return $this->getAllAsArray($withComputed);
  }

  public function next() {
    $return = $this->get($this->cursor);
    if($this->cursor === $this->count()) {
      $this->cursor = 0;
    } else {
      $this->cursor++;
    }
    return $return;
  }

  public function push($entry) {
    array_push($this->items, $entry);
  }

  public function setCollection(string $collection) {
    $this->collection = $collection;
  }

  public static function fromArray($arr) {
    $className = get_called_class();

    $result = new $className();

    foreach($arr as $index => $item) {
      $result->push($item);
    }
    return $result;
  }

  public static function fromStructure(string $collection) {

    $db = Flight::mongo()->getConn();

    $c = $db->$collection;
    $className = get_called_class();
    var_dump($c);
    var_dump($className);
    $result = new $className();
    $result->setCollection($c);
    return $result;

  }

  /**
   * Return the current element
   * @link http://php.net/manual/en/iterator.current.php
   * @return mixed Can return any type.
   * @since 5.0.0
   */
  public function current()
  {
    return $this->get($this->cursor);
  }

  /**
   * Return the key of the current element
   * @link http://php.net/manual/en/iterator.key.php
   * @return mixed scalar on success, or null on failure.
   * @since 5.0.0
   */
  public function key()
  {
    return $this->cursor;
  }

  /**
   * Checks if current position is valid
   * @link http://php.net/manual/en/iterator.valid.php
   * @return boolean The return value will be casted to boolean and then evaluated.
   * Returns true on success or false on failure.
   * @since 5.0.0
   */
  public function valid(): bool {
    return isset($this->items[$this->cursor]);
  }

  /**
   * Rewind the Iterator to the first element
   * @link http://php.net/manual/en/iterator.rewind.php
   * @return void Any returned value is ignored.
   * @since 5.0.0
   */
  public function rewind()
  {
    $this->cursor = 0;
  }
}
