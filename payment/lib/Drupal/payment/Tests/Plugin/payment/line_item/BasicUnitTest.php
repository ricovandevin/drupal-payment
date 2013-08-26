<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\payment\line_item\BasicUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\payment\line_item;

use Drupal\payment\Plugin\payment\line_item\PaymentLineItemInterface;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests \Drupal\payment\Plugin\payment\status\Basic.
 */
class BasicUnitTest extends DrupalUnitTestBase {

  /**
   * The line item to test.
   *
   * @var \Drupal\payment\Plugin\payment\line_item\Basic
   */
  protected $lineItem;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('payment', 'payment_test');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\payment\line_item\Basic unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->lineItem = $this->container->get('plugin.manager.payment.line_item')->createInstance('payment_basic');
  }

  /**
   * Tests setAmount() and getAmount().
   */
  protected function testGetAmount() {
    $amount = 5.3;
    $this->assertTrue($this->lineItem->setAmount($amount) instanceof PaymentLineItemInterface);
    $this->assertIdentical($this->lineItem->getAmount(), $amount);
  }

  /**
   * Tests setQuantity() and getQuantity().
   */
  protected function testGetQuantity() {
    $quantity = 7;
    $this->assertTrue($this->lineItem->setQuantity($quantity) instanceof PaymentLineItemInterface);
    $this->assertIdentical($this->lineItem->getQuantity(), $quantity);
  }

  /**
   * Tests getTotalAmount().
   */
  protected function testGetTotalAmount() {
    $amount= 7;
    $quantity = 7;
    $this->lineItem->setAmount($amount);
    $this->lineItem->setQuantity($quantity);
    $this->assertIdentical($this->lineItem->getTotalAmount(), 49);
  }

  /**
   * Tests setName() and getName().
   */
  protected function testGetName() {
    $name = $this->randomName();
    $this->assertTrue($this->lineItem->setName($name) instanceof PaymentLineItemInterface);
    $this->assertIdentical($this->lineItem->getName(), $name);
  }

  /**
   * Tests setDescription() and getDescription().
   */
  protected function testGetDescription() {
    $description = $this->randomName();
    $this->assertTrue($this->lineItem->setDescription($description) instanceof PaymentLineItemInterface);
    $this->assertIdentical($this->lineItem->getDescription(), $description);
  }

  /**
   * Tests setPaymentId() and getPaymentId().
   */
  protected function testGetPaymentId() {
    $payment_id = mt_rand();
    $this->assertTrue($this->lineItem->setPaymentId($payment_id) instanceof PaymentLineItemInterface);
    $this->assertEqual($this->lineItem->getPaymentId(), $payment_id);
  }
}