<?php

namespace Drupal\minfin_piwik;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * DataLayer service.
 */
class DataLayerService {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * The session interface.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  private $session;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $currentUser;

  /**
   * The langauge interface.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  private $currentLanguage;

  /**
   * The currenct environment.
   *
   * @var string
   */
  private $environment;

  /**
   * The current page title.
   *
   * @var string
   */
  private $pageTitle;

  /**
   * The route match interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * DataLayerService constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $accountProxy
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Drupal\Core\Controller\TitleResolverInterface $titleResolver
   *   The title resolver.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The render inteterface.
   *
   * @throws \Exception
   */
  public function __construct(AccountProxyInterface $accountProxy, ConfigFactoryInterface $configFactory, Connection $connection, EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, RequestStack $requestStack, RouteMatchInterface $routeMatch, TitleResolverInterface $titleResolver, RendererInterface $renderer) {
    $this->connection = $connection;
    $this->session = $requestStack->getCurrentRequest()->getSession();
    $this->currentUser = $entityTypeManager->getStorage('user')->load($accountProxy->id());
    $this->environment = $configFactory->get('indicia_profile')->get('environment');
    $this->currentLanguage = $languageManager->getCurrentLanguage();
    $this->routeMatch = $routeMatch;
    $title = $titleResolver->getTitle($requestStack->getCurrentRequest(), $routeMatch->getRouteObject());
    $this->pageTitle = (string) (is_array($title) ? $renderer->render($title) : $title);
  }

  /**
   * Return the values.
   *
   * @return array
   *   An array with values.
   */
  public function getValues(): array {
    $settings = $this->getDataLayerSettings();

    $values = [
      'site_name' => 'Rijksfinancien',
      'site_env' => $this->environment,
      'page_title' => $this->pageTitle,
      'page_type' => $settings['page_type'],
      'page_language' => $this->currentLanguage->getId(),
      'user_type' => $this->getUserType(),
    ];

    if ($settings['handler'] === 'search' && $searchValues = $this->session->get('minfin_piwik.search')) {
      $values['search_term'] = $searchValues['search_term'] ?? '';
      $values['search_page'] = $searchValues['search_page'] ?? 1;
      $values['search_results'] = $searchValues['search_results'] ?? 0;
      $values['search_filters'] = $searchValues['search_filters'] ?? '';
    }

    return $values;
  }

  /**
   * Get the user type.
   *
   * @return string
   *   The user type.
   */
  private function getUserType(): string {
    if ($this->currentUser->id() > 0) {
      if ($this->currentUser->id() === 1 || $this->currentUser->hasRole('administrator')) {
        return 'admin';
      }
      return 'user';
    }
    return 'anonymous';
  }

  /**
   * Get the datalayer settigns.
   *
   * @return array
   *   An array with the settings.
   */
  private function getDataLayerSettings(): array {
    $routeName = explode('.', $this->routeMatch->getRouteName());

    $query = $this->connection->select('minfin_piwik_datalayer', 'd');
    $query->fields('d', ['page_type', 'handler']);

    $route = '';
    $max = count($routeName) - 1;
    $or = $query->orConditionGroup();
    for ($i = 0; $i <= $max; $i++) {
      if ($i === 0) {
        $route = $routeName[0];
      }
      else {
        $route .= '.' . $routeName[$i];
      }

      if ($i < $max) {
        $or->condition('route', $route . '.*', '=');
      }
      else {
        $or->condition('route', $route, '=');
      }
    }

    $query->condition($or);
    $query->orderBy('route', 'DESC');
    $query->range(0, 1);
    if ($result = $query->execute()->fetchAssoc()) {
      return $result;
    }
    return [
      'page_type' => 'undefined',
      'handler' => 'default',
    ];
  }

}
