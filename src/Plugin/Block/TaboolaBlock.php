<?php

namespace Drupal\taboola\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Taboola block.
 *
 * @Block(
 *   id = "taboola_block",
 *   admin_label = @Translation("Taboola"),
 * )
 */
class TaboolaBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor for TaboolaBlock block class.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $configuration = $this->getConfiguration();

    $form['mode'] = [
      '#type' => 'textfield',
      '#title' => t('Mode'),
      '#required' => TRUE,
      '#default_value' => !empty($configuration['mode']) ? $configuration['mode'] : '',
    ];

    $form['placement'] = [
      '#type' => 'textfield',
      '#title' => t('Placement'),
      '#required' => TRUE,
      '#default_value' => !empty($configuration['placement']) ? $configuration['placement'] : '',
    ];

    $form['target_type'] = [
      '#type' => 'textfield',
      '#title' => t('Target Type'),
      '#required' => TRUE,
      '#default_value' => !empty($configuration['target_type']) ? $configuration['target_type'] : '',
    ];

    $form['container'] = [
      '#type' => 'textfield',
      '#title' => t('Container'),
      '#required' => TRUE,
      '#default_value' => !empty($configuration['container']) ? $configuration['container'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('mode', $form_state->getValue('mode'));
    $this->setConfigurationValue('placement', $form_state->getValue('placement'));
    $this->setConfigurationValue('target_type', $form_state->getValue('target_type'));
    $this->setConfigurationValue('container', $form_state->getValue('container'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $service_url = $this->configFactory->get('taboola.settings')->get('service_url');
    $lazy_load = $this->configFactory->get('taboola.settings')->get('lazy_load');
    if (empty($service_url)) {
      return NULL;
    }

    $block_configuration = $this->getConfiguration();
    return [
      '#theme' => 'taboola',
      '#container_id' => $block_configuration['container'],
      '#attached' => [
        'library' => ['taboola/taboola'],
        'drupalSettings' => [
          'taboola' => [
            'service_url' => $service_url,
            'lazy_load' => $lazy_load,
          ],
        ],
      ],
    ];
  }

}
