<?php

namespace Drupal\minfin\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides documentation for the imports.
 */
class ImportDocumentation extends ControllerBase {

  /**
   * Build the page.
   *
   * @param string $type
   *   The type.
   *
   * @return array
   *   A Drupal render array.
   */
  public function buildPage(string $type): array {
    $documentation = $this->getDocumentation($type);
    if (empty($documentation)) {
      return ['#markup' => $this->t('No documentation found.')];
    }

    $details = [];
    foreach ($documentation as $title => $data) {
      if (isset($data['headers'])) {
        $detail = [
          '#type' => 'details',
          '#title' => $title,
        ];
        $detail['table'] = [
          '#type' => 'table',
          '#header' => ['Kolom', 'Header', 'Soort data'],
          '#rows' => $data['headers'],
        ];
        if (isset($data['remarks'])) {
          $detail['remarks'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => $data['remarks'] ?? '',
            '#attributes' => [
              'class' => ['remarks'],
            ],
          ];
        }
        if (isset($data['manual_delete']) && $data['manual_delete']) {
          $detail['manual_delete'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => '<strong>Let op: </strong> bij deze import moet je zelf actief aangeven of je de oude waardes wilt verwijderen. Dit is gedaan omdat het bestand te groot kan zijn om in een keer te uploaden, door het vinkje alleen bij de eerste import aan te zetten en daarna uit te laten kan je een te groot bestand zelf opsplitsen en in meerdere delen uploaden.',
            '#attributes' => [
              'class' => ['remarks'],
            ],
          ];
        }
        if (isset($data['names_not_imported']) && $data['names_not_imported']) {
          $detail['names_not_imported'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#value' => 'De begrotings-/artikelnaam worden niet geimporteerd vanuit dit bestand. De naamgeving die uiteindelijk wordt gebruikt wordt opgehaald uit de database op bassis van het begrotings-/artikelnummer (voor een consistent naamgebruik met de budgettaire tabellen).',
            '#attributes' => [
              'class' => ['remarks'],
            ],
          ];
        }

        $details[] = $detail;
      }
    }

    if (count($details) === 1) {
      $details[0]['#open'] = TRUE;
    }

    $build['#attributes']['class'][] = 'documentation';
    $build['#attached']['library'][] = 'minfin/chapter_names_form';

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['minfin-import-documentation'],
      ],
      '#attached' => [
        'library' => ['minfin/documentation'],
      ],
      'details' => $details,
    ];
  }

  /**
   * Get the documentation.
   *
   * @param string $type
   *   The type.
   *
   * @return array
   *   A Drupal render array.
   */
  private function getDocumentation(string $type): array {
    switch ($type) {
      case 'beleidsevaluaties':
        return [
          'Beleidsevaluaties' => [
            'headers' => [
              ['A', 'Titel onderzoek', ''],
              ['B', 'Departement', ''],
              ['C', 'Artikelnaam', ''],
              ['D', '', ''],
              ['E', 'Type onderzoek', ''],
              ['F', 'Status', ''],
              ['G', 'Opleverdatum', $this->t('number', [], ['context' => 'minfin import'])],
              ['H', 'Begrotingsnummer', 'Komma gescheiden lijst'],
              ['I', 'Artikelnummer', 'Komma gescheiden lijst'],
              ['J', '', ''],
              ['K', '', ''],
              ['L', 'Link SEA', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['M', '', ''],
              ['N', 'Toelichting onderzoek', ''],
              ['O', 'Onafhankelijke deskundige', ''],
              ['P', 'Hoofdrapport', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['Q', 'Aankondiging', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['R', 'Kabinetsreactie/Aanbiedingsbrief', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['S', 'Open data', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['T', 'Bijlage 1', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['U', 'Bijlage 2', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['V', 'Bijlage 3', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['W', 'Bijlage 4', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['X', 'Bijlage 5', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['Y', 'Bijlage 6', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['Z', 'Bijlage 7', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['AA', 'Bijlage 8', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['AB', 'Bijlage 9', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['AC', 'Bijlage 10', $this->t('complete URL', [], ['context' => 'minfin import'])],
            ],
          ],
        ];

      case 'budgettaire_tabellen':
        return [
          'Ontwerp begroting' => [
            'headers' => [
              ['A', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Begrotingsnummer', ''],
              ['C', 'Minister', ''],
              ['D', 'Begrotingsnaam', ''],
              ['E', '', ''],
              ['F', 'Fase', ''],
              ['G', 'Artikelnummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['H', '', ''],
              ['I', 'Artikelnaam', ''],
              ['J', '', ''],
              ['K', '', ''],
              ['L', '', ''],
              ['M', 'VUO', ''],
              ['N', '', ''],
              ['O', '', ''],
              ['P', 'Artikelonderdeel', ''],
              ['Q', 'Artikelonderdeel nummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['R', 'Financieel instrument', ''],
              ['S', 'Financieel instrument nummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['T', 'Regeling detailniveau', ''],
              ['U', '', ''],
              ['V', '', ''],
              ['W', '', ''],
              ['X', 'Bedrag t', $this->t('amount', [], ['context' => 'minfin import'])],
              ['Y', '', ''],
              ['Z', '', ''],
              ['AA', '', ''],
              ['AB', '', ''],
              ['AC', 'Index', ''],
            ],
          ],
          '1e Suppletoire' => [
            'headers' => [
              ['A', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Begrotingsnummer', ''],
              ['C', 'Minister', ''],
              ['D', 'Begrotingsnaam', ''],
              ['E', '', ''],
              ['F', 'Fase', ''],
              ['G', 'Artikelnummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['H', '', ''],
              ['I', 'Artikelnaam', ''],
              ['J', '', ''],
              ['K', '', ''],
              ['L', '', ''],
              ['M', 'VUO', ''],
              ['N', '', ''],
              ['O', '', ''],
              ['P', 'Artikelonderdeel', ''],
              ['Q', 'Artikelonderdeel nummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['R', 'Financieel instrument', ''],
              ['S', 'Financieel instrument nummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['T', 'Regeling detailniveau', ''],
              ['U', '', ''],
              ['V', '', ''],
              ['W', '', ''],
              ['X', 'Stand vastgestelde begroting', $this->t('amount', [], ['context' => 'minfin import'])],
              ['Y', '', ''],
              ['Z', '', ''],
              ['AA', 'Stand 1e suppletoire begroting', $this->t('amount', [], ['context' => 'minfin import'])],
              ['AB', '', ''],
              ['AC', '', ''],
              ['AD', '', ''],
              ['AE', '', ''],
              ['AF', 'Index', ''],
            ],
          ],
          'Jaarverslag & 2e Suppletoire' => [
            'headers' => [
              ['A', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Begrotingsnummer', ''],
              ['C', 'Minister', ''],
              ['D', 'Begrotingsnaam', ''],
              ['E', '', ''],
              ['F', 'Fase', ''],
              ['G', 'Artikelnummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['H', '', ''],
              ['I', 'Artikelnaam', ''],
              ['J', '', ''],
              ['K', '', ''],
              ['L', '', ''],
              ['M', 'VUO', ''],
              ['N', '', ''],
              ['O', '', ''],
              ['P', 'Artikelonderdeel', ''],
              ['Q', 'Artikelonderdeel nummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['R', 'Financieel instrument', ''],
              ['S', 'Financieel instrument nummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['T', 'Regeling detailniveau', ''],
              ['U', '', ''],
              ['V', '', ''],
              ['W', '', ''],
              ['X', '', ''],
              ['Y', '', ''],
              ['Z', 'Bedrag t', $this->t('amount', [], ['context' => 'minfin import'])],
              ['AA', 'Index', ''],
            ],
          ],
        ];

      case 'budgettaire_tabellen_history':
        return [
          'Budgettaire tabellen (before-became table)' => [
            'headers' => [
              ['A', 'Was', $this->t('specific values (see explanation)', [], ['context' => 'minfin import'])],
              ['B', 'Wordt', $this->t('specific values (see explanation)', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'In kolom A & B worden de index nummers verwacht die gebruikt zijn bij de Budgettaire tabellen import.<br /><br /><strong>Let op: </strong> Zorg ervoor dat de Budgettaire tabellen zelf volledig geimporteerd zijn voordat je deze import start.',
            'manual_delete' => TRUE,
          ],
        ];

      case 'corona_visual':
        return [
          'Automatische Stablisatoren Inkomsten' => [
            'headers' => [
              ['A', 'Visual uitleg', $this->t('specific values (see explanation)', [], ['context' => 'minfin import'])],
              ['B', 'Automatische stablisatie', ''],
              ['C', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'Er worden 4 regels verwacht waarbij Kolom A exact de volgende waardes bevat:<br />Linkerbalk<br />Rechterbalk - grijs<br />Rechterbalk - gestreept<br />Rechterbalk',
          ],
          'Automatische Stablisatoren Uitgaven' => [
            'headers' => [
              ['A', 'Visual uitleg', $this->t('specific values (see explanation)', [], ['context' => 'minfin import'])],
              ['B', 'Raming WW- en bijstanduitgaven 2020', ''],
              ['C', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'Er worden 3 regels verwacht waarbij Kolom A exact de volgende waardes bevat:<br />Linkerbalk<br />Rechterbalk - blauw<br />Rechterbalk - gestreept',
          ],
          'Belastinguitstel' => [
            'headers' => [
              ['A', 'Belasting', ''],
              ['B', 'Specificiatie', ''],
              ['C', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
          ],
          'Belastinguitstel (toelichting)' => [
            'headers' => [
              ['A', 'Maatregel', ''],
              ['B', 'Toelichting', ''],
              ['C', 'Uitleg', ''],
              ['D', 'Link', $this->t('complete URL', [], ['context' => 'minfin import'])],
            ],
          ],
          'Emu Saldo' => [
            'headers' => [
              ['A', 'Waarde', $this->t('specific values (see explanation)', [], ['context' => 'minfin import'])],
              ['B', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['C', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['D', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['F', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['H', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['J', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['K', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'Er worden 3 regels verwacht waarbij Kolom A de volgende waardes bevat:<br />EMU-saldo<br />bbp<br />EMU-saldo (in procenten bbp)<br /><br />De x in "Bedrag x" is het jaartal waaronder de data geimporteerd wordt. Hierbij verwacht de importer minimaal 3 en maximaal 10 jaren.',
          ],
          'Emu Schuld' => [
            'headers' => [
              ['A', 'Waarde', $this->t('specific values (see explanation)', [], ['context' => 'minfin import'])],
              ['B', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['C', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['D', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['F', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['H', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['J', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
              ['K', 'Bedrag x', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'Er worden 3 regels verwacht waarbij Kolom A de volgende waardes bevat:<br />bbp<br />EMU-schuld<br />EMU-schuld (in procenten bbp)<br /><br />De x in "Bedrag x" is het jaartal waaronder de data geimporteerd wordt. Hierbij verwacht de importer minimaal 3 en maximaal 10 jaren.',
          ],
          'Endogene ontwikkelingen' => [
            'headers' => [
              ['A', 'Endogene ontwikkelingen', ''],
              ['B', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
          ],
          'Fiscalemaatregelen' => [
            'headers' => [
              ['A', 'Consolidatienr', ''],
              ['B', 'Belastingsoort', ''],
              ['C', 'Maatregel', ''],
              ['D', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Bedrag 2021', $this->t('amount', [], ['context' => 'minfin import'])],
              ['F', 'Bedrag 2022', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Bedrag 2023', $this->t('amount', [], ['context' => 'minfin import'])],
              ['H', 'Bedrag 2024', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2025', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2026', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2027', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2028', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2029', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2030', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
          ],
          'Fiscalemaatregelen (toelichting)' => [
            'headers' => [
              ['A', 'Consolidatienr', ''],
              ['B', 'Belastingsoort', ''],
              ['C', 'Maatregel', ''],
              ['D', 'Toelichting', ''],
              ['E', 'Uitleg', ''],
              ['D', 'Link', $this->t('complete URL', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'De waardes in kolom B & C moeten overeenkomen met de waardes die geupload zijn bij voor de Fiscalemaatregelen in kolom B & C.',
          ],
          'Garanties' => [
            'headers' => [
              ['A', 'Nationaal/Internationaal', ''],
              ['B', 'Nationaal/Internationaal', ''],
              ['C', 'Maatregel', ''],
              ['D', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Bedrag 2021', $this->t('amount', [], ['context' => 'minfin import'])],
              ['F', 'Bedrag 2022', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Bedrag 2023', $this->t('amount', [], ['context' => 'minfin import'])],
              ['H', 'Bedrag 2024', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2025', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2026', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2027', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2028', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2029', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2030', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'Kolom A wordt gebruikt als identifier voor de doorklik, Kolom B wordt gebruikt als titel.',
          ],
          'Garanties (toelichting)' => [
            'headers' => [
              ['A', 'Nationaal/Internationaal', ''],
              ['B', 'Maatregel', ''],
              ['C', 'Toelichting', ''],
              ['D', 'Uitleg', ''],
              ['E', 'Link', $this->t('complete URL', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'De waardes in kolom A, B moeten overeenkomen met de waardes die geupload zijn bij voor de Garanties in kolom A & C.',
          ],
          'Leningen' => [
            'headers' => [
              ['A', 'Maatregel', ''],
              ['B', 'Maatregel', ''],
              ['C', 'Onderverdeling maatregel', ''],
              ['D', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Bedrag 2021', $this->t('amount', [], ['context' => 'minfin import'])],
              ['F', 'Bedrag 2022', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Bedrag 2023', $this->t('amount', [], ['context' => 'minfin import'])],
              ['H', 'Bedrag 2024', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2025', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2026', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2027', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2028', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2029', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2030', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'Kolom A wordt gebruikt als identifier voor de doorklik, Kolom B wordt gebruikt als titel. Kolom C was ooit een onderverdeling, maar deze wordt momenteel niet gebruikt. Zolang deze onderverdeling niet gebruikt wordt moet deze kolom leeg blijven.',
          ],
          'Leningen (toelichting)' => [
            'headers' => [
              ['A', 'Maatregel', ''],
              ['B', '', ''],
              ['C', '', ''],
              ['D', 'Toelichting', ''],
              ['E', 'Uitleg', ''],
              ['F', 'Link', $this->t('complete URL', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'De waardes in kolom A moet overeenkomen met de waardes die geupload zijn bij voor de Leningen in kolom A.',
          ],
          'Plafond' => [
            'headers' => [
              ['A', 'Visual uitleg', $this->t('specific values (see explanation)', [], ['context' => 'minfin import'])],
              ['B', 'Waarde', ''],
              ['D', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Bedrag 2021', $this->t('amount', [], ['context' => 'minfin import'])],
              ['F', 'Bedrag 2022', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Bedrag 2023', $this->t('amount', [], ['context' => 'minfin import'])],
              ['H', 'Bedrag 2024', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2025', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2026', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2027', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2028', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2029', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2030', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'Er worden 6 regels verwacht waarbij Kolom A exact de volgende waardes bevat:<br />Inkomsten<br />Corona<br />Lijn<br />Plafond 1: Rijksbegroting<br />Plafond 2:Sociale Zekerheid<br />Plafond 3: Zorg',
          ],
          'Plafond: Corona hoofdstukken' => [
            'headers' => [
              ['A', 'Begrotingsnummer', ''],
              ['B', 'Begrotingsnaam', ''],
              ['C', 'Maatregel', ''],
              ['D', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Bedrag 2021', $this->t('amount', [], ['context' => 'minfin import'])],
              ['F', 'Bedrag 2022', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Bedrag 2023', $this->t('amount', [], ['context' => 'minfin import'])],
              ['H', 'Bedrag 2024', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2025', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2026', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2027', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2028', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2029', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2030', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
          ],
          'Tijdlijn noodpakketten' => [
            'headers' => [
              ['A', '', ''],
              ['B', 'Datum', ''],
              ['C', 'Begrotingsnummer', ''],
              ['D', 'Begrotingsnaam', ''],
              ['E', 'Maatregel', ''],
              ['F', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'Kolom B heet "datum", maar bevat momenteel het type verslag (Miljoennennota, Najaarsnota, Voorjaarsnota, Jaarverslag).',
          ],
          'Uitgavenmaatregelen' => [
            'headers' => [
              ['A', 'Begrotingsnummer', ''],
              ['B', 'Begrotingsnaam', ''],
              ['C', 'Maatregel', ''],
              ['D', 'Bedrag 2020', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Bedrag 2021', $this->t('amount', [], ['context' => 'minfin import'])],
              ['F', 'Bedrag 2022', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Bedrag 2023', $this->t('amount', [], ['context' => 'minfin import'])],
              ['H', 'Bedrag 2024', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2025', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2026', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2027', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2028', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2029', $this->t('amount', [], ['context' => 'minfin import'])],
              ['I', 'Bedrag 2030', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
          ],
          'Uitgavenmaatregelen (toelichting)' => [
            'headers' => [
              ['A', 'Begrotingsnummer', ''],
              ['B', 'Hoofdstuk', ''],
              ['C', 'Maatregel', ''],
              ['D', 'Toelichting', ''],
              ['E', 'Uitleg', ''],
              ['F', 'Link', $this->t('complete URL', [], ['context' => 'minfin import'])],
            ],
            'remarks' => 'De waardes in kolom A & C moet overeenkomen met de waardes die geupload zijn bij voor de Uitgavenmaatregel in kolom A & C.',
          ],
        ];

      case 'financiele_instrumenten':
        return [
          'Financiele instrumenten' => [
            'headers' => [
              ['A', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Begrotingsnummer', ''],
              ['C', '', ''],
              ['D', 'Artikelnummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['E', '', ''],
              ['F', '', ''],
              ['G', 'Instrument', ''],
              ['H', 'Regeling', ''],
              ['I', 'Ontvanger', ''],
              ['J', 'Realisatie (x1000)', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'names_not_imported' => TRUE,
          ],
        ];

      case 'fiscale_regelingen':
        return [
          'Begroting' => [
            'headers' => [
              ['A', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', '', ''],
              ['C', 'Begrotingsnummer', ''],
              ['D', '', ''],
              ['E', 'Artikelnummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['F', '', ''],
              ['G', 'Stand ontwerpbegroting', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'names_not_imported' => TRUE,
          ],
          'Fiscaal' => [
            'headers' => [
              ['A', '', ''],
              ['B', '', ''],
              ['C', '', ''],
              ['D', '', ''],
              ['E', '', ''],
              ['F', 'Bedrag', $this->t('amount', [], ['context' => 'minfin import'])],
              ['G', 'Begrotingshoofdstuk', ''],
              ['H', 'Begrotingsartikel', ''],
            ],
            'remarks' => 'Voor zowel kolom G als H verwachten we een begrotings-/artikelnummer. In de aangeleverde bestanden is dit altijd een begrotings-/artikelnummer inclusief de begrotings-/artikelnaam gescheiden met een dubbele punt (:), bv. "XII: I&W". In princiepe wordt alleen het nummer geimporteerd, dus als dit veld alleen het begrotigns-/artikelnummer bevat is het ook goed.',
            'names_not_imported' => TRUE,
          ],
          'Premie' => [
            'headers' => [
              ['A', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Begrotingsnummer', ''],
              ['C', '', ''],
              ['D', '', ''],
              ['E', '', ''],
              ['F', '', ''],
              ['G', 'Artikelnummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['H', '', ''],
              ['I', '', ''],
              ['J', '', ''],
              ['K', '', ''],
              ['L', '', ''],
              ['M', '', ''],
              ['N', '', ''],
              ['O', '', ''],
              ['P', '', ''],
              ['Q', '', ''],
              ['R', '', ''],
              ['S', '', ''],
              ['T', '', ''],
              ['U', '', ''],
              ['V', 'Bedrag t', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
            'names_not_imported' => TRUE,
          ],
        ];

      case 'kamerstuk':
        return [
          'Kamerstuk' => [
            'headers' => [
              ['A', 'Nummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['C', 'Fase', ''],
              ['D', 'Hoofdstuk', ''],
              ['E', 'Url', $this->t('complete URL', [], ['context' => 'minfin import'])],
            ],
          ],
        ];

      case 'kamerstuk_pdf':
        return [
          'Kamerstuk PDF' => [
            'headers' => [
              ['A', 'Nummer', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['C', 'Fase', ''],
              ['D', 'Hoofdstuk', ''],
              ['E', 'Url', $this->t('complete URL', [], ['context' => 'minfin import'])],
            ],
          ],
        ];

      case 'subsidies':
        return [
          'Subsidies' => [
            'headers' => [
              ['A', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Begrotingsnummer', ''],
              ['C', '', ''],
              ['D', '', ''],
              ['E', 'Beleidsartikel', ''],
              ['F', 'Regeling', ''],
              ['G', 'Naam ontvanger', ''],
              ['H', '', ''],
              ['I', 'Uitgekeerd bedrag', $this->t('amount', [], ['context' => 'minfin import'])],
            ],
          ],
        ];

      case 'uitzonderingen':
        return [
          'Uitzonderingen' => [
            'headers' => [
              ['A', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
              ['B', 'Fase', ''],
              ['C', 'Hoofdstuk', ''],
              ['D', 'Level_1', $this->t('number', [], ['context' => 'minfin import'])],
              ['E', 'Level_2', $this->t('number/empty', [], ['context' => 'minfin import'])],
              ['F', 'Level_3', $this->t('number/empty', [], ['context' => 'minfin import'])],
              ['G', 'Artikel Hoofdstuk', ''],
              ['H', 'Artikel Nummer', ''],
              ['I', 'B Tabel', $this->t('number/empty', [], ['context' => 'minfin import'])],
              ['C', 'Geen Subhoofdstukken', $this->t('number/empty', [], ['context' => 'minfin import'])],
            ],
          ],
        ];

      case 'verzelfstandigingen':
        return [
          'Verzelfstandigingen' => [
            'headers' => [
              ['A', 'Ministerie', ''],
              ['B', 'Organisatie', ''],
              ['C', 'FTE', $this->t('number', [], ['context' => 'minfin import'])],
              ['D', 'Omzet', $this->t('amount', [], ['context' => 'minfin import'])],
              ['E', 'Link naar jaarverslag', $this->t('complete URL', [], ['context' => 'minfin import'])],
              ['F', 'Jaar', $this->t('number', [], ['context' => 'minfin import'])],
            ],
          ],
        ];
    }

    return [];
  }

}
