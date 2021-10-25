<?php

namespace Drupal\minfin_ckan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\minfin_ckan\CkanRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The dataset page.
 */
class DatasetController extends ControllerBase {

  /**
   * The CKAN request.
   *
   * @var \Drupal\minfin_ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * Constructs a ImportBaseForm object.
   *
   * @param \Drupal\minfin_ckan\CkanRequestInterface $ckanRequest
   *   The CKAN request.
   */
  public function __construct(CkanRequestInterface $ckanRequest) {
    $this->ckanRequest = $ckanRequest;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('minfin_ckan.request')
    );
  }

  /**
   * Get the page title.
   *
   * @param string $title
   *   The dataset.
   *
   * @return string
   *   The title.
   */
  public function title($title): string {
    return $title;
  }

  /**
   * Build the actual page.
   *
   * @param string $title
   *   The dataset.
   *
   * @return array
   *   A Drupal render array.
   */
  public function content($title): array {
    $title = strtolower($title);
    $search = str_replace('-', ' ', $title);
    if (($result = $this->ckanRequest->getDatasets($search)) && isset($result[$title]['datasets'])) {
      $datasets = $result[$title]['datasets'];

      $dataset = reset($datasets['all']);
      if (isset($datasets['tabs'])) {
        $tabs = reset($datasets['tabs']);
        $dataset = reset($tabs);
        krsort($datasets['tabs']);
      }

      return [
        '#theme' => 'dataset',
        '#activeDataset' => $dataset,
        '#datasets' => $datasets,
      ];
    }

    throw new NotFoundHttpException();
  }

}
