<?php

namespace Drupal\minfin_beleidsevaluaties\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A form for the beleidsevaluaties overview filters.
 */
class BeleidsevaluatiesFilterForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The tempstore.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $tempStore;

  /**
   * Constructs an BeleidsevaluatiesFilterForm object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $privateTempStore
   *   The tempstore.
   */
  public function __construct(Connection $connection, RequestStack $requestStack, PrivateTempStoreFactory $privateTempStore) {
    $this->connection = $connection;
    $this->request = $requestStack->getCurrentRequest();
    $this->tempStore = $privateTempStore->get('beleidsevaluaties');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_beleidsevaluaties_overview_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $values = $this->tempStore->get('values');
    $filters = $this->request->query->all();

    $query = $this->connection->select('mf_beleidsevaluatie_hoofdstuk', 'bh');
    $query->join('mf_hoofdstuk', 'h', 'bh.hoofdstuk_id = h.hoofdstuk_id');
    $query->distinct();
    $query->fields('h', ['naam']);
    $query->orderBy('naam');
    $options = $query->execute()->fetchAllKeyed(0, 0);
    $form['hoofdstuk_naam'] = [
      '#title' => $this->t('Department'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $filters['hoofdstuk_naam'] ?? ($values['hoofdstuk_naam'] ?? NULL),
      '#empty_option' => $this->t('Choose department'),
      '#attributes' => [
        'class' => ['select2'],
      ],
    ];

    $query = $this->connection->select('mf_beleidsevaluatie_artikel', 'ba');
    $query->join('mf_artikel', 'a', 'ba.artikel_id = a.artikel_id');
    $query->distinct();
    $query->fields('a', ['naam']);
    $query->orderBy('naam');
    $options = $query->execute()->fetchAllKeyed(0, 0);
    $form['artikel_naam'] = [
      '#title' => $this->t('Article'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $filters['artikel_naam'] ?? ($values['artikel_naam'] ?? NULL),
      '#empty_option' => $this->t('Choose article'),
      '#attributes' => [
        'class' => ['select2'],
        'data-disable-search' => 'true',
      ],
    ];

    $options = $this->connection->select('mf_beleidsevaluatie', 'b')
      ->distinct()
      ->fields('b', ['type'])
      ->condition('b.type', '', '!=')
      ->orderBy('type')
      ->execute()
      ->fetchAllKeyed(0, 0);
    $form['type'] = [
      '#title' => $this->t('Type research'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $filters['type'] ?? ($values['type'] ?? NULL),
      '#empty_option' => $this->t('Choose type research'),
      '#attributes' => [
        'class' => ['select2', 'no-search'],
      ],
    ];

    $form['titel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title', [], ['context' => 'minfin_beleidsevaluaties']),
      '#size' => 30,
      '#autocomplete_route_name' => 'minfin_beleidsevaluaties.autocomplete.title',
      '#default_value' => $filters['titel'] ?? ($values['titel'] ?? NULL),
      '#attributes' => [
        'placeholder' => $this->t('Search title'),
      ],
    ];

    $options = $this->connection->select('mf_beleidsevaluatie', 'b')
      ->distinct()
      ->fields('b', ['status'])
      ->condition('b.status', '', '!=')
      ->orderBy('status')
      ->execute()
      ->fetchAllKeyed(0, 0);
    $form['status'] = [
      '#title' => $this->t('Status'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $filters['status'] ?? ($values['status'] ?? NULL),
      '#empty_option' => $this->t('Choose status'),
      '#attributes' => [
        'class' => ['select2', 'no-search'],
      ],
    ];

    $options = $this->connection->select('mf_beleidsevaluatie', 'b')
      ->distinct()
      ->fields('b', ['opleverdatum'])
      ->condition('b.opleverdatum', '', '!=')
      ->orderBy('opleverdatum')
      ->execute()
      ->fetchAllKeyed(0, 0);
    $form['opleverdatum'] = [
      '#title' => 'Afronding',
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $filters['opleverdatum'] ?? ($values['opleverdatum'] ?? NULL),
      '#empty_option' => $this->t('Choose year'),
      '#attributes' => [
        'class' => ['select2'],
      ],
    ];

    // Make sure the 'Show' button is rendered before the 'Reset' button.
    $form['selector']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Show'),
    ];

    $form['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#submit' => ['::resetForm'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $options = [];
    $cleanValues = $form_state->cleanValues()->getValues();
    $this->tempStore->set('values', $cleanValues);
    foreach ($cleanValues as $key => $values) {
      if (!empty($values)) {
        $options['query'][$key] = is_array($values) ? array_values($values) : $values;
      }
    }

    $form_state->setRedirect('minfin_beleidsevaluaties.beleidsonderzoeken', [], $options);
  }

  /**
   * Resets the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function resetForm(array &$form, FormStateInterface $form_state): void {
    $this->tempStore->delete('values');
    $form_state->setRedirect('minfin_beleidsevaluaties.beleidsonderzoeken', [], []);
  }

}
