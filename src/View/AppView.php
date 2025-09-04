<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\View;

use App\View\Helper\ExtendedAuthUserHelper;
use Cake\View\View;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @property \App\View\Helper\IconMakerHelper $IconMaker
 * @property \App\View\Helper\ExtendedFlashHelper $Flash
 * @property \App\View\Helper\ExtendedFormHelper $Form
 * @property \App\View\Helper\ExtendedPaginatorHelper $Paginator
 * @property \App\View\Helper\ExtendedHtmlHelper $Html
 * @property \App\View\Helper\ExtendedTextHelper $Text
 * @property \App\View\Helper\ExtendedTimeHelper $Time
 * @property ExtendedAuthUserHelper $AuthUser
 * @link https://book.cakephp.org/4/en/views.html#the-app-view
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading helpers.
     *
     * e.g. `$this->loadHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadHelper('IconMaker', ['className' => 'IconMaker']);
        $this->loadHelper('Flash', ['className' => 'ExtendedFlash']);
        $this->loadHelper('Form', ['className' => 'ExtendedForm']);
        $this->loadHelper('Paginator', ['className' => 'ExtendedPaginator']);
        $this->loadHelper('Html', ['className' => 'ExtendedHtml']);
        $this->loadHelper('Text', ['className' => 'ExtendedText']);
        if (defined('LCL_TZ')) {
            $this->loadHelper('Time', ['className' => 'ExtendedTime', 'outputTimezone' => LCL_TZ,]);
        } else {
            $this->loadHelper('Time', ['className' => 'ExtendedTime', 'outputTimezone' => 'utc',]);
        }

        $tinyAuthConfig = [
            'autoClearCache' => false,
            'multiRole' => true,
            'pivotTable ' => 'roles_users',
            'roleColumn ' => 'roles',
            'className' => 'App\View\Helper\ExtendedAuthUserHelper',
        ];
        $this->loadHelper('AuthUser', $tinyAuthConfig);
    }
}
