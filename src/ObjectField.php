<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @copyright Copyright (c) 2018 Sergey Petrenko <spetrenko@me.com>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

namespace Getrix\ObjectModel;

class ObjectField {
  public $name;
  public $type;
  public $default = null;
  public $required = false;
  public $value;
  public $populate = false;
  public $fn = null;

  public function __construct($name, $params = []) {
    $this->name = $name;

    if($params["default"]) {
      $this->default = $params["default"];
    }

    if($params["type"]) {
      $this->type = $params["type"];
    }

    if($params["required"]) {
      $this->required = $params["required"];
    }

    if($params["populate"]) {
      $this->populate = $params["populate"];
    }

    if($params["fn"] && is_callable($params["fn"])) {
      $this->fn = $params["fn"];
    }
  }
}