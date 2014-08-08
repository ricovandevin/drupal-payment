<?php

/**
 * @file
 * Contains
 * \Drupal\payment_reference\Tests\Plugin\Payment\Type\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Tests\Plugin\Payment\Type;

use Drupal\payment_reference\Plugin\Payment\Type\PaymentReference;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @coversDefaultClass \Drupal\payment_reference\Plugin\Payment\Type\PaymentReference
 *
 * @group Payment Reference Field
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * The event dispatcher used for testing.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $eventDispatcher;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The payment used for testing.
   *
   * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $payment;

  /**
   * The payment type plugin under test.
   *
   * @var \Drupal\payment_reference\Plugin\Payment\Type\PaymentReference
   */
  protected $paymentType;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  protected function setUp() {
    $this->eventDispatcher = $this->getMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->urlGenerator = $this->getMock('\Drupal\Core\Routing\UrlGeneratorInterface');
    $this->urlGenerator->expects($this->any())
      ->method('generateFromRoute')
      ->will($this->returnValue('http://example.com'));

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->paymentType = new PaymentReference(array(), 'payment_reference', array(), $this->eventDispatcher, $this->urlGenerator, $this->entityManager, $this->stringTranslation);

    $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $this->paymentType->setPayment($this->payment);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager),
      array('event_dispatcher', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->eventDispatcher),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
      array('url_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->urlGenerator),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = array();
    $plugin_definition = array();
    $plugin_id = $this->randomMachineName();
    $plugin = PaymentReference::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment_reference\Plugin\Payment\Type\PaymentReference', $plugin);
  }

  /**
   * @covers ::defaultConfiguration
   */
  public function testDefaultConfiguration() {
    $this->assertInternalType('array', $this->paymentType->defaultConfiguration());
  }

  /**
   * @covers ::setEntityTypeId
   * @covers ::getEntityTypeId
   */
  public function testGetEntityTypeId() {
    $id = $this->randomMachineName();
    $this->assertSame($this->paymentType, $this->paymentType->setEntityTypeId($id));
    $this->assertSame($id, $this->paymentType->getEntityTypeId());
  }

  /**
   * @covers ::setBundle
   * @covers ::getBundle
   */
  public function testGetBundle() {
    $bundle = $this->randomMachineName();
    $this->assertSame($this->paymentType, $this->paymentType->setBundle($bundle));
    $this->assertSame($bundle, $this->paymentType->getBundle());
  }

  /**
   * @covers ::setFieldName
   * @covers ::getFieldName
   */
  public function testGetFieldName() {
    $name = $this->randomMachineName();
    $this->assertSame($this->paymentType, $this->paymentType->setFieldName($name));
    $this->assertSame($name, $this->paymentType->getFieldName());
  }

  /**
   * @covers ::getFieldId
   *
   * @depends testGetEntityTypeId
   * @depends testGetBundle
   * @depends testGetFieldName
   */
  public function testGetFieldId() {
    $entity_type_id = $this->randomMachineName();
    $bundle = $this->randomMachineName();
    $field_name = $this->randomMachineName();

    $this->paymentType->setEntityTypeId($entity_type_id);
    $this->paymentType->setBundle($bundle);
    $this->paymentType->setFieldName($field_name);

    $this->assertSame("$entity_type_id.$bundle.$field_name", $this->paymentType->getFieldId());
  }

  /**
   * @covers ::paymentDescription
   *
   * @depends testGetEntityTypeId
   * @depends testGetBundle
   * @depends testGetFieldName
   */
  public function testPaymentDescription() {
    $entity_type_id = $this->randomMachineName();
    $bundle = $this->randomMachineName();
    $field_name = $this->randomMachineName();
    $label = $this->randomMachineName();
    $field_definition = $this->getMock('\Drupal\Core\Field\FieldDefinitionInterface');
    $field_definition->expects($this->atLeastOnce())
      ->method('getLabel')
      ->will($this->returnValue($label));

    $definitions = array(
      $field_name => $field_definition,
    );

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue($definitions));

    $this->paymentType->setEntityTypeId($entity_type_id);
    $this->paymentType->setBundle($bundle);
    $this->paymentType->setFieldName($field_name);

    $this->assertSame($label, $this->paymentType->paymentDescription());
  }

  /**
   * @covers ::paymentDescription
   */
  public function testPaymentDescriptionWithNonExistingField() {
    $entity_type_id = $this->randomMachineName();
    $bundle = $this->randomMachineName();

    $this->entityManager->expects($this->atLeastOnce())
      ->method('getFieldDefinitions')
      ->with($entity_type_id, $bundle)
      ->will($this->returnValue(array()));

    $this->paymentType->setEntityTypeId($entity_type_id);
    $this->paymentType->setBundle($bundle);

    $this->assertSame('Unavailable', $this->paymentType->paymentDescription());
  }

  /**
   * @covers ::doResumeContext
   */
  public function testResumeContext() {
    $url = 'http://example.com';

    $kernel = $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface');
    $request = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
      ->disableOriginalConstructor()
      ->getMock();
    $request_type = $this->randomMachineName();
    $response = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Response')
      ->disableOriginalConstructor()
      ->getMock();
    $event = new FilterResponseEvent($kernel, $request, $request_type, $response);

    $this->eventDispatcher->expects($this->once())
      ->method('addListener')
      ->with(KernelEvents::RESPONSE, new PaymentReferenceUnitTestDoResumeContextCallableConstraint($event, $url), 999);


    $this->paymentType->resumeContext();
  }

}

/**
 * Provides a constraint for the doResumeContext() callable.
 */
class PaymentReferenceUnitTestDoResumeContextCallableConstraint extends \PHPUnit_Framework_Constraint {

  /**
   * The event to listen to.
   *
   * @var \Symfony\Component\HttpKernel\Event\FilterResponseEvent
   */
  protected $event;

  /**
   * The redirect URL.
   *
   * @var string
   */
  protected $url;

  /**
   * Constructs a new class instance.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   * @param string $url
   */
  public function __construct(FilterResponseEvent $event, $url) {
    $this->event = $event;
    $this->url = $url;
  }

  /**
   * {@inheritdoc}
   */
  public function matches($other) {
    if (is_callable($other)) {
      $other($this->event);
      $response = $this->event->getResponse();
      if ($response instanceof RedirectResponse) {
        return $response->getTargetUrl() == $this->url;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function toString() {
    return 'returns a RedirectResponse through a KernelEvents::RESPONSE event listener';
  }

}
