<?php

/**
 * @file
 * Provide views data for the minfin.module.
 *
 * @ingroup views_module_handlers
 */

/**
 * Implements hook_views_data().
 */
function minfin_views_data() {
  $data = [];

  $data['mf_artikel'] = [
    'table' => [
      'group' => 'Artikel',
      'join' => [
        'mf_beleidsevaluatie' => [
          'field' => 'artikel_minfin_id',
          'left_field' => 'artikel_minfin_id',
          'extra' => [
            [
              'field' => 'hoofdstuk_minfin_id',
              'left_field' => 'hoofdstuk_minfin_id',
            ],
            [
              'field' => 'jaar',
              'left_field' => 'jaar',
            ],
          ],
        ],
      ],
    ],
    'jaar' => [
      'title' => 'Jaar',
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'hoofdstuk_minfin_id' => [
      'title' => 'Hoofdstuk id',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'hoofdstuk',
      ],
    ],
    'artikel_minfin_id' => [
      'title' => 'Artikel id',
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'naam' => [
      'title' => 'Naam Artikel',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'omschrijving' => [
      'title' => 'Omschrijving',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
  ];

  $data['mf_beleidsevaluatie'] = [
    'table' => [
      'group' => 'Beleidsevaluaties',
      'base' => [
        'field' => 'beleidsevaluatie_id',
        'title' => 'Beleidsevaluaties',
      ],
    ],
    'beleidsevaluatie_id' => [
      'title' => 'Id',
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'jaar' => [
      'title' => 'Jaar',
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'hoofdstuk_minfin_id' => [
      'title' => 'Hoofdstuk id',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'hoofdstuk',
      ],
    ],
    'artikel_minfin_id' => [
      'title' => 'Artikel id',
      'field' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'doel' => [
      'title' => 'Doel',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'soort' => [
      'title' => 'Soort',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'thema' => [
      'title' => 'Thema',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'type' => [
      'title' => 'Type',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'betrokken_partijen' => [
      'title' => 'Betrokken partijen',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'onafhankelijke_deskundige' => [
      'title' => 'Onafhankelijke deskundige',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'status' => [
      'title' => 'Status',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'opleverdatum' => [
      'title' => 'Opleverdatum',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'naam_rapport' => [
      'title' => 'Naam rapport',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'hoofdrapport' => [
      'title' => 'Hoofdrapport',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'aankondiging' => [
      'title' => 'Aankondiging/Taakopdracht',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'kabinetsreactie_aanbiedingsbrief' => [
      'title' => 'Kabinetsreactie/Aanbiedingsbrief',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'open_data' => [
      'title' => 'Open data',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'samenvatting' => [
      'title' => 'Samenvatting',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
  ];

  $data['mf_hoofdstuk'] = [
    'table' => [
      'group' => 'Hoofdstuk',
      'join' => [
        'mf_beleidsevaluatie' => [
          'field' => 'hoofdstuk_minfin_id',
          'left_field' => 'hoofdstuk_minfin_id',
          'extra' => [
            [
              'field' => 'jaar',
              'left_field' => 'jaar',
            ],
          ],
        ],
      ],
    ],
    'jaar' => [
      'title' => 'Jaar',
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
    'hoofdstuk_minfin_id' => [
      'title' => 'Hoofdstuk id',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'hoofdstuk',
      ],
    ],
    'naam' => [
      'title' => 'Naam hoofdstuk',
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ],
  ];

  return $data;
}
