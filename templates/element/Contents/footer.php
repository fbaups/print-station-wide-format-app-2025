<?php
/**
 * @var AppView $this
 */

use App\View\AppView;

?>
<footer class="footer-admin mt-auto footer-light">
    <div class="container-xl px-4">
        <div class="row">
            <?php
            if (!defined('COMPANY_NAME')) {
                define('COMPANY_NAME', '');
            }
            if (empty(COMPANY_NAME)) {
                $companyName = '';
            } elseif (COMPANY_NAME === 'company_name') {
                $link = ['prefix' => 'Administrators', 'controller' => 'Settings', 'action' => 'edit-group', 'company'];
                $companyName = $this->AuthUser->link('{Edit Company Details}', $link);
            } else {
                $companyName = COMPANY_NAME;
            }
            ?>
            <div class="col">
                <div class="float-start small">
                    Copyright &copy; <?= $companyName . " " . date("Y") ?>
                </div>
                <div class="float-end small">
                    Powered by
                    <?php
                    $options = [
                        'class' => "powered-by-fujifilm-logo",
                        'style' => "",
                        'alt' => APP_NAME . ' Dashboard',
                    ];
                    echo $this->Html->image("/img/fujifilm_basic.svg", $options)
                    ?>
                    Professional Services
                </div>
            </div>
        </div>
    </div>
</footer>
