<?php
namespace Drupal\nova_accredita\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Our nova_accredita Theme Negotiator
 */
class ThemeNegotiator implements ThemeNegotiatorInterface
{
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

        return $option == 'nova_accredita';
    }

    /**
     * {@inheritdoc}
     */
    public function determineActiveTheme(RouteMatchInterface $route_match) {
        return 'vetrina_aziende';
    }
}