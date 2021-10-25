<?php

namespace Drupal\minfin_search\Plugin\Block;

use Drupal\minfin_search\Form\SearchForm;

/**
 * Provides the search block.
 *
 * @Block(
 *  id = "minfin_search_block",
 *  admin_label = @Translation("MINFIN search block"),
 *  category = @Translation("MINFIN search"),
 * )
 */
class SearchBlock extends AdvancedSearchBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->formBuilder->getForm(SearchForm::class);
  }

}
