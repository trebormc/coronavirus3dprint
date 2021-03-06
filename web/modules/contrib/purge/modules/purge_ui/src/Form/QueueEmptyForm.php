<?php

namespace Drupal\purge_ui\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\purge\Plugin\Purge\Queue\QueueServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Empty the queue.
 */
class QueueEmptyForm extends ConfirmFormBase {
  use CloseDialogTrait;

  /**
   * The 'purge.queue' service.
   *
   * @var \Drupal\purge\Plugin\Purge\Queue\QueueServiceInterface
   */
  protected $purgeQueue;

  /**
   * Construct a QueueClearForm object.
   *
   * @param \Drupal\purge\Plugin\Purge\Queue\QueueServiceInterface $purge_queue
   *   The purge queue service.
   */
  public function __construct(QueueServiceInterface $purge_queue) {
    $this->purgeQueue = $purge_queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('purge.queue'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'purge_ui.queue_empty_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // This is rendered as a modal dialog, so we need to set some extras.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    // Update the buttons and bind callbacks.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->getConfirmText(),
      '#ajax' => ['callback' => '::emptyQueue'],
    ];
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
      '#weight' => -10,
      '#ajax' => ['callback' => '::closeDialog'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Yes, throw everything away!');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to empty the queue?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Empty the queue.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response object.
   */
  public function emptyQueue(array &$form, FormStateInterface $form_state) {
    $this->purgeQueue->emptyQueue();
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
