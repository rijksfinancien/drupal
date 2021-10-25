<?php

namespace Drupal\minfin_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\minfin\MinfinServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A form for the archive selector.
 */
class ArchiveSelectorForm extends FormBase {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Minfin helper functionality.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * ChapterSelectForm constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   */
  public function __construct(RouteMatchInterface $routeMatch, MinfinServiceInterface $minfinService) {
    $this->routeMatch = $routeMatch;
    $this->minfinService = $minfinService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('minfin.minfin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_general_archive_selector_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string|null $hoofdstukMinfinId
   *   The hoofdstukMinfinId.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?string $hoofdstukMinfinId = NULL): array {
    $params = [];
    if ($hoofdstukMinfinId) {
      $params['hoofdstukMinfinId'] = $hoofdstukMinfinId;
    }

    $fromParam = (int) $this->routeMatch->getParameter('from');
    $tillParam = (int) $this->routeMatch->getParameter('till');

    $items = [];
    $firstYear = $this->minfinService->getFirstYear();
    $lastYear = $this->minfinService->getLastYear();
    foreach (range($firstYear, $lastYear) as $year) {
      $options = [];
      if ($fromParam === $year && $tillParam === $year) {
        $options['attributes']['class'][] = 'active';
      }
      $items[$year] = Link::createFromRoute($year, 'minfin_general.archive', array_merge($params, ['from' => $year, 'till' => $year]), $options);
    }

    $missingYearMarkup = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#attributes' => [
        'class' => ['missing-year'],
      ],
    ];

    // Fills out the missing years at the start of the list,
    // till there are at least 10 items.
    if (($itemsCount = count($items)) < 10) {
      for ($i = $itemsCount; $i < 10; $i++) {
        $firstYear--;
        $items[$firstYear] = $missingYearMarkup;
      }
    }
    krsort($items);

    $form_state->set('hoofdstukMinfinId', $hoofdstukMinfinId);

    $form['years'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
      '#attributes' => ['class' => ['year-selector']],
    ];

    $form['selector'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['selector-wrapper'],
      ],
    ];

    $form['selector']['from'] = [
      '#type' => 'number',
      '#title' => $this->t('Year or period'),
      '#min' => $this->minfinService->getFirstYear(),
      '#max' => $this->minfinService->getLastYear(),
      '#default_value' => $this->routeMatch->getParameter('from'),
      '#attributes' => [
        'placeholder' => $this->minfinService->getActiveYear(),
      ],
    ];

    $form['selector']['till'] = [
      '#type' => 'number',
      '#title' => $this->t('To', [], ['context' => 'abbreviation']),
      '#min' => $this->minfinService->getFirstYear(),
      '#max' => $this->minfinService->getLastYear(),
      '#default_value' => $this->routeMatch->getParameter('till'),
      '#attributes' => [
        'placeholder' => $this->minfinService->getActiveYear(),
      ],
    ];

    $form['selector']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Show'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $params = [
      'from' => $form_state->getValue('from') ?: $this->minfinService->getActiveYear(),
      'till' => $form_state->getValue('till') ?: $this->minfinService->getActiveYear(),
    ];
    if ($hoofdstukMinfinId = $form_state->get('hoofdstukMinfinId')) {
      $params['hoofdstukMinfinId'] = $hoofdstukMinfinId;
    }

    $form_state->setRedirect('minfin_general.archive', $params);
  }

}
