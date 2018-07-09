<?php

namespace Drupal\campaignion_csv\Exporter\WebformFormatted;

/**
 * Transform values using a mapping.
 */
class MapTransformer {

  /**
   * Create a new instance from an info-array.
   *
   * @param array $info
   *   The info array. The following keys are in use:
   *   - map: An array mapping old values to new values.
   *   - default: Value that is returned if no mapping is found for an input
   *     value.
   */
  public static function fromInfo(array $info) {
    $info += [
      'map' => [],
      'default' => NULL,
    ];
    return new static($info['map'], $info['default']);
  }

  /**
   * Create a new instance.
   *
   * @param array $map
   *   An array mapping old values to new values.
   * @param mixed $default
   *   Value that is returned if no mapping is found for an input value.
   */
  public function __construct(array $map, $default) {
    $this->map = $map;
    $this->default = $default;
  }

  /**
   * Map a specific value.
   *
   * @param mixed $value
   *   The old value.
   *
   * @return mixed
   *   The mapped value.
   */
  public function transform($value) {
    if (array_key_exists($value, $this->map)) {
      return $this->map[$value];
    }
    return $this->default;
  }

}
