<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @desc Exception that library used to throw. 
 * @since 1.1.0
 * @package Getrix\ObjectModel
 * @copyright Copyright (c) 2018 Sergey Petrenko <me@getrix.design>
 * @author Sergey Petrenko <me@getrix.design>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

namespace Getrix\ObjectModel;

/**
 * 
 * @author 
 *
 */
class ObjectModelException extends \Exception {
  public function __construct(string $message = "", int $code = 0, \Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}