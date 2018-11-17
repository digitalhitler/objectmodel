<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @desc Field type encode/decode handler
 * @since 1.2.0
 * @package Getrix\ObjectModel
 * @copyright Copyright (c) 2018 Sergey Petrenko <me@getrix.design>
 * @author Sergey Petrenko <me@getrix.design>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

namespace Getrix\ObjectModel;

class ObjectFieldType {

  /**
   * @var array - global types registry
   */
  private static $registry = [];

  /**
   * @var string - field type name
   */
  protected $name;

  /**
   * @var callable - field type encoder/decoder
   */
  protected $fn;

  public function __construct(string $name, callable $fn) {
    $this->setName($name);
    $this->setFn($fn);
  }

  /**
   * Executes processing the value (encoding or decoding)
   * @param      $value - given value to process
   * @param bool $reverse - reverse action (e.g. decoding)
   *
   * @return mixed
   */
  public function execute($value, $reverse = false) {
    return $this->fn->call($this, $value, $reverse);
  }

  /**
   * @param string $name
   *
   * @return ObjectFieldType
   */
  public function setName(string $name): ObjectFieldType {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @param callable $fn
   *
   * @return ObjectFieldType
   */
  public function setFn(callable $fn): ObjectFieldType {
    $this->fn = $fn;
    return $this;
  }

  /**
   * @return callable
   */
  public function getFn(): callable {
    return $this->fn;
  }

  /**
   * @param ObjectFieldType $type
   *
   * @throws ObjectFieldTypeException
   */
  public static function register(ObjectFieldType $type): void {
    if(self::hasType($type->getName())) {
      throw new ObjectFieldTypeException("Failed to register ObjectFieldType {$type->getName()} because field type with same name are already exists.");
    }

    self::$registry[$type->getName()] = $type;
  }

  /**
   * @param string $name
   *
   * @return bool|mixed
   */
  public static function getType(string $name) {
    if(self::hasType($name)) {
      return self::$registry[$name];
    }

    return false;
  }

  /**
   * Checks for existance of given type in types registry
   * @param string $name
   *
   * @return bool
   */
  public static function hasType(string $name): bool {
    return isset(self::$registry[$name]) && \is_object(self::$registry[$name]);
  }

  /**
   * Executes encoding/decoding the value with the given type name.
   *
   * @param string $name
   * @param        $value
   * @param bool   $reverse
   *
   * @return mixed
   * @throws ObjectFieldTypeException
   */
  public static function executeWithType(string $name, $value, $reverse = false) {
    if(false === self::hasType($name)) {
      throw new ObjectFieldTypeException("Failed to execute type encoding/decoding because there are no ${name} type in registery.");
    }

    return self::getType($name)->execute($value, $reverse);
  }
}