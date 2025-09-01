<?php
/**
 * @var AppView $this
 */

use App\View\AppView;
use Cake\Routing\Router;

?>
<div class="container-fluid">

    <div id="control-data" class="d-none"></div>

    <div class="content">

        <div class="theatre-header">
            <h1><?= APP_NAME ?> Theatre</h1>
        </div>

        <div class="video-gallery">

            <div class="gallery-item ">
                <div class="gallery-item-caption">
                    <h2 class="text-light">Slideshow</h2>
                    <p></p>
                    <?php
                    echo $this->Html->link('', ['controller' => 'theatre', 'action' => 'slideshow-player'], ['class' => ''])
                    ?>
                </div>
            </div>

            <div class="gallery-item ">
                <div class="gallery-item-caption">
                    <h2 class="text-light">Media Clips</h2>
                    <p></p>
                    <?php
                    echo $this->Html->link('', ['controller' => 'theatre', 'action' => 'media-clip-player'], ['class' => ''])
                    ?>
                </div>
            </div>

        </div>

        <div class="login-button">
            <a href="<?= Router::url(['controller' => 'login',]) ?>">
                <i class="fa fa-user fa-lg"></i>
            </a>
        </div>
    </div>

</div>
