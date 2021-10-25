<?php

namespace Drupal\minfin_api\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Creates the swagger.json page.
 */
class SwaggerController extends ControllerBase {

  /**
   * The base url.
   *
   * @var string|null
   */
  protected $baseUrl;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(RequestStack $requestStack) {
    if ($request = $requestStack->getCurrentRequest()) {
      $this->baseUrl = $request->getSchemeAndHttpHost();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Returns the markup for the swagger page.
   *
   * @return array
   *   Drupal render array.
   */
  public function view(): array {
    $build = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['container'],
        'id' => 'swagger-ui',
      ],
      '#attached' => [
        'library' => [
          'minfin_api/api',
        ],
        'drupalSettings' => [
          'minfin_api' => [
            'url' => $this->baseUrl . '/doc/api/swagger.json',
          ],
        ],
      ],
    ];
    return $build;
  }

  /**
   * Returns the json file for the swagger API docs.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   Json response.
   */
  public function swagger(): CacheableJsonResponse {
    $swagger = \Swagger\scan(DRUPAL_ROOT . '/' . drupal_get_path('module', 'minfin_api'));
    $response = new CacheableJsonResponse($swagger, 200, ['Content-Type' => 'application/json']);
    $response->headers->set('Access-Control-Allow-Origin', $this->baseUrl);
    return $response;
  }

}
