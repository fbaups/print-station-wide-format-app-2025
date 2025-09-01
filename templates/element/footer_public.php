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
            ?>
            <div class="col">
                <div class="float-start small">
                    Copyright &copy; <?= COMPANY_NAME . " " . date("Y") ?>
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
