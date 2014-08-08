<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference.
 */

namespace Drupal\payment_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a payment reference field widget.
 *
 * @FieldWidget(
 *   description = @Translation("Allows users to select existing unused payments, or to add a new payment on the fly."),
 *   field_types = {
 *     "payment_reference"
 *   },
 *   id = "payment_reference",
 *   label = @Translation("Payment reference"),
 *   multiple_values = "false"
 * )
 */
class PaymentReference extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new class instance.
   *
   * @param array $plugin_id
   *   The plugin_id for the widget.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct($plugin_id, array $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, AccountInterface $current_user) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('current_user'));
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['payment_id'] = array(
      '#bundle' => $items->getEntity()->bundle(),
      '#default_value' => isset($items[$delta]) ? $items[$delta]->target_id : NULL,
      '#entity_type_id' => $items->getEntity()->getEntityTypeId(),
      '#field_name' => $this->fieldDefinition->getName(),
      // The requested user account may contain a string numeric ID.
      '#owner_id' => (int) $this->currentUser->id(),
      '#payment_line_items_data' => $this->getFieldSetting('line_items_data'),
      '#payment_currency_code' => $this->getFieldSetting('currency_code'),
      '#required' => $this->fieldDefinition->isRequired(),
      '#type' => 'payment_reference',
    );

    return $element;
  }

}
