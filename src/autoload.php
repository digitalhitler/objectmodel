<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @copyright Copyright (c) 2018 Sergey Petrenko <spetrenko@me.com>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

define("GETRIX_OBJECTMODEL_PATH", dirname(__FILE__));

require_once GETRIX_OBJECTMODEL_PATH.DIRECTORY_SEPARATOR."exceptions".DIRECTORY_SEPARATOR."ObjectModelException.php";
require_once GETRIX_OBJECTMODEL_PATH.DIRECTORY_SEPARATOR."exceptions".DIRECTORY_SEPARATOR."ObjectFieldTypeException.php";
require_once GETRIX_OBJECTMODEL_PATH.DIRECTORY_SEPARATOR."ObjectCollection.php";
require_once GETRIX_OBJECTMODEL_PATH.DIRECTORY_SEPARATOR."ObjectField.php";
require_once GETRIX_OBJECTMODEL_PATH.DIRECTORY_SEPARATOR."ObjectFieldType.php";
require_once GETRIX_OBJECTMODEL_PATH.DIRECTORY_SEPARATOR."ObjectModel.php";