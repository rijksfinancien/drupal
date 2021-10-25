<?php

// phpcs:disable Drupal.Commenting.DocComment.MissingShort
// phpcs:disable Drupal.Files.LineLength.TooLong
namespace Drupal\minfin_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The swagger API for the corona visual.
 */
class CoronaVisualApiController extends BaseApiController {

  /**
   * @SWG\Parameter(
   *   parameter = "coronaYear",
   *   name = "jaar",
   *   description = "The calendar year.",
   *   in = "path",
   *   required = true,
   *   type = "integer",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "coronaId",
   *   name = "id",
   *   description = "The identifier.",
   *   in = "path",
   *   required = true,
   *   type = "string",
   * );
   *
   * @SWG\Parameter(
   *   parameter = "coronaId2",
   *   name = "id2",
   *   description = "The second identifier.",
   *   in = "path",
   *   required = true,
   *   type = "string",
   * );
   */

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/automatische_stabilisatoren/{type}",
   *   summary = "Get the data for the corona visual: Automatische stabilisatoren.",
   *   description = "Get the data for the corona visual: Automatische stabilisatoren.",
   *   operationId = "corona_visuals_automatische_stabilisatoren",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(
   *     parameter = "coronaType",
   *     name = "type",
   *     in = "path",
   *     required = true,
   *     type = "string",
   *     enum={"inkomsten", "uitgaven", "uitsplitsing"},
   *   ),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function automatischeStabilisatoren(string $type): JsonResponse {
    $data = [
      'title' => '',
      'date' => $this->getLastUpdateDate('endogene_ontwikkelingen'),
      'description' => NULL,
      'link' => NULL,
      'children' => [],
    ];

    if ($type === 'uitsplitsing') {
      $data['title'] = 'Uitsplitsing geraamde belasting- en premieontvangsten 2020 (endogene ontwikkeling)';

      $result = $this->connection->select('mf_corona_visuals_data', 'c')
        ->fields('c', ['niveau1', 'bedrag'])
        ->condition('c.type', 'endogene_ontwikkelingen', '=')
        ->orderBy('bedrag', 'DESC')
        ->execute();
      while ($record = $result->fetchAssoc()) {
        $data['children'][] = [
          'title' => $record['niveau1'],
          'value' => (float) $record['bedrag'],
        ];
      }
    }
    elseif ($type === 'uitgaven') {
      $data['title'] = 'Automatische stabilisatoren uitgaven';

      $values = [];
      $result = $this->connection->select('mf_corona_visuals', 'c')
        ->fields('c', ['position', 'label', 'bedrag'])
        ->condition('c.type', 'automatische_stablisatoren_' . $type, '=')
        ->execute();
      while ($record = $result->fetchAssoc()) {
        $values[strtolower($record['position'])] = [
          'label' => $record['label'],
          'bedrag' => (float) $record['bedrag'],
        ];
      }

      $data['children'] = [
        [
          'identifier' => 1,
          'Gerealiseerde uitgaven WW en bijstand 2020' => $values['rechterbalk - blauw']['bedrag'],
          'Miljoenennota 2020' => $values['linkerbalk']['bedrag'],
          'extra' => '',
          'title' => 'WW- en bijstandsuitgaven',
        ],
        [
          'identifier' => 2,
          'Gerealiseerde uitgaven WW en bijstand 2020' => abs($values['rechterbalk - gestreept']['bedrag']),
          'Miljoenennota 2020' => 0,
          'extra' => 'pattern',
          'title' => 'Extra WW- en bijstandsuitgaven',
        ],
      ];
    }
    else {
      $data['title'] = 'Geraamde belasting- en premieinkomsten (endogene ontwikkeling)';

      $values = [];
      $result = $this->connection->select('mf_corona_visuals', 'c')
        ->fields('c', ['position', 'label', 'bedrag'])
        ->condition('c.type', 'automatische_stablisatoren_' . $type, '=')
        ->execute();
      while ($record = $result->fetchAssoc()) {
        $values[strtolower($record['position'])] = [
          'label' => $record['label'],
          'bedrag' => (float) $record['bedrag'],
        ];
      }

      $data['children'] = [
        [
          'identifier' => 1,
          'Geraamde belasting en premie-inkomsten financieel jaarverslag rijk (FJR) 2020' => $values['rechterbalk']['bedrag'],
          'Miljoenennota 2020' => $values['linkerbalk']['bedrag'],
          'extra' => '',
          'title' => 'WW- en bijstandsuitgaven',
        ],
        [
          'identifier' => 2,
          'Geraamde belasting en premie-inkomsten financieel jaarverslag rijk (FJR) 2020' => abs($values['rechterbalk - grijs']['bedrag'] - $values['rechterbalk - gestreept']['bedrag']),
          'Miljoenennota 2020' => 0,
          'extra' => 'pattern',
          'title' => 'Automatische stabilisatie minder inkomsten',
        ],
      ];
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/begroting_vs_realisatie/{jaar}/{hoofdstukMinfinId}'",
   *   summary = "Get the data for the corona visual: Begroting vs Realisatie.",
   *   description = "Get the data for the corona visual: Begroting vs Realisatie.",
   *   operationId = "corona_visuals_begroting_vs_realisatie",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(ref="#/parameters/coronaYear"),
   *   @SWG\Parameter(
   *     parameter = "hoofdstukMinfinId",
   *     name = "Hoofdstuk minfin id",
   *     description = "The hoofdstuk minfin identifier.",
   *     in = "path",
   *     required = true,
   *     type = "string",
   *   ),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function begrotingVsRealisatie(string $jaar, ?string $hoofdstukMinfinId = NULL) {
    $data = [
      'title' => 'Begroting vs Realisatie',
      'children' => [],
    ];

    if ($hoofdstukMinfinId !== NULL) {
      $data['title'] = $this->minfinNamingService->getHoofdstukName($jaar, $hoofdstukMinfinId);
      $data['identifier'] = 0;
      $data['backlink'] = 'Begroting vs Realisatie';

      $query = $this->connection->select('mf_b_tabel', 'bt');
      $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id AND a.jaar = bt.jaar');
      $query->addField('a', 'naam', 'title');
      $query->addExpression('SUM(bt.bedrag_begroting)', 'bedrag_begroting');
      $query->addExpression('SUM(bt.bedrag_suppletoire1)', 'bedrag_suppletoire1');
      $query->addExpression('SUM(bt.bedrag_suppletoire2)', 'bedrag_suppletoire2');
      $query->addExpression('SUM(bt.bedrag_jaarverslag)', 'bedrag_jaarverslag');
      $query->condition('bt.jaar', $jaar, '=');
      $query->condition('bt.vuo', 'U', '=');
      $query->condition('a.hoofdstuk_minfin_id', $hoofdstukMinfinId, '=');
      $query->groupBy('a.naam');
      if ($result = $query->execute()) {
        while ($record = $result->fetchAssoc()) {
          $data['children'][] = [
            'title' => $record['title'],
            'Ontwerp' => (int) $record['bedrag_begroting'],
            '1e supp.' => (int) $record['bedrag_suppletoire1'],
            '2e supp.' => (int) $record['bedrag_suppletoire2'],
            'Jaarverslag' => (int) $record['bedrag_jaarverslag'],
          ];
        }
      }
    }
    else {
      $query = $this->connection->select('mf_b_tabel', 'bt');
      $query->join('mf_hoofdstuk', 'h', 'h.hoofdstuk_id = bt.hoofdstuk_id AND h.jaar = bt.jaar');
      $query->join('mf_artikel', 'a', 'a.artikel_id = bt.artikel_id AND a.jaar = bt.jaar');
      $query->addField('h', 'hoofdstuk_minfin_id', 'identifier');
      $query->addField('h', 'naam', 'hoofdstuk');
      $query->addField('a', 'naam', 'artikel');
      $query->addExpression('SUM(bt.bedrag_begroting)', 'bedrag_begroting');
      $query->addExpression('SUM(bt.bedrag_suppletoire1)', 'bedrag_suppletoire1');
      $query->addExpression('SUM(bt.bedrag_suppletoire2)', 'bedrag_suppletoire2');
      $query->addExpression('SUM(bt.bedrag_jaarverslag)', 'bedrag_jaarverslag');
      $query->condition('bt.jaar', $jaar, '=');
      $query->condition('bt.vuo', 'U', '=');
      $query->groupBy('h.naam');
      $query->groupBy('a.naam');
      $query->groupBy('h.hoofdstuk_minfin_id');
      if ($result = $query->execute()) {
        $children = [];
        while ($record = $result->fetchAssoc()) {
          if (!isset($children[$record['identifier']])) {
            $children[$record['identifier']] = [
              'identifier' => $record['identifier'],
              'title' => $record['hoofdstuk'],
              'Ontwerp' => 0,
              '1e supp.' => 0,
              '2e supp.' => 0,
              'Jaarverslag' => 0,
              'children' => [],
            ];
          }

          $children[$record['identifier']]['children'][] = [
            'title' => $record['artikel'],
            'amount' => (int) $record['bedrag_jaarverslag'],
          ];
          $children[$record['identifier']]['Ontwerp'] += (int) $record['bedrag_begroting'];
          $children[$record['identifier']]['1e supp.'] += (int) $record['bedrag_suppletoire1'];
          $children[$record['identifier']]['2e supp.'] += (int) $record['bedrag_suppletoire2'];
          $children[$record['identifier']]['Jaarverslag'] += (int) $record['bedrag_jaarverslag'];
        }
        $data['children'] = array_values($children);
      }
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/belastinguitstel/{jaar}",
   *   summary = "Get the data for the corona visual: Belastinguitstel.",
   *   description = "Get the data for the corona visual: Belastinguitstel.",
   *   operationId = "corona_visuals_belastinguitstel",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(ref="#/parameters/coronaYear"),
   *   @SWG\Parameter(ref="#/parameters/coronaId"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function belastinguitstel(string $jaar): JsonResponse {
    $data = [
      'title' => 'Belastinguitstel',
      'date' => $this->getLastUpdateDate('belastinguitstel'),
      'children' => [],
    ];
    $data += $this->getToelichtingData('belastinguitstel', 0, 0);

    $query = $this->connection->select('mf_corona_visuals_data', 'c');
    $query->fields('c', [
      'niveau1_id',
      'niveau1',
      'niveau2',
      'bedrag',
    ]);
    $query->condition('c.type', 'belastinguitstel', '=');
    $query->condition('c.datum', $jaar, '=');
    $query->orderBy('c.bedrag', 'DESC');
    $result = $query->execute();
    while ($record = $result->fetchAssoc()) {
      $child = [
        'title' => $record['niveau1'],
        'identifier' => $record['niveau1_id'],
        'amount' => (int) $record['bedrag'],
        'value' => (int) $record['bedrag'],
      ];

      $data['children'][] = $child;
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/emu_saldo",
   *   summary = "Get the data for the corona visual: Emu-saldo.",
   *   description = "Get the data for the corona visual: Emu-saldo.",
   *   operationId = "corona_visuals_emu_saldo",
   *   tags = { "Corona visuals" },
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function emuSaldo(): JsonResponse {
    $data = [
      'title' => 'EMU-saldo in procenten BBP',
      'date' => $this->getLastUpdateDate('emu_saldo'),
      'description' => NULL,
      'link' => NULL,
      'children' => [],
    ];

    $result = $this->connection->select('mf_corona_emu', 'c')
      ->fields('c', [
        'jaar',
        'value1',
        'value3',
      ])
      ->condition('c.type', 'emu_saldo', '=')
      ->condition('c.jaar', 2015, '>=')
      ->orderBy('c.jaar', 'ASC')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $data['children'][] = [
        'title' => $record['jaar'],
        'EMU_saldo_(%)_incl correctie 1995' => (float) $record['value3'],
        'Europese grenswaarde uit SGP' => -3,
      ];
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/emu_schuld",
   *   summary = "Get the data for the corona visual: Emu schuld.",
   *   description = "Get the data for the corona visual: Emu schuld.",
   *   operationId = "corona_visuals_emu_schuld",
   *   tags = { "Corona visuals" },
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function emuSchuld(): JsonResponse {
    $data = [
      'title' => 'EMU schuld in procenten BBP en miljarden',
      'date' => $this->getLastUpdateDate('emu_schuld'),
      'description' => NULL,
      'link' => NULL,
      'children' => [],
    ];

    $result = $this->connection->select('mf_corona_emu', 'c')
      ->fields('c', [
        'jaar',
        'value1',
        'value2',
        'value3',
      ])
      ->condition('c.type', 'emu_schuld', '=')
      ->condition('c.jaar', 2015, '>=')
      ->orderBy('c.jaar', 'ASC')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $data['children'][] = [
        'title' => $record['jaar'],
        'value' => (float) $record['value2'],
        'EMU-schuld in procenten bbp' => (float) $record['value3'],
        'Europese grenswaarde uit SGP' => 60,
      ];
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/fiscalemaatregelen/{jaar}/{id}/{id2}",
   *   summary = "Get the data for the corona visual: Fiscale maatregelen.",
   *   description = "Get the data for the corona visual: Fiscale maatregelen.",
   *   operationId = "corona_visuals_fiscalemaatregelen",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(ref="#/parameters/coronaYear"),
   *   @SWG\Parameter(ref="#/parameters/coronaId"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function fiscalemaatregelen(string $jaar, ?string $id, ?string $id2): JsonResponse {
    return $this->getVisualData('Fiscale maatregelen', 'fiscalemaatregelen', $jaar, $id, $id2);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/garanties/{jaar}/{id}/{id2}",
   *   summary = "Get the data for the corona visual: Garanties.",
   *   description = "Get the data for the corona visual: Garanties.",
   *   operationId = "corona_visuals_garanties",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(ref="#/parameters/coronaYear"),
   *   @SWG\Parameter(ref="#/parameters/coronaId"),
   *   @SWG\Parameter(ref="#/parameters/coronaId2"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function garanties(string $jaar, ?string $id, ?string $id2): JsonResponse {
    return $this->getVisualData('Garanties', 'garanties', $jaar, $id, $id2);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/leningen/{jaar}/{id}/{id2}",
   *   summary = "Get the data for the corona visual: Leningen.",
   *   description = "Get the data for the corona visual: Leningen.",
   *   operationId = "corona_visuals_leningen",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(ref="#/parameters/coronaYear"),
   *   @SWG\Parameter(ref="#/parameters/coronaId"),
   *   @SWG\Parameter(ref="#/parameters/coronaId2"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function leningen(string $jaar, ?string $id, ?string $id2): JsonResponse {
    return $this->getVisualData('Leningen', 'leningen', $jaar, $id, $id2);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/tijdlijn_noodpakketten",
   *   summary = "Get the data for the corona visual: Tijdlijn noodpakketten.",
   *   description = "Get the data for the corona visual: Tijdlijn noodpakketten.",
   *   operationId = "corona_visuals_tijdlijn_noodpakketten",
   *   tags = { "Corona visuals" },
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function tijdlijnNoodpakketten(): JsonResponse {
    $data = [
      'title' => 'Tijdlijn noodpakketen',
      'date' => $this->getLastUpdateDate('tijdlijn_noodpakketten'),
      'children' => [],
    ];

    $availableDates = [];
    $children = [];
    $result = $this->connection->select('mf_corona_visuals_data', 'c')
      ->fields('c', [
        'niveau1_id',
        'niveau1',
        'niveau2',
        'datum',
        'bedrag',
      ])
      ->condition('c.type', 'tijdlijn_noodpakketten', '=')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $children[$record['niveau2']]['identifier'] = $record['niveau2'];
      $children[$record['niveau2']]['title'] = $record['niveau2'];
      $children[$record['niveau2']][$record['datum']] = (float) $record['bedrag'];
      $availableDates[$record['datum']] = $record['datum'];
    }

    foreach ($children as $k => $v) {
      foreach ($availableDates as $date) {
        if (!isset($children[$k][$date])) {
          $children[$k][$date] = 0;
        }
      }
    }

    $data['children'] = array_values($children);

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/uitgavenmaatregelen/{jaar}/{id}/{id2}",
   *   summary = "Get the data for the corona visual: Uitgavenmaatregelen.",
   *   description = "Get the data for the corona visual: Uitgavenmaatregelen.",
   *   operationId = "corona_visuals_uitgavenmaatregelen",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(ref="#/parameters/coronaYear"),
   *   @SWG\Parameter(ref="#/parameters/coronaId"),
   *   @SWG\Parameter(ref="#/parameters/coronaId2"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function uitgavenmaatregelen(string $jaar, ?string $id, ?string $id2): JsonResponse {
    return $this->getVisualData('Noodmaatregelen coronacrisis', 'uitgavenmaatregelen', $jaar, $id, $id2);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/uitgavenplafonds/{jaar}",
   *   summary = "Get the data for the corona visual: Uitgavenplafonds.",
   *   description = "Get the data for the corona visual: Uitgavenplafonds.",
   *   operationId = "corona_visuals_uitgavenplafonds",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(ref="#/parameters/coronaYear"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function uitgavenplafonds(string $jaar): JsonResponse {
    $data = [
      'title' => 'Uitgavenplafonds',
      'date' => $this->getLastUpdateDate('plafond'),
      'description' => NULL,
      'link' => NULL,
      'children' => [],
    ];

    $lijn = (float) $this->connection->select('mf_corona_visuals', 'c')
      ->fields('c', ['bedrag'])
      ->condition('c.type', 'plafond', '=')
      ->condition('c.jaar', $jaar, '=')
      ->condition('c.position', 'lijn', '=')
      ->execute()->fetchField();

    $result = $this->connection->select('mf_corona_visuals', 'c')
      ->fields('c', ['label', 'bedrag'])
      ->condition('c.type', 'plafond', '=')
      ->condition('c.jaar', $jaar, '=')
      ->condition('c.position', 'Inkomsten', '=')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $data['children'][] = [
        'title' => $record['label'],
        'inkomsten' => (float) $record['bedrag'],
        'reguliere uitgaven' => $lijn,
      ];
    }

    $result = $this->connection->select('mf_corona_visuals', 'c')
      ->fields('c', ['label', 'bedrag'])
      ->condition('c.type', 'plafond', '=')
      ->condition('c.jaar', $jaar, '=')
      ->condition('c.position', 'Plafond %', 'LIKE')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $data['children'][] = [
        'title' => $record['label'],
        'uitgaven' => (float) $record['bedrag'],
        'reguliere uitgaven' => $lijn,
      ];
    }

    $result = $this->connection->select('mf_corona_visuals', 'c')
      ->fields('c', ['label', 'bedrag'])
      ->condition('c.type', 'plafond', '=')
      ->condition('c.jaar', $jaar, '=')
      ->condition('c.position', 'Corona', '=')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $data['children'][] = [
        'title' => $record['label'],
        'uitgaven' => (float) $record['bedrag'],
        'extra' => 'pattern',
        'reguliere uitgaven' => $lijn,
      ];
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/uitgavenplafonds2/{jaar}",
   *   summary = "Get the data for the corona visual: Uitgavenplafonds 2.",
   *   description = "Get the data for the corona visual: Uitgavenplafonds 2.",
   *   operationId = "corona_visuals_uitgavenplafonds2",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(ref="#/parameters/coronaYear"),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function uitgavenplafonds2(string $jaar): JsonResponse {
    $data = [
      'title' => 'Uitgavenplafonds',
      'date' => $this->getLastUpdateDate('plafond_hoofdstukken'),
      'description' => NULL,
      'link' => NULL,
      'children' => [],
    ];

    $result = $this->connection->select('mf_corona_visuals_data', 'c')
      ->fields('c', [
        'niveau1',
        'niveau2',
        'bedrag',
      ])
      ->condition('c.type', 'plafond_hoofdstukken', '=')
      ->condition('c.datum', $jaar, '=')
      ->execute();
    while ($record = $result->fetchAssoc()) {
      $child = [
        'identifier' => $record['niveau1'],
        $record['niveau1'] => (float) $record['bedrag'],
        'title' => $record['niveau1'],
      ];
      if (strtolower($record['niveau2']) === 'corona') {
        $child['extra'] = 'pattern';
      }

      $data['children'][] = $child;
    }

    return $this->jsonResponse($data);
  }

  /**
   * @SWG\Get(
   *   path = "/json/corona_visuals/available_years/{type}",
   *   summary = "Get the available years for the corona visual.",
   *   description = "Get the available years for the corona visual.",
   *   operationId = "corona_visuals_available_years",
   *   tags = { "Corona visuals" },
   *   @SWG\Parameter(
   *     parameter = "coronaVisualType",
   *     name = "type",
   *     in = "path",
   *     required = true,
   *     type = "string",
   *     enum={"fiscalemaatregelen", "uitgavenmaatregelen"},
   *   ),
   *   @SWG\Response(response=200, ref="#/responses/bvSuccess"),
   *   @SWG\Response(response=404, ref="#/responses/bvFailure")
   * )
   */
  public function getAvailableYears(string $type): JsonResponse {
    $data = [];

    $type = strtolower($type);
    if ($type === 'plafond' || $type === 'uitgavenplafond') {
      $query = $this->connection->select('mf_corona_visuals', 'c');
      $query->distinct(TRUE);
      $query->fields('c', ['jaar']);
      $query->condition('c.type', 'plafond', '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $data[$record['jaar']] = $record['jaar'];
      }
    }
    else {
      $query = $this->connection->select('mf_corona_visuals_data', 'c');
      $query->distinct(TRUE);
      $query->fields('c', ['datum']);
      $query->condition('c.type', $type, '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $data[$record['datum']] = $record['datum'];
      }
    }

    ksort($data);
    return $this->jsonResponse($data);
  }

  /**
   * Get the visual data.
   *
   * @param string $title
   *   The title.
   * @param string $type
   *   The corona visual type.
   * @param string $jaar
   *   The year.
   * @param string|null $id
   *   The identifier.
   * @param string|null $id2
   *   The second identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json.
   */
  private function getVisualData(string $title, string $type, string $jaar, ?string $id, ?string $id2 = NULL): JsonResponse {
    $children = $this->getChildren($type, $jaar, $id, $id2);
    $data = [
      'title' => $this->getTitle($title, $type, $jaar, $id, $id2),
      'date' => $this->getLastUpdateDate($type),
      'children' => $children,
    ];
    $data += $this->getDescription($type, $jaar, $id, $id2);

    if ($backTitle = $this->getBackTitle($title, $type, $jaar, $id, $id2)) {
      $data['back_title'] = $backTitle;
    }

    if ($id2 !== NULL) {
      $data['identifier'] = $id;
    }
    elseif ($id !== NULL) {
      $data['identifier'] = 0;
    }

    return $this->jsonResponse($data);
  }

  /**
   * Get the children.
   *
   * @param string $type
   *   The corona visual type.
   * @param string $jaar
   *   The year.
   * @param string|null $id
   *   The identifier.
   * @param string|null $id2
   *   The second identifier.
   *
   * @return array
   *   An array with the children.
   */
  private function getChildren(string $type, string $jaar, ?string $id, ?string $id2 = NULL): array {
    $data = [];

    if ($id2 !== NULL) {
      $query = $this->connection->select('mf_corona_visuals_data', 'c');
      $query->fields('c', [
        'id',
        'niveau2',
        'bedrag',
      ]);
      $query->condition('c.type', $type, '=');
      $query->condition('c.datum', $jaar, '=');
      $query->condition('c.niveau1_id', $id, '=');
      $query->condition('c.id', $id2, '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $data[] = [
          'title' => $record['niveau2'],
          'amount' => (float) $record['bedrag'],
          'value' => (float) $record['bedrag'],
        ];
      }
    }
    elseif ($id !== NULL) {
      $query = $this->connection->select('mf_corona_visuals_data', 'c');
      $query->fields('c', [
        'id',
        'niveau2',
        'bedrag',
      ]);
      $query->condition('c.type', $type, '=');
      $query->condition('c.datum', $jaar, '=');
      $query->condition('c.niveau1_id', $id, '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $description = $this->connection->select('mf_corona_visuals_uitleg', 'c')
          ->fields('c', ['uitleg'])
          ->condition('c.type', $type, '=')
          ->condition('c.niveau1_id', $id, '=')
          ->condition('c.niveau2', $record['niveau2'], '=')
          ->execute()->fetchField();

        $child = [
          'title' => $record['niveau2'],
          'identifier' => $record['id'],
          'amount' => (float) $record['bedrag'],
          'value' => (float) $record['bedrag'],
        ];
        if ($description) {
          $child['description'] = (bool) $description;
        }
        $data[] = $child;
      }
    }
    else {
      $query = $this->connection->select('mf_corona_visuals_data', 'c');
      $query->fields('c', [
        'id',
        'niveau1_id',
        'niveau1',
        'niveau2',
        'bedrag',
      ]);
      $query->condition('c.type', $type, '=');
      $query->condition('c.datum', $jaar, '=');
      $result = $query->execute();
      while ($record = $result->fetchAssoc()) {
        $bedrag = (float) $record['bedrag'];

        if (!isset($data[$record['niveau1_id']])) {
          $data[$record['niveau1_id']] = [
            'title' => $record['niveau1'],
            'identifier' => $record['niveau1_id'],
            'amount' => 0,
            'value' => 0,
            'children' => [],
          ];
        }

        if (!empty($record['niveau2'])) {
          $data[$record['niveau1_id']]['children'][] = [
            'title' => $record['niveau2'],
            'identifier' => $record['id'],
            'amount' => $bedrag,
            'value' => $bedrag,
          ];
        }

        $data[$record['niveau1_id']]['amount'] += $bedrag;
        $data[$record['niveau1_id']]['value'] += $bedrag;
      }

      $data = array_values($data);
      usort($data, function ($a, $b) {
        return $b['amount'] <=> $a['amount'];
      });
    }

    return $data;
  }

  /**
   * Get the title.
   *
   * @param string $title
   *   The visual title.
   * @param string $type
   *   The corona visual type.
   * @param string $jaar
   *   The year.
   * @param string|null $id
   *   The identifier.
   * @param string|null $id2
   *   The second identifier.
   *
   * @return string|null
   *   The title.
   */
  private function getTitle(string $title, string $type, string $jaar, ?string $id, ?string $id2 = NULL): ?string {
    if ($id2 !== NULL) {
      $query = $this->connection->select('mf_corona_visuals_data', 'c');
      $query->fields('c', ['niveau2']);
      $query->condition('c.type', $type, '=');
      $query->condition('c.datum', $jaar, '=');
      $query->condition('c.id', $id2, '=');
      return $query->execute()->fetchField();
    }
    elseif ($id !== NULL) {
      $query = $this->connection->select('mf_corona_visuals_data', 'c');
      $query->fields('c', ['niveau1']);
      $query->condition('c.type', $type, '=');
      $query->condition('c.datum', $jaar, '=');
      $query->condition('c.niveau1_id', $id, '=');
      return $query->execute()->fetchField();
    }

    return $title;
  }

  /**
   * Get the description.
   *
   * @param string $type
   *   The corona visual type.
   * @param string $jaar
   *   The year.
   * @param string|null $id
   *   The identifier.
   * @param string|null $id2
   *   The second identifier.
   *
   * @return array
   *   The value for the description.
   */
  private function getDescription(string $type, string $jaar, ?string $id, ?string $id2): array {
    if ($id !== NULL && $id2 !== NULL) {
      $query = $this->connection->select('mf_corona_visuals_data', 'cvd');
      $query->join('mf_corona_visuals_uitleg', 'vu', 'cvd.niveau1_id = vu.niveau1_id AND cvd.niveau2 = vu.niveau2 AND cvd.type = vu.type');
      $query->fields('vu', ['uitleg', 'link']);
      $query->condition('cvd.type', $type, '=');
      $query->condition('cvd.datum', $jaar, '=');
      $query->condition('cvd.id', $id2, '=');
      $result = $query->execute();
      if ($record = $result->fetchAssoc()) {
        return [
          'description' => $record['uitleg'] ?: NULL,
          'link' => $record['link'] ? $record['link'] : NULL,
        ];
      }
    }

    return [
      'description' => NULL,
      'link' => NULL,
    ];
  }

  /**
   * Get the back title.
   *
   * @param string $title
   *   The visual title.
   * @param string $type
   *   The corona visual type.
   * @param string $jaar
   *   The year.
   * @param string|null $id
   *   The identifier.
   * @param string|null $id2
   *   The second identifier.
   *
   * @return string|null
   *   The back title.
   */
  private function getBackTitle(string $title, string $type, string $jaar, ?string $id, ?string $id2 = NULL): ?string {
    if ($id2 !== NULL) {
      $query = $this->connection->select('mf_corona_visuals_data', 'c');
      $query->fields('c', ['niveau1']);
      $query->condition('c.type', $type, '=');
      $query->condition('c.datum', $jaar, '=');
      $query->condition('c.niveau1_id', $id, '=');
      return $query->execute()->fetchField();
    }
    elseif ($id !== NULL) {
      return $title;
    }

    return NULL;
  }

  /**
   * Get the toelichting for the matching data.
   *
   * @param string $type
   *   The corona visual type.
   * @param string|null $idNiveau1
   *   The id of the first value.
   * @param string|null $niveau2
   *   The second value.
   *
   * @return array|null[]
   *   The toelichting.
   */
  private function getToelichtingData(string $type, ?string $idNiveau1, ?string $niveau2): array {
    if ($idNiveau1 !== NULL && $niveau2 !== NULL) {
      $result = $this->connection->select('mf_corona_visuals_uitleg', 'c')
        ->fields('c', [
          'toelichting',
          'uitleg',
          'link',
        ])
        ->condition('c.type', $type, '=')
        ->condition('c.niveau1_id', $idNiveau1, '=')
        ->condition('c.niveau2', $niveau2, '=')
        ->execute();
      if ($record = $result->fetchAssoc()) {
        return [
          'description' => $record['uitleg'],
          'link' => $record['link'],
        ];
      }
    }

    return [
      'description' => NULL,
      'link' => NULL,
    ];
  }

  /**
   * Get the last update date.
   *
   * @param string $type
   *   The corona visual type.
   *
   * @return string
   *   The last update date.
   */
  private function getLastUpdateDate(string $type) {
    $config = $this->configFactory->get('minfin_corona_visuals.last_update');
    return (string) ($config->get($type) ?? '1-1-1999');
  }

}
