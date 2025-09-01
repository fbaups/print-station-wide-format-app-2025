<?php

namespace App\View\Helper;

use arajcany\ToolBox\Utility\TextFormatter;
use Cake\View\Helper;

/**
 * Bootstrap-Icons helper
 */
class IconMakerHelper extends Helper
{

    /**
     * @param string $icon
     * @param int|float $size
     * @param string|null $colour
     * @param string|null $additionalClasses
     * @param string|null $additionalStyles
     * @return string
     */
    public function bootstrapIcon(string $icon, int|float $size = 1.0, string $colour = null, string $additionalClasses = null, string $additionalStyles = null): string
    {
        if (!str_starts_with($icon, 'bi-')) {
            $icon = "bi-{$icon}";
        }
        $class = "{$icon} {$additionalClasses}";

        $style = $additionalStyles ? TextFormatter::makeEndsWith($additionalStyles, ";") : '';
        if ($size) {
            $style .= "font-size: {$size}rem;";
        }
        if ($colour) {
            $style .= "color: {$colour};";
        }

        return '<i class="' . $class . '" style="' . $style . '" ></i>';
    }


    /**
     * @param string $title
     * @param string $icon
     * @param int $size
     * @param string|null $colour
     * @param string|null $additionalClasses
     * @return string
     */
    public function sideNavigationLinkIcon(string $title, string $icon, int $size = 1, string $colour = null, string $additionalClasses = null): string
    {
        $icon = $this->bootstrapIcon($icon, $size, $colour, $additionalClasses);

        return '<div class="nav-link-icon">' . $icon . '</div> ' . $title;
    }
}
