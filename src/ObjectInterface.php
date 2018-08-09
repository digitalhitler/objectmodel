<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @copyright Copyright (c) 2018 Sergey Petrenko <spetrenko@me.com>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

namespace Getrix\ObjectModel;

/**
 * Interface ObjectInterface
 */

interface ObjectInterface {
  /**
   * @param $name
   * @param $val
   * @return mixed
   */
  public function __set($name, $val);
  public function __get($name);
  public function setValue($name, $val);
  public function setValues($arr);
  public function getData();
  public function getUnsavedData();
  public function getKey();
  public function getTableName();
  public function revertUnsavedData();
  public function commit();
  public function setComputedValue(string $name, $val): void;
  public function setSchema($arr);
  public function handleFieldSchema(string $name, $val, bool $reverse = false);
}