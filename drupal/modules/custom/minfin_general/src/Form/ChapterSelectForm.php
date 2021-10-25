<?php

namespace Drupal\minfin_general\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\minfin\MinfinServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A form to select the chapter.
 */
class ChapterSelectForm extends FormBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Minfin helper functionality.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * ChapterSelectForm constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   */
  public function __construct(RequestStack $requestStack, MinfinServiceInterface $minfinService) {
    $this->request = $requestStack->getCurrentRequest();
    $this->minfinService = $minfinService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('minfin.minfin')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_general_chapter_select_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $options = [];
    $defaultValue = NULL;
    $year = $this->minfinService->getActiveYear();

    if ($this->getRouteMatch()->getRouteName() === 'minfin_general.archive') {
      $year = NULL;
      $till = $this->routeMatch->getParameter('till');
      $from = $this->routeMatch->getParameter('from');
      if ($till && $from && $till === $from) {
        $year = $from;
      }
    }

    if ($year) {
      foreach ($this->minfinService->getChaptersForYear($year) as $chapter => $label) {
        $options[$this->getOptionKey($year, $chapter)] = $label;
      }
      if ($hoofdstukMinfinId = $this->request->get('hoofdstukMinfinId')) {
        $defaultValue = $this->getOptionKey($year, $hoofdstukMinfinId);
      }
    }

    $form['chapter_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Chapter select'),
      '#title_display' => 'invisible',
      '#attributes' => [
        'disabled' => empty($options),
        'class' => ['chapter-select', 'chosen'],
        'data-disable-search' => 'true',
      ],
      '#options' => $options,
      '#empty_option' => 'Kies begrotingshoofdstuk',
      '#default_value' => $defaultValue,
      '#attached' => [
        'library' => [
          'minfin_general/chapter_select',
        ],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => [
        'class' => ['visually-hidden'],
        'tabindex' => '-1',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // The submit functionality is handled by JavaScript.
  }

  /**
   * Get the option key.
   *
   * @param int $jaar
   *   The year.
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   *
   * @return string
   *   The option key.
   */
  private function getOptionKey(int $jaar, string $hoofdstukMinfinId) {
    $routeName = $this->getRouteMatch()->getRouteName();
    $routeParams = $this->getRouteMatch()->getParameters()->all();
    if (isset($routeParams['hoofdstukMinfinId'])) {
      $params = $routeParams;
      $params['hoofdstukMinfinId'] = $hoofdstukMinfinId;
      $url = Url::fromRoute($routeName, $params);
      if ($url->access()) {
        return $url->toString();
      }
    }

    $params = [
      'hoofdstukMinfinId' => $hoofdstukMinfinId,
      'year' => $jaar,
    ];
    return Url::fromRoute('minfin_general.chapter', $params)->toString();
  }

}
