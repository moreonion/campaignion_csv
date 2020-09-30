<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Wrapper for lazy data recursion to lazily load special attributes.
 */
class NestedData {

  /**
   * The data object that is being wrapped.
   *
   * @var mixed
   */
  protected $data;

  /**
   * Array of lazy loading callables keyed by the name of the property.
   *
   * @var callable[]
   */
  protected $overloaded;

  /**
   * Create a new instance by passing the data object that should be wrapped.
   *
   * @param mixed $data
   *   The data that should be wrapped.
   * @param array $overloaded
   *   An array of lazy loading callables for loading special properties.
   */
  public function __construct($data, array $overloaded = []) {
    $this->data = $data;
    $this->overloaded = $overloaded;
    foreach (get_class_methods(static::class) as $method) {
      if (substr($method, 0, 1) != '_') {
        $this->overloaded[$method] = [$this, $method];
      }
    }
  }

  /**
   * Get an (overloaded) property or from the wrapped data structure.
   */
  public function __get($key) {
    if ($loader = $this->overloaded[$key] ?? NULL) {
      return $loader($key);
    }
    if (is_array($this->data)) {
      return $this->data[$key] ?? NULL;
    }
    else {
      return $this->data->{$key} ?? NULL;
    }
  }

  /**
   * Read a value by passing the dot-separated path as string.
   */
  public function _valueByPath($path) {
    return $this->_valueByParts(explode('.', $path));
  }

  /**
   * Read a value by passing the path parts.
   */
  public function _valueByParts(array $parts) {
    $data = $this;
    while ($part = array_shift($parts)) {
      if ($data instanceof NestedData) {
        $data = $data->__get($part);
      }
      elseif (is_array($data)) {
        $data = $data[$part] ?? NULL;
      }
      else {
        $data = $data->{$part} ?? NULL;
      }
    }
    return $data;
  }

}
