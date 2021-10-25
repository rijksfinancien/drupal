<?PHP

namespace Drupal\minfin\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Param converter for phase.
 */
class ParamConverterPhase implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $allowedValues = [
      'OWB',
      'JV',
      '1SUPP',
      '2SUPP',
    ];

    if (in_array(strtoupper($value), $allowedValues)) {
      return strtoupper($value);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] === 'minfin-phase';
  }

}
