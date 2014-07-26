<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationDeleteFormUnitTest.
 */

namespace Drupal\payment\Tests\Entity\PaymentMethodConfiguration {

use Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationDeleteForm;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationDeleteForm
 *
 * @group Payment
 */
class PaymentMethodConfigurationDeleteFormUnitTest extends UnitTestCase {

  /**
   * The payment.
   *
   * @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The form under test.
   *
   * @var \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationDeleteForm
   */
  protected $form;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
    $this->stringTranslation->expects($this->any())
      ->method('translate')
      ->will($this->returnArgument(0));

    $this->form = new PaymentMethodConfigurationDeleteForm($this->stringTranslation);
    $this->form->setEntity($this->payment);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $container->expects($this->once())
      ->method('get')
      ->with('string_translation')
      ->will($this->returnValue($this->stringTranslation));

    $form = PaymentMethodConfigurationDeleteForm::create($container);
    $this->assertInstanceOf('\Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationDeleteForm', $form);
  }

  /**
   * @covers ::getQuestion
   */
  function testGetQuestion() {
    $label = $this->randomName();
    $string = 'Do you really want to delete %label?';

    $this->payment->expects($this->once())
      ->method('label')
      ->will($this->returnValue($label));

    $this->stringTranslation->expects($this->once())
      ->method('translate')
      ->with($string, array(
        '%label' => $label,
      ));

    $this->assertSame($string, $this->form->getQuestion());
  }

  /**
   * @covers ::getConfirmText
   */
  function testGetConfirmText() {
    $string = 'Delete';

    $this->stringTranslation->expects($this->once())
      ->method('translate')
      ->with($string);

    $this->assertSame($string, $this->form->getConfirmText());
  }

  /**
   * @covers ::getCancelUrl
   */
  function testGetCancelUrl() {
    $url = $this->form->getCancelUrl();
    $this->assertInstanceOf('\Drupal\Core\Url', $url);
    $this->assertSame('payment.payment_method_configuration.list', $url->getRouteName());
  }

  /**
   * @covers ::submit
   */
  function testSubmit() {
    $this->payment->expects($this->once())
      ->method('delete');

    $form = array();
    $form_state = array();

    $this->form->submit($form, $form_state);
    $this->assertArrayHasKey('redirect_route', $form_state);
    /** @var \Drupal\Core\Url $url */
    $url = $form_state['redirect_route'];
    $this->assertInstanceOf('\Drupal\Core\Url', $url);
    $this->assertSame('payment.payment_method_configuration.list', $url->getRouteName());
  }

}

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}

}
