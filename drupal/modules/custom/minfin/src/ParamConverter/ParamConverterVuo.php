<?PHP

namespace Drupal\minfin\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Param converter for vuo.
 */
class ParamConverterVuo implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if (!empty($value)) {
      if (strtolower($value) === 'verplichtingen' || strtoupper($value) === 'V') {
        return 'V';
      }
      if (strtolower($value) === 'uitgaven' || strtoupper($value) === 'U') {
        return 'U';
      }
      if (strtolower($value) === 'ontvangsten' || strtoupper($value) === 'O') {
        return 'O';
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] === 'minfin-vuo';
  }

}
