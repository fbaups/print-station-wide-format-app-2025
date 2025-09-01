<?php

namespace App\View\Helper;

use Cake\View\Helper\HtmlHelper as CakeHtmlHelper;

/**
 * Html helper
 */
class ExtendedHtmlHelper extends CakeHtmlHelper
{

    public function outputProcessorFileBuilderSpan($part): string
    {
        return '<span class="prefix pointer text-primary"><small>{{' . $part . '}}</small></span>';
    }

    public function outputProcessorFileBuilderDefault(): string
    {
        return '<span class="prefix pointer text-primary"><small>{{DocumentFileName}}.{{DocumentFileExtension}}</small></span>';
    }

}
