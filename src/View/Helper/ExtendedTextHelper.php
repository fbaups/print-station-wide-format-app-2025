<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper\TextHelper as CakeTextHelper;
use Cake\View\View;

/**
 * Text helper
 */
class ExtendedTextHelper extends CakeTextHelper
{

    public function boolToWord($boolValue): string
    {
        if (in_array($boolValue, [1, '1', true, 'true'])) {
            return 'Yes';
        }

        if (in_array($boolValue, [0, '0', false, 'false'])) {
            return 'No';
        }

        return 'No';
    }

    public function boolToIcon($boolValue): string
    {

        if (in_array($boolValue, [1, '1', true, 'true'])) {
            return 'Yes';
        }

        if (in_array($boolValue, [0, '0', false, 'false'])) {
            return 'No';
        }

        return 'No';
    }

    public function tableValueTooltip($tableValue, $options = [])
    {
        if (is_array($tableValue)) {
            ob_start();
            print_r($tableValue);
            $tableValuePopOver = ob_get_clean();
            $tableValuePopOver = str_replace("Array", "", $tableValuePopOver);
        } else {
            $tableValuePopOver = $tableValue;
        }

        $tableValueTruncated = $this->truncate($tableValuePopOver, 40);

        //return "<span data-toggle=\"popover\" data-trigger=\"hover\" data-placement=\"top\" data-content=\"{$tableValuePopOver}\">{$tableValueTruncated}</span>";
        return "<span data-bs-toggle=\"tooltip\" data-bs-trigger=\"hover\" data-bs-placement=\"right\" title=\"{$tableValuePopOver}\">{$tableValueTruncated}</span>";
    }

    public function tooltipPathSyntax($path, $options = [])
    {
        $defaultOptions = [
            'placement' => 'top',
            'trigger' => 'hover', //hover or click
        ];
        $options = array_merge($defaultOptions, $options);

        $fullPath = $path;

        $path = explode("\\", $path);
        $path = array_pop($path);

        return "<span data-bs-toggle=\"tooltip\" data-bs-trigger=\"hover\" data-bs-placement=\"{$options['placement']}\" title=\"{$fullPath}\">{$path}</span>";
    }

    public function truncate(mixed $text, int $length = 100, array $options = []): string
    {
        if (is_array($text)) {
            $text = json_encode($text);
        }

        return parent::truncate($text, $length, $options);
    }


}
