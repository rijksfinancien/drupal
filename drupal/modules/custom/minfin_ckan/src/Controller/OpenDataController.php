<?php

namespace Drupal\minfin_ckan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\minfin\MinfinServiceInterface;
use Drupal\minfin_ckan\CkanRequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The open data page.
 */
class OpenDataController extends ControllerBase {

  /**
   * The file storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The CKAN request.
   *
   * @var \Drupal\minfin_ckan\CkanRequestInterface
   */
  protected $ckanRequest;

  /**
   * Minfin helper functionality.
   *
   * @var \Drupal\minfin\MinfinServiceInterface
   */
  protected $minfinService;

  /**
   * Constructs a ImportBaseForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\minfin_ckan\CkanRequestInterface $ckanRequest
   *   The CKAN request.
   * @param \Drupal\minfin\MinfinServiceInterface $minfinService
   *   Minfin helper functionality.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, CkanRequestInterface $ckanRequest, MinfinServiceInterface $minfinService) {
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->ckanRequest = $ckanRequest;
    $this->minfinService = $minfinService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('minfin_ckan.request'),
      $container->get('minfin.minfin')
    );
  }

  /**
   * Build the actual page.
   *
   * @return array
   *   A Drupal render array.
   */
  public function content(): array {
    $items = [];
    foreach ($this->ckanRequest->getDatasets('', FALSE) as $key => $values) {
      $routeParams = [
        'title' => $key,
      ];
      $items[] = Link::createFromRoute($values['title'], 'minfin_ckan.open_data.dataset', $routeParams);
    }
    $config = $this->config('minfin_general.open_data.settings');
    $banner = '';
    if ($data = $config->get('open_data_block')) {
      if ($data['banner_image'][0] && ($file = $this->fileStorage->load($data['banner_image'][0]))) {
        /** @var \Drupal\file\FileInterface $file */
        $banner = $file->createFileUrl();
      }
    }

    return [
      '#theme' => 'open_data',
      '#banner' => $banner,
      '#content' => [
        '#theme' => 'open_data_items',
        '#items' => $items,
        '#list_type' => 'ol',
      ],
    ];
  }

}
