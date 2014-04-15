<?php

/**
 * Contains \Drupal\payment_form\Plugin\Payment\Type\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\Payment\Type;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\HttpKernel;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The payment form field payment type.
 *
 * @PaymentType(
 *   configuration_form = "\Drupal\payment_form\Plugin\Payment\Type\PaymentFormConfigurationForm",
 *   id = "payment_form",
 *   label = @Translation("Payment form field")
 * )
 */
class PaymentForm extends PaymentTypeBase implements ContainerFactoryPluginInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The field instance storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fieldInstanceConfigStorage;

  /**
   * The HTTP kernel.
   *
   * @var \Drupal\Core\HttpKernel
   */
  protected $httpKernel;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\HttpKernel $http_kernel
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Entity\EntityStorageInterface $field_instance_config_storage
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, HttpKernel $http_kernel, EventDispatcherInterface $event_dispatcher, ModuleHandlerInterface $module_handler, EntityStorageInterface $field_instance_config_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler);
    $this->httpKernel = $http_kernel;
    $this->eventDispatcher = $event_dispatcher;
    $this->fieldInstanceConfigStorage = $field_instance_config_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_kernel'),
      $container->get('event_dispatcher'),
      $container->get('module_handler'),
      $container->get('entity.manager')->getStorage('field_instance_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'destination_url' => NULL,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resumeContext() {
    parent::resumeContext();
    $response = new RedirectResponse($this->getDestinationUrl());
    $listener = function(FilterResponseEvent $event) use ($response) {
      $event->setResponse($response);
    };
    $this->eventDispatcher->addListener(KernelEvents::RESPONSE, $listener, 999);
  }

  /**
   * {@inheritdoc}
   */
  public function paymentDescription($language_code = NULL) {
    $instance = $this->fieldInstanceConfigStorage->load($this->getFieldInstanceConfigId());

    return $instance->label();
  }

  /**
   * Sets the ID of the field instance config the payment was made for.
   *
   * @param string $field_instance_config_id
   *
   * @return static
   */
  public function setFieldInstanceConfigId($field_instance_config_id) {
    $this->getPayment()->set('payment_form_field_instance', $field_instance_config_id);

    return $this;
  }

  /**
   * Gets the ID of the field instance config the payment was made for.
   *
   * @return string
   */
  public function getFieldInstanceConfigId() {
    $values =  $this->getPayment()->get('payment_form_field_instance');

    return isset($values[0]) ? $values[0]->get('target_id')->getValue() : NULL;
  }

  /**
   * Sets the URL the user should be redirected to upon resuming the context.
   *
   * @param string $url
   *   The destination URL.
   *
   * @return $this
   */
  public function setDestinationUrl($url) {
    $this->configuration['destination_url'] = $url;

    return $this;
  }

  /**
   * Gets the URL the user should be redirected to upon resuming the context.
   *
   * @return string
   *   The destination URL.
   */
  public function getDestinationUrl() {
    return $this->configuration['destination_url'];
  }

}
