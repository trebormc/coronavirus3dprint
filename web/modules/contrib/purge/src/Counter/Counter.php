<?php

namespace Drupal\purge\Counter;

use Drupal\purge\Plugin\Purge\Purger\Exception\BadBehaviorException;

/**
 * Provides a numeric counter.
 */
class Counter implements CounterInterface {

  /**
   * The callback that is called on writes when not NULL.
   *
   * @var null|callable
   */
  protected $callback;

  /**
   * Whether it is possible to call ::decrement() or not.
   *
   * @var bool
   */
  protected $permissionDecrement = TRUE;

  /**
   * Whether it is possible to call ::increment() or not.
   *
   * @var bool
   */
  protected $permissionIncrement = TRUE;

  /**
   * Whether it is possible to call ::set() or not.
   *
   * @var bool
   */
  protected $permissionSet = TRUE;

  /**
   * The value of the counter.
   *
   * @var float
   */
  protected $value;

  /**
   * {@inheritdoc}
   */
  public function __construct($value = 0.0) {
    $this->set($value);
  }

  /**
   * {@inheritdoc}
   */
  public function disableDecrement() {
    $this->permissionDecrement = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function disableIncrement() {
    $this->permissionIncrement = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function disableSet() {
    $this->permissionSet = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getInteger() {
    return (int) $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function set($value) {
    if (!$this->permissionSet) {
      throw new \LogicException('No ::set() permission on this object.');
    }
    $this->setDirectly($value);
  }

  /**
   * {@inheritdoc}
   */
  public function setWriteCallback(callable $callback) {
    $this->callback = $callback;
  }

  /**
   * Overwrite the counter value (permission bypass).
   *
   * @param int|float $value
   *   The new value.
   *
   * @throws \Drupal\purge\Plugin\Purge\Purger\Exception\BadBehaviorException
   *   Thrown when $value is not a integer, float or when it is negative.
   * @throws \LogicException
   *   Thrown when the object got created without set permission.
   */
  protected function setDirectly($value) {
    if (!(is_float($value) || is_int($value))) {
      throw new BadBehaviorException('Given $value is not a integer or float.');
    }
    if (is_int($value)) {
      $value = (float) $value;
    }
    if ($value < 0.0) {
      throw new BadBehaviorException('Given $value can only be zero or positive.');
    }
    if ($value !== $this->value) {
      $this->value = $value;
      if (!is_null($this->callback)) {
        $callback = $this->callback;
        $callback($value);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function decrement($amount = 1.0) {
    if (!$this->permissionDecrement) {
      throw new \LogicException('No ::decrement() permission on this object.');
    }
    if (!(is_float($amount) || is_int($amount))) {
      throw new BadBehaviorException('Given $amount is not a integer or float.');
    }
    if (is_int($amount)) {
      $amount = (float) $amount;
    }
    if (!($amount > 0.0)) {
      throw new BadBehaviorException('Given $amount is zero or negative.');
    }
    $new = $this->value - $amount;
    if ($new < 0.0) {
      $new = 0.0;
    }
    $this->setDirectly($new);
  }

  /**
   * {@inheritdoc}
   */
  public function increment($amount = 1.0) {
    if (!$this->permissionIncrement) {
      throw new \LogicException('No ::increment() permission on this object.');
    }
    if (!(is_float($amount) || is_int($amount))) {
      throw new BadBehaviorException('Given $amount is not a integer or float.');
    }
    if (is_int($amount)) {
      $amount = (float) $amount;
    }
    if (!($amount > 0.0)) {
      throw new BadBehaviorException('Given $amount is zero or negative.');
    }
    $this->setDirectly($this->value + $amount);
  }

}
