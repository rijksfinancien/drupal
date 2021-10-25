<?php

namespace Drupal\minfin_visuals\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines a route controller for redirecting the user.
 */
class RedirectController extends ControllerBase {

  /**
   * Redirect the user.
   *
   * @param string|null $jaar
   *   The jaar.
   * @param string|null $fase
   *   The fase.
   * @param string|null $vuo
   *   The vuo.
   * @param string|null $param1
   *   The first param.
   * @param string|null $param2
   *   The second param.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect.
   */
  public function redirectUser(?string $jaar, ?string $fase, ?string $vuo, ?string $param1, ?string $param2): RedirectResponse {
    $routeParams = [
      'jaar' => $jaar,
      'fase' => $this->renameFase($fase),
      'vuo' => $this->renameVuo($vuo),
    ];

    if ($hoofdstukMinfinId = $this->renameHoofdstuk($param1)) {
      $routeParams['hoofdstukMinfinId'] = $hoofdstukMinfinId;

      if ($artikelMinfinId = $this->renameArtikel($hoofdstukMinfinId, $param2)) {
        $routeParams['artikelMinfinId'] = $artikelMinfinId;
      }
    }

    return $this->redirect('minfin_visuals', $routeParams);
  }

  /**
   * Rename the fase.
   *
   * @param string|null $value
   *   The original value.
   *
   * @return string
   *   The renamed value.
   */
  private function renameFase(?string $value): string {
    $rename = [
      'owb' => 'begroting',
      'jv' => 'jaarverslag',
    ];

    return $rename[strtolower($value)] ?? 'begroting';
  }

  /**
   * Rename the vuo.
   *
   * @param string|null $value
   *   The original value.
   *
   * @return string
   *   The renamed value.
   */
  private function renameVuo(?string $value): string {
    $rename = [
      'u' => 'uitgaven',
      'o' => 'ontvangsten',
      'v' => 'verplichtingen',
    ];

    return $rename[strtolower($value)] ?? 'uitgaven';
  }

  /**
   * Rename the hoofdstuk.
   *
   * @param string|null $value
   *   The original value.
   *
   * @return string|null
   *   The renamed value.
   */
  private function renameHoofdstuk(?string $value): ?string {
    if ($value === NULL) {
      return NULL;
    }
    $rename = [
      'infrastructuurfonds' => 'A',
      'gemeentefonds' => 'B',
      'provinciefonds' => 'C',
      'diergezondheidsfonds' => 'F',
      'economische-zaken-a' => 'F',
      'diergezondheidsfonds-a' => 'F',
      'bes-fonds' => 'H',
      'bes' => 'H',
      'bes-fonds-a' => 'H',
      'de-koning' => 'I',
      'staten-generaal' => 'IIA',
      'overige-hoge-colleges-van-staat-en-kabinetten-van-de-gouverneurs' => 'IIB',
      'overige-hoge-colleges-van-staat-kabinetten-van-de-gouverneurs-en-de-kiesraad' => 'IIB',
      'overige-hoge-colleges-van-staat-en-kabinetten-van-de-gouverneurs-a' => 'IIB',
      'hoge-colleges-van-staat' => 'IIB',
      'algemene-zaken' => 'III',
      'algemene-zaken-a' => 'IIIA',
      'kabinet-van-de-koning' => 'IIIB',
      'commissie-van-toezicht-betreffende-de-inlichtingen-en-veiligheidsdiensten' => 'IIIC',
      'koninkrijksrelaties' => 'IV',
      'financien-en-nationale-schuld' => 'IX',
      'nationale-schuld' => 'IXA',
      'financien' => 'IXB',
      'financien-a' => 'IXB',
      'deltafonds' => 'J',
      'defensiematerieelfonds' => 'K',
      'diergezondheidsfonds-b' => 'LVIII',
      'buitenlandse-zaken' => 'V',
      'justitie-en-veiligheid' => 'VI',
      'veiligheid-en-justitie' => 'VI',
      'justitie-en-veiligheid-a' => 'VI',
      'binnenlandse-zaken-en-koninkrijksrelaties' => 'VII',
      'binnenlandse-zaken' => 'VII',
      'binnenlandse-zaken-en-koninkrijksrelaties-a' => 'VII',
      'onderwijs-cultuur-en-wetenschap' => 'VIII',
      'defensie' => 'X',
      'infrastructuur-en-milieu' => 'XII',
      'infrastructuur-en-waterstaat' => 'XII',
      'economische-zaken' => 'XIII',
      'economische-zaken-en-klimaat' => 'XIII',
      'landbouw-natuur-en-voedselkwaliteit' => 'XIV',
      'nationaal-groeifonds' => 'XIX',
      'sociale-zaken-en-werkgelegenheid' => 'XV',
      'volksgezondheid-welzijn-en-sport' => 'XVI',
      'buitenlandse-handel-en-ontwikkelingssamenwerking' => 'XVII',
      'wonen-en-rijksdienst' => 'XVIII',
    ];

    return $rename[strtolower($value)] ?? NULL;
  }

  /**
   * Rename the artikel.
   *
   * @param string $hoofdstukMinfinId
   *   The hoofdstuk minfin id.
   * @param string|null $value
   *   The original value.
   *
   * @return string|null
   *   The renamed value.
   */
  private function renameArtikel(string $hoofdstukMinfinId, ?string $value): ?string {
    if ($value === NULL) {
      return NULL;
    }
    $rename = [
      'A' => [
        'hoofdwegennet' => '12',
        'spoorwegen' => '13',
        'regionaal-lokale-infrastructuur' => '14',
        'hoofdvaarwegennet' => '15',
        'megaprojecten-verkeer-en-vervoer' => '17',
        'overige-uitgaven-en-ontvangsten' => '18',
        'bijdragen-andere-begrotingen-rijk' => '19',
        'verkenningen-reserveringen-en-investeringsruimte' => '20',
      ],
      'B' => [
        'gemeentefonds' => '1',
        'gemeentefonds-a' => '2',
        'gemeentefonds-b' => '3',
        'gemeentefonds-c' => '4',
      ],
      'C' => [
        'provinciefonds' => '1',
      ],
      'F' => [
        'bewaking-en-bestrijding-van-dierziekten-en-voorkomen-en-verminderen-van-welzijnsproblemen' => '1',
      ],
      'H' => [
        'bes-fonds' => '1',
      ],
      'I' => [
        'grondwettelijke-uitkering-aan-de-leden-van-het-koninklijk-huis' => '1',
        'functionele-uitgaven-van-de-koning' => '2',
        'doorbelaste-uitgaven-van-andere-begrotingen' => '3',
      ],
      'IIA' => [
        'wetgeving-en-controle-eerste-kamer' => '1',
        'uitgaven-ten-behoeve-van-leden-en-oud-leden-tweede-kamer-alsmede-leden-van-het-europees-parlement' => '2',
        'wetgeving-en-controle-tweede-kamer' => '3',
        'wetgeving-en-controle-eerste-en-tweede-kamer' => '4',
        'nominaal-en-onvoorzien' => '10',
      ],
      'IIB' => [
        'raad-van-state' => '1',
        'algemene-rekenkamer' => '2',
        'de-nationale-ombudsman' => '3',
        'kanselarij-der-nederlandse-orden' => '4',
        'kabinet-van-de-gouverneur-van-aruba' => '6',
        'kabinet-van-de-gouverneur-van-curacao' => '7',
        'kabinet-van-de-gouverneur-van-sint-maarten' => '8',
        'kiesraad' => '9',
        'nominaal-en-onvoorzien' => '10',
      ],
      'III' => [
        'eenheid-van-het-algemeen-regeringsbeleid' => '1',
        'kabinet-van-de-koning-a' => '2',
        'nominaal-en-onvoorzien' => '3',
        'kabinet-van-de-koning' => '4',
        'cie-v-toez-i-v' => '5',
      ],
      'IIIA' => [
        'eenheid-van-het-algemeen-regeringsbeleid' => '1',
        'kabinet-van-de-koning-a' => '2',
        'commissie-van-toezicht-op-de-inlichtingen-en-veiligheidsdiensten' => '3',
        'kabinet-van-de-koning' => '4',
        'commissie-van-toezicht-betreffende-de-inlichtingen-en-veiligheidsdiensten' => '5',
      ],
      'IIIB' => [
        'kabinet-van-de-koning' => '1',
        'kabinet-van-de-koning-a' => '4',
      ],
      'IIIC' => [
        'commissie-van-toezicht-betreffende-de-inlichtingen-en-veiligheidsdiensten' => '1',
        'commissie-van-toezicht-betreffende-de-inlichtingen-en-veiligheidsdiensten-a' => '5',
      ],
      'IV' => [
        'waarborgfunctie' => '1',
        'bevorderen-autonomie-koninkrijkspartners' => '2',
        'nominaal-en-onvoorzien' => '3',
        'bevorderen-sociaal-economische-structuur' => '4',
        'schuldsanering-lopende-inschrijving-leningen' => '5',
        'apparaat' => '6',
        'nominaal-en-onvoorzien-a' => '7',
        'noodhulp-en-wederopbouw-bovenwindse-eilanden' => '8',
      ],
      'IX' => [
        'belastingen' => '1',
      ],
      'IXA' => [
        'belastingen' => '1',
        'financiele-markten' => '2',
        'financieringsactiviteiten-publiek-private-sector' => '3',
        'internationale-financiele-betrekkingen' => '4',
        'exportkrediet-en-investeringsgaranties' => '5',
        'btw-compensatiefonds' => '6',
        'beheer-materiele-activa' => '7',
        'centraal-apparaat' => '8',
        'algemeen' => '9',
        'nominaal-en-onvoorzien' => '10',
        'financiering-staatsschuld' => '11',
        'kasbeheer' => '12',
      ],
      'IXB' => [
        'belastingen' => '1',
        'financiele-markten' => '2',
        'financieringsactiviteiten-publiek-private-sector' => '3',
        'internationale-financiele-betrekkingen' => '4',
        'exportkredietverzekeringen-garanties-en-investeringsverzekeringen' => '5',
        'btw-compensatiefonds' => '6',
        'beheer-materiele-activa' => '7',
        'centraal-apparaat' => '8',
        'douane' => '9',
        'nominaal-en-onvoorzien' => '10',
        'financiering-staatsschuld' => '11',
        'kasbeheer' => '12',
        'toeslagen' => '13',
        'premies-volksverzekeringen-kas' => '20',
        'premies-werknemersverzekeringen' => '21',
      ],
      'J' => [
        'investeren-in-waterveiligheid' => '1',
        'investeren-in-zoetwatervoorziening' => '2',
        'beheer-onderhoud-en-vervanging' => '3',
        'experimenteren-cf-art-iii-deltawet' => '4',
        'netwerkgebonden-kosten-en-overige-uitgaven' => '5',
        'bijdragen-t-l-v-begrotingen-hoofdstuk-xii' => '6',
        'investeren-in-waterkwaliteit' => '7',
      ],
      'K' => [
        'defensiebreed-materieel' => '1',
        'maritiem-materieel' => '2',
        'land-materieel' => '3',
        'lucht-materieel' => '4',
        'infrastructuur-en-vastgoed' => '5',
        'it' => '6',
        'bijdrage-andere-begrotingshoofdstukken-rijk' => '7',
      ],
      'LVIII' => [
        'bewaking-en-bestrijding-van-dierziekten-en-voorkomen-en-verminderen-van-welzijnsproblemen' => '1',
      ],
      'V' => [
        'versterkte-internationale-rechtsorde-en-eerbiediging-van-mensenrechten' => '1',
        'veiligheid-en-stabilitieit' => '2',
        'europese-samenwerking' => '3',
        'consulaire-belangenbehartiging-en-het-internationaal-uitdragen-van-nederlandse-waarden-en-belangen' => '4',
        'toegenomen-menselijke-ontplooiing-en-sociale-ontwikkeling' => '5',
        'nominaal-en-onvoorzien-a' => '6',
        'apparaat-a' => '7',
        'versterkt-cultureel-profiel-en-positieve-beeldvorming-nl' => '8',
        'geheim' => '9',
        'nominaal-en-onvoorzien' => '10',
        'apparaat' => '11',
        'versterkte-internationale-rechtsorde' => '41',
        'veiligheid-en-stabiliteit' => '42',
        'effectieve-europese-samenwerking' => '43',
        'consulaire-dienstverlening-en-uitdragen-nederlandse-waarden' => '44',
        'geheim-a' => '45',
        'nominaal-en-onvoorzien-b' => '46',
        'apparaat-b' => '47',
      ],
      'VI' => [
        'nationale-politie' => '31',
        'rechtspleging-en-rechtsbijstand' => '32',
        'veiligheid-en-criminaliteitsbestrijding' => '33',
        'sanctietoepassing' => '34',
        'jeugd' => '35',
        'contraterrorisme-en-nationaal-veiligheidsbeleid' => '36',
        'vreemdelingen' => '37',
        'apparaat-kerndepartement' => '91',
        'nominaal-en-onvoorzien' => '92',
        'geheim' => '93',
      ],
      'VII' => [
        'openbaar-bestuur-en-democratie' => '1',
        'aivd' => '2',
        'woningmarkt' => '3',
        'energietransitie-gebouwde-omgeving-en-bouwkwaliteit' => '4',
        'ruimtelijke-ordening-en-omgevingswet' => '5',
        'dienstverlenende-en-innovatieve-overheid' => '6',
        'arbeidszaken-overheid' => '7',
        'kwaliteit-rijksdienst' => '8',
        'uitvoering-rijksvastgoedbeleid' => '9',
        'groningen-versterken-en-perspectief' => '10',
        'centraal-apparaat' => '11',
        'algemeen' => '12',
        'nominaal-en-onvoorzien' => '13',
        'vut-fonds' => '14',
        'openbaar-bestuur-en-democratie-a' => '61',
        'algemene-inlichtingen-en-veiligheidsdienst' => '62',
        'woningmarkt-a' => '63',
        'woonomgeving-en-bouw' => '64',
        'integratie-en-maatschappelijke-samenhang' => '65',
        'dienstverlenende-en-innovatieve-overheid-a' => '66',
        'arbeidszaken-overheid-a' => '67',
        'kwaliteit-rijksdienst-a' => '68',
        'uitvoering-rijkshuisvesting' => '69',
        'vreemdelingen' => '70',
        'centraal-apparaat-a' => '71',
        'algemeen-a' => '72',
        'nominaal-en-onvoorzien-a' => '73',
        'vut-fonds-a' => '74',
      ],
      'VIII' => [
        'primair-onderwijs' => '1',
        'voortgezet-onderwijs' => '3',
        'beroepsonderwijs-en-volwasseneneducatie' => '4',
        'hoger-beroepsonderwijs' => '6',
        'wetenschappelijk-onderwijs' => '7',
        'internationaal-beleid' => '8',
        'arbeidsmarkt-en-personeelsbeleid' => '9',
        'studiefinanciering' => '11',
        'tegemoetkoming-onderwijsbijdrage-en-schoolkosten' => '12',
        'lesgelden' => '13',
        'cultuur' => '14',
        'media' => '15',
        'onderzoek-en-wetenschapsbeleid' => '16',
        'emancipatie' => '25',
        'emancipatie-a' => '26',
        'nominaal-en-onvoorzien' => '91',
        'apparaatsuitgaven' => '95',
      ],
      'X' => [
        'inzet' => '1',
        'taakuitvoering-zeestrijdkrachten' => '2',
        'taakuitvoering-landstrijdkrachten' => '3',
        'taakuitvoering-luchtstrijdkrachten' => '4',
        'taakuitvoering-marechaussee' => '5',
        'investeringen-krijgsmacht' => '6',
        'ondersteuning-krijgsmacht-door-defensie-materieel-organisatie' => '7',
        'ondersteuning-krijgsmacht-door-commandodienstencentra' => '8',
        'algemeen' => '9',
        'centraal-apparaat' => '10',
        'geheime-uitgaven' => '11',
        'nominaal-en-onvoorzien' => '12',
        'bijdrage-aan-defensiematerieelbegrotingsfonds' => '13',
      ],
      'XII' => [
        'waterkwantiteit' => '11',
        'waterkwaliteit' => '12',
        'ruimtelijke-ontwikkeling' => '13',
        'wegen-en-verkeersveiligheid' => '14',
        'openbaar-vervoer' => '15',
        'spoor' => '16',
        'luchtvaart' => '17',
        'scheepvaart-en-havens' => '18',
        'klimaat' => '19',
        'lucht-en-geluid' => '20',
        'duurzaamheid' => '21',
        'externe-veiligheid-en-risico-s' => '22',
        'meteorologie-seismologie-en-aardobservatie' => '23',
        'handhaving-en-toezicht' => '24',
        'brede-doeluitkering' => '25',
        'bijdrage-investeringsfondsen' => '26',
        'algemeen-departement' => '97',
        'apparaatsuitgaven-kerndepartement' => '98',
        'nominaal-en-onvoorzien' => '99',
      ],
      'XIII' => [
        'goed-functionerende-economie-en-markten' => '1',
        'bedrijvenbeleid-innovatief-en-duurzaam-ondernemen' => '2',
        'toekomstfonds' => '3',
        'een-doelmatige-en-duurzame-energievoorziening' => '4',
        'meerjarenprogramma-nationaal-coordinator-groningen' => '5',
        'concurrerende-duurzame-veilige-agro-visserij-en-voedselketens' => '6',
        'groen-onderwijs-van-hoge-kwaliteit' => '7',
        'natuur-en-biodiversiteit' => '8',
        'goed-functionerende-economie-en-markten-a' => '11',
        'een-sterk-innovatievermogen' => '12',
        'een-excellent-ondernemingsklimaat' => '13',
        'een-doelmatige-en-duurzame-energievoorziening-a' => '14',
        'een-doelmatige-en-duurzame-energievoorziening-b' => '15',
        'concurrerende-duurzame-veilige-agro-visserij-en-voedselketens-a' => '16',
        'groen-onderwijs-van-hoge-kwaliteit-a' => '17',
        'natuur-en-regio' => '18',
        'toekomstfonds-a' => '19',
        'nominaal-en-onvoorzien' => '40',
        'algemeen-apparaat' => '41',
        'apparaat-a' => '42',
        'apparaat' => '43',
      ],
      'XIV' => [
        'concurrerende-duurzame-veilige-agro-visserij-en-voedselketens' => '11',
        'natuur-en-biodiversiteit' => '12',
        'land-en-tuinbouw' => '21',
        'natuur-visserij-en-gebiedsgericht-werken' => '22',
        'kennis-en-innovatie' => '23',
        'uitvoering-en-toezicht' => '24',
        'apparaat' => '50',
        'nog-onverdeeld' => '51',
      ],
      'XIX' => [
        'kennisontwikkeling' => '1',
        'r-d-en-innovatie' => '2',
        'infrastructuur' => '3',
        'apparaatsuitgaven-nationaal-groeifonds' => '11',
      ],
      'XV' => [
        'arbeidsmarkt' => '1',
        'bijstand-toeslagenwet-en-sociale-werkvoorziening' => '2',
        'arbeidsongeschiktheid' => '3',
        'jonggehandicapten' => '4',
        'werkloosheid' => '5',
        'ziekte-en-zwangerschap' => '6',
        'kinderopvang' => '7',
        'oudedagsvoorziening' => '8',
        'nabestaanden' => '9',
        'tegemoetkoming-ouders' => '10',
        'uitvoeringskosten' => '11',
        'rijksbijdragen' => '12',
        'integratie-en-maatschappelijke-samenhang' => '13',
        'apparaatsuitgaven-kerndepartement' => '96',
        'aflopende-regelingen' => '97',
        'algemeen' => '98',
        'nominaal-en-onvoorzien' => '99',
      ],
      'XVI' => [
        'volksgezondheid' => '1',
        'curatieve-zorg' => '2',
        'langdurige-zorg-en-ondersteuning' => '3',
        'zorgbreed-beleid' => '4',
        'jeugd' => '5',
        'sport-en-bewegen' => '6',
        'oorlogsgetroffenen-en-herinnering-wereldoorlog-ii' => '7',
        'tegemoetkoming-specifieke-kosten' => '8',
        'algemeen' => '9',
        'apparaatsuitgaven' => '10',
        'nominaal-en-onvoorzien' => '11',
        'volksgezondheid-a' => '41',
        'gezondheidszorg' => '42',
        'langdurige-zorg' => '43',
        'maatschappelijke-ondersteuning' => '44',
        'jeugdzorg' => '45',
        'sport' => '46',
        'oorlogsgetroffenen-en-herinnering-wo-ii' => '47',
        'apparaatsuitgaven-a' => '96',
        'algemeen-a' => '97',
        'nominaal-en-onvoorzien-a' => '99',
      ],
      'XVII' => [
        'duurzame-handel-en-investeringen' => '1',
        'duurzame-ontwikkeling-voedselzekerheid-en-water' => '2',
        'sociale-vooruitgang' => '3',
        'vrede-en-veiligheid-voor-ontwikkeling' => '4',
        'versterkte-kaders-voor-ontwikkeling' => '5',
        'duurzaam-water-en-milieubeheer' => '6',
        'regulering-van-het-personenverkeer' => '7',
        'versterkt-cultureel-profiel-en-positieve-beeldvorming-nl' => '8',
        'duurzame-economische-ontwikkeling-handel-en-investeringen' => '41',
        'duurzame-ontwikkeling-voedselzekerheid-water-en-klimaat' => '42',
        'sociale-vooruitgang-a' => '43',
        'vrede-veiligheid-en-duurzame-ontwikkeling' => '44',
        'multilaterale-samenwerking-en-overige-inzet' => '45',
      ],
      'XVIII' => [
        'woningmarkt' => '1',
        'woonomgeving-en-bouw' => '2',
        'kwaliteit-rijksdienst' => '3',
        'uitvoering-rijksvastgoedbedrijf' => '6',
        'woningmarkt-a' => '11',
        'woonomgeving-en-bouw-a' => '12',
        'kwaliteit-rijksdienst-a' => '13',
        'uitvoering-rijkshuisvesting' => '14',
        'beheer-materiele-activa' => '15',
        'uitvoering-rijksvastgoedbeleid' => '16',
      ],
    ];

    return $rename[$hoofdstukMinfinId][strtolower($value)] ?? NULL;
  }

}
