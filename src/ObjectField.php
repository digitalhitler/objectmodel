<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @copyright Copyright (c) 2018 Sergey Petrenko <spetrenko@me.com>
 * @homepage  https://github.com/digitalhitler/objectmodel
 * @license   MIT
 */

namespace Getrix\ObjectModel;

/**
 * Class ObjectField
 * This class is used to provide an object with property field name,
 * it`s value, validation and serialization rules.
 *
 * @package Getrix\ObjectModel
 */
class ObjectField {

  /**
   * @var string Name of the field
   */
  public $name;

  /**
   * @var mixed Type of the field
   */
  public $type;

  /**
   * @var mixed|null Default value
   */
  public $default = null;

  /**
   * @var bool Is required field flag
   */
  public $required = false;

  /**
   * @var bool Is population can be applied flag
   *           (see "Populating fields" in README.md)
   */
  public $populate = false;

  /**
   * @var mixed Current value
   */
  public $value;

  /**
   * @var Callable|null Validation/serialization callback function
   *                    (will be called as Closure)
   */
  public $fn = null;

  /**
   * ObjectField constructor.
   *
   * @param string $name Name of the field
   * @param array  $params Array with options
   *
   * $params options (each one is will be set as relevant object property)
   *   default
   *   type
   *   required
   *   populate
   *   fn
   */
  public function __construct(string $name, array $params = []) {
    $this->name = $name;

    if(isset($params["default"])) {
      $this->default = $params["default"];
    }

    if(isset($params["type"])) {
      $this->type = $params["type"];
    }

    if(isset($params["required"])) {
      $this->required = $params["required"];
    }

    if(isset($params["populate"])) {
      $this->populate = $params["populate"];
    }

    if(isset($params["fn"]) && is_callable($params["fn"])) {
      $this->fn = $params["fn"];
    }
  }
}