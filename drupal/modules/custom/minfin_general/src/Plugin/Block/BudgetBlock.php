<?php

namespace Drupal\minfin_general\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Drupal\minfin\MinfinServiceInterface;
use Drupal\minfin_general\Form\ArchiveSelectorForm;
use Drupal\minfin_general\Form\ChapterSelectForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a budget block.
 *
 * @Block(
 *  id = "general_budget_block",
 *  admin_label = @Translation("Minfin budget block"),
 * )
 */
class BudgetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Minfin helper functionality.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * BannerBlock constructor.
   *
   * @param array $configuration
   *   The block configuration.
   * @param string $pluginId
   *   The block id.
   * @param mixed $pluginDefinition
   *   The block definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $routeMatch
   *   The current route match.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, Connection $connection, CurrentRouteMatch $routeMatch, MinfinServiceInterface $minfinService, FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->connection = $connection;
    $this->routeMatch = $routeMatch;
    $this->minfinService = $minfinService;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('database'),
      $container->get('current_route_match'),
      $container->get('minfin.minfin'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['select_next_to_year'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show chapter select next to years?'),
      '#default_value' => $config['select_next_to_year'] ?? '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['select_next_to_year'] = $values['select_next_to_year'];
  }

  /**
   * Build the block.
   *
   * @return array
   *   A render array.
   */
  public function build(): array {
    $isArchivePath = $this->routeMatch->getRouteName() === 'minfin_general.archive';

    $activeYear = $this->minfinService->getActiveYear();
    $lastYear = $this->minfinService->getLastYear();
    $config = $this->getConfiguration();
    $route = $this->routeMatch->getRouteName();

    $artikelMinfinId = NULL;
    $activeParams = $this->routeMatch->getParameters()->all();
    if (!empty($activeParams['anchor']) && !empty($activeParams['phase']) && !empty($activeParams['hoofdstukMinfinId']) && (strpos($route, 'minfin.') === 0) && ($type = explode('.', $route)[1])) {
      $artikelMinfinId = $this->minfinService->getArtikelMinfinIdFromKamerstukAnchor($type, $activeYear, $activeParams['phase'], $activeParams['hoofdstukMinfinId'], $activeParams['anchor']);
    }

    $routeOptions = [
      'attributes' => [
        'class' => ['tab'],
      ],
    ];
    foreach ([$lastYear, $lastYear - 1, $lastYear - 2] as $year) {
      $url = NULL;
      $routeParams = $activeParams;
      $route = $this->routeMatch->getRouteName();
      if (!empty($routeParams['year'])) {
        $routeParams['year'] = $year;
        $kamerstukRoutes = [
          'minfin.memorie_van_toelichting',
          'minfin.jaarverslag',
          'minfin.miljoenennota',
          'minfin.belastingplan_memorie_van_toelichting',
          'minfin.voorjaarsnota',
          'minfin.najaarsnota',
          'minfin.financieel_jaarverslag',
        ];

        $explode = explode('.', $route);
        if (isset($explode[1]) && in_array($explode[0] . '.' . $explode[1], $kamerstukRoutes, TRUE)) {
          $url = $this->minfinService->buildKamerstukUrl($explode[0] . '.' . $explode[1], $year, $routeOptions, $routeParams['phase'] ?? NULL, $routeParams['hoofdstukMinfinId'] ?? NULL, $artikelMinfinId);
        }
        elseif ($explode[0] === 'minfin_general' && isset($explode[1]) && $explode[1] === 'year') {
          $explode[2] = $year;
          $url = Url::fromRoute(implode('.', $explode), $routeParams, $routeOptions);
        }
        else {
          $url = Url::fromRoute($route, $routeParams, $routeOptions);
        }
      }

      if ($url && $url->access()) {
        $tabs[$year] = Link::fromTextAndUrl($year, $url)->toRenderable();
      }
      else {
        $tabs[$year] = Link::fromTextAndUrl($year, Url::fromUserInput('/' . $year, $routeOptions))->toRenderable();
      }
    }

    $archiveRouteParams = [];
    if ($hoofdstukMinfinId = $this->routeMatch->getParameter('hoofdstukMinfinId')) {
      $archiveRouteParams['from'] = $activeYear;
      $archiveRouteParams['till'] = $activeYear;
      $archiveRouteParams['hoofdstukMinfinId'] = $hoofdstukMinfinId;
    }
    $tabs['archive'] = Link::fromTextAndUrl($this->t('Archive'), Url::fromRoute('minfin_general.archive', $archiveRouteParams, $routeOptions))->toRenderable();

    return [
      '#theme' => 'budget_block',
      '#tabs' => $tabs,
      '#active_tab' => $isArchivePath ? 'archive' : $activeYear,
      '#content' => $isArchivePath ? $this->renderArchive($activeParams) : $this->renderLinks($route, $activeParams, $activeYear, $artikelMinfinId),
      '#chapter_select' => $this->formBuilder->getForm(ChapterSelectForm::class),
      '#select_next_to_year' => $config['select_next_to_year'] ?? '',
      '#attached' => [
        'library' => [
          'minfin_general/minfin-general',
        ],
      ],
    ];
  }

  /**
   * Render the archive.
   *
   * @param array $activeParams
   *   The active params.
   *
   * @return array
   *   A render array.
   */
  private function renderArchive(array $activeParams): array {
    $hoofdstukMinfinId = $activeParams['hoofdstukMinfinId'] ?? NULL;
    return $this->formBuilder->getForm(ArchiveSelectorForm::class, $hoofdstukMinfinId);
  }

  /**
   * Render the links.
   *
   * @param string|null $route
   *   The route.
   * @param array $activeParams
   *   The active params.
   * @param int $activeYear
   *   The active year.
   * @param string|null $artikelMinfinId
   *   The artikelMinfinId.
   *
   * @return array
   *   A render array.
   */
  private function renderLinks(?string $route, array $activeParams, int $activeYear, ?string $artikelMinfinId): array {
    // Handle the 'VvW' links as if they are the 'MvT' links.
    if (strpos($route, 'minfin.belastingplan_voorstel_van_wet.') === 0) {
      $route = str_replace('minfin.belastingplan_voorstel_van_wet.', 'minfin.belastingplan_memorie_van_toelichting.', $route);
    }

    $links = [];
    foreach ($this->minfinService->getCategories() as $label => $category) {
      foreach ($category as $index => $link) {
        if (isset($link['text'], $link['routePrefix'])) {
          $options = [];
          if (in_array($route, [
            $link['routePrefix'] . '.overview',
            $link['routePrefix'] . '.table_of_contents',
            $link['routePrefix'] . '.anchor',
            $link['routePrefix'] . '.voorstel_van_wet',
          ], TRUE)) {
            if ($link['routePrefix'] !== 'minfin.memorie_van_toelichting' || (isset($link['params']['phase']) && $this->routeMatch->getParameter('phase') === $link['params']['phase'])) {
              $options['attributes']['class'][] = 'active';
            }
          }

          $links[$label][$index] = $link['text'];
          $url = $this->minfinService->buildKamerstukUrl($link['routePrefix'], $activeYear, $options, $link['params']['phase'] ?? NULL, $activeParams['hoofdstukMinfinId'] ?? NULL, $artikelMinfinId);
          if ($url && $url->access()) {
            $links[$label][$index] = Link::fromTextAndUrl($link['text'], $url);
          }
        }
      }
    }

    return [
      '#theme' => 'budget_block_links',
      '#links' => $links,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return Cache::mergeTags(parent::getCacheContexts(), ['url']);
  }

}
