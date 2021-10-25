<?php

namespace Drupal\minfin;

/**
 * Interface for minfin related namingconventions.
 */
interface MinfinNamingServiceInterface {

  /**
   * Get the readable name for the vuo variable.
   *
   * @param string $vuo
   *   Verplichtingen, Uitgaven, Ontvangsten.
   *
   * @return string
   *   The readable name.
   */
  public function getVuoName(string $vuo): string;

  /**
   * Retrieve the readable document type name for the given type and phase.
   *
   * @param string $type
   *   The type.
   * @param string $phase
   *   The phase.
   *
   * @return string|null
   *   The document type.
   */
  public function getDocumentType(string $type, string $phase): ?string;

  /**
   * Retrieve the readable fiscal phase name for the given type and phase.
   *
   * @param string $type
   *   The type.
   * @param string $phase
   *   The phase.
   *
   * @return string|null
   *   The fiscal phase.
   */
  public function getFiscalPhase(string $type, string $phase): ?string;

  /**
   * Get the readable name for the fase variable.
   *
   * @param string $fase
   *   Fase.
   *
   * @return string
   *   The readable name.
   */
  public function getFaseName(string $fase): string;

  /**
   * Get the readable name for the isb fase variable.
   *
   * @param string $fase
   *   Fase.
   * @param int|null $jaar
   *   Jaar.
   * @param string|null $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   *
   * @return string
   *   The readable name.
   */
  public function getIsbName(string $fase, ?int $jaar = NULL, ?string $hoofdstukMinfinId = NULL): string;

  /**
   * Get the hoofdstuk name.
   *
   * @param int $jaar
   *   Jaar.
   * @param string $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param bool $prefix
   *   Prefix the hoofdstuk name with its number.
   *
   * @return string
   *   The name.
   */
  public function getHoofdstukName(int $jaar, string $hoofdstukMinfinId, bool $prefix = FALSE): string;

  /**
   * Get the artikel name.
   *
   * @param int $jaar
   *   Jaar.
   * @param string $hoofdstukMinfinId
   *   Hoofdstuk minfin id.
   * @param string $artikelMinfinId
   *   Artikel minfin id.
   *
   * @return string
   *   The name.
   */
  public function getArtikelName(int $jaar, string $hoofdstukMinfinId, string $artikelMinfinId): string;

}
