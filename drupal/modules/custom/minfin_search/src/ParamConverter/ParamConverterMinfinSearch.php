<?PHP

namespace Drupal\minfin_search\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Convert the 'minfin-search-int' param types.
 */
class ParamConverterMinfinSearch implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults): ?int {
    if ($value && is_numeric($value)) {
      return (int) $value;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route): bool {
    return !empty($definition['type']) && $definition['type'] === 'minfin-search-int';
  }

}
