<?php

namespace Drupal\minfin;

/**
 * Add a helper functions for a kamerstuk form.
 */
trait KamerstukFormTrait {

  /**
   * Get a list with the available types.
   *
   * @return array
   *   A list with the available types.
   */
  protected function getAvailableTypes() {
    return [
      'miljoenennota' => 'miljoenennota',
      'miljoenennota (bijlage)' => 'miljoenennota',
      'voorjaarsnota' => 'voorjaarsnota',
      'najaarsnota' => 'najaarsnota',
      'belastingplan (vvw)' => 'belastingplan_voorstel_van_wet',
      'belastingplan (mvt)' => 'belastingplan_memorie_van_toelichting',
      'belastingplan (sb)' => 'belastingplan_staatsblad',
      'financieel jaarverslag' => 'financieel_jaarverslag',
      'financieel jaarverslag (bijlage)' => 'financieel_jaarverslag',
      'sw' => 'voorstel_van_wet',
      'sw (mvt)' => 'memorie_van_toelichting',
      'owb' => 'memorie_van_toelichting',
      'owb (wet)' => 'voorstel_van_wet',
      'jv' => 'jaarverslag',
      '1supp' => 'memorie_van_toelichting',
      '1supp (wet)' => 'voorstel_van_wet',
      '2supp' => 'memorie_van_toelichting',
      '2supp (wet)' => 'voorstel_van_wet',
      'isb (mvt)' => 'isb_memorie_van_toelichting',
      'isb (wet)' => 'isb_voorstel_van_wet',
    ];
  }

  /**
   * Maps the given type to the real type.
   *
   * @param string $type
   *   The given type.
   *
   * @return string
   *   The real type.
   */
  protected function getRealType(string $type): string {
    $type = trim(strtolower($type));
    if (in_array(substr($type, -11), ['e isb (mvt)', 'e isb (wet)'])) {
      $type = substr($type, -9);
    }

    return $this->getAvailableTypes()[$type] ?? 'undefined';
  }

  /**
   * Maps the given phase to the real phase.
   *
   * @param string $phase
   *   The given phase.
   *
   * @return string
   *   The real phase.
   */
  protected function getRealPhase(string $phase): string {
    $phase = strtolower($phase);
    switch ($phase) {
      case 'miljoenennota':
      case 'miljoenennota (bijlage)':
      case 'belastingplan (vvw)':
      case 'belastingplan (mvt)':
      case 'belastingplan (sb)':
      case 'owb (wet)':
        return 'OWB';

      case 'voorjaarsnota':
      case '1supp (wet)':
        return '1SUPP';

      case 'najaarsnota':
      case '2supp (wet)':
        return '2SUPP';

      case 'financieel jaarverslag':
      case 'financieel jaarverslag (bijlage)':
      case 'financiel jaarverslag':
      case 'financiel jaarverslag (bijlage)':
      case 'sw':
      case 'sw (mvt)':
        return 'JV';
    }

    if (in_array(substr($phase, -11), ['e isb (mvt)', 'e isb (wet)'])) {
      return 'ISB' . substr($phase, 0, -11);
    }

    return strtoupper($phase);
  }

  /**
   * Checks if the kamerstuk type is an appendix or not.
   *
   * @param string $type
   *   The type.
   *
   * @return bool
   *   A boolean indication if the kamerstuk is an appendix.
   */
  protected function isKamerstukAppendix(string $type): bool {
    return substr($type, -10) === ' (bijlage)';
  }

}
