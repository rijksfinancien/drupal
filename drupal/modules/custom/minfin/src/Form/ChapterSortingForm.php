<?php

namespace Drupal\minfin\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the chapter sortings.
 */
class ChapterSortingForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'minfin_chapter_sorting_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('minfin.chapter_sorting');

    $form['chapters'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#prefix' => '<div id="chapters-wrapper"><table><tr><th>Hoofdstuk</th><th>Gewicht</th></tr>',
      '#suffix' => '</table></div>',
    ];
    $chapters = [];
    foreach ($config->get('chapters') ?? [] as $k => $v) {
      $chapters[] = [
        'chapter' => $k,
        'weight' => $v,
      ];
    }
    $chapterCount = $form_state->get('chapterCount');
    if (empty($chapterCount)) {
      $chapterCount = \count($chapters) + 1;
      $form_state->set('chapterCount', $chapterCount);
    }
    for ($i = 0; $i < $chapterCount; $i++) {
      $form['chapters'][$i]['chapter'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Chapter') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $chapters[$i]['chapter'] ?? NULL,
        '#maxlength' => 28,
        '#prefix' => '<tr><td>',
        '#suffix' => '</td>',
      ];

      $form['chapters'][$i]['weight'] = [
        '#type' => 'number',
        '#title' => $this->t('Weight') . ' ' . $i,
        '#title_display' => 'invisible',
        '#default_value' => $chapters[$i]['weight'] ?? NULL,
        '#min' => 1,
        '#max' => 9999,
        '#prefix' => '<td>',
        '#suffix' => '</td></tr>',
      ];
    }

    $form['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add'),
      '#submit' => ['::addOne'],
      '#ajax' => [
        'callback' => '::addMoreCallback',
        'wrapper' => 'chapters-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('minfin.chapter_sorting');

    $chapters = [];
    foreach ($form_state->getValue('chapters') as $value) {
      if ($value['chapter'] && $value['weight']) {
        $chapters[$value['chapter']] = $value['weight'];
      }
    }

    asort($chapters);
    $config->set('chapters', $chapters);
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['minfin.chapter_sorting'];
  }

  /**
   * {@inheritdoc}
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $form_state->set('chapterCount', $form_state->get('chapterCount') + 1);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function addMoreCallback(array &$form, FormStateInterface $form_state) {
    return $form['chapters'];
  }

}
