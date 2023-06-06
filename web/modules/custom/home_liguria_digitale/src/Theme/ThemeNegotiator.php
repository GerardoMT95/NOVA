<?php
namespace Drupal\home_liguria_digitale\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Our novaliguria Theme Negotiator
 */
class ThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route = $route_match->getRouteObject();
    if (!$route instanceof Route) {
      return FALSE;
    }
    $option = $route->getOption('_custom_theme');
    if (!$option) {
      return FALSE;
    }

    return $option == 'home_liguria_digitale';
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'novaliguria';
  }
}
