<?php
/**
 * @var AppView $this
 *
 */

use App\View\AppView;
use Cake\Routing\Router;

?>
<div class="container-fluid">

    <div id="control-data" class="d-none"></div>

    <div class="content">

        <div id="tracks">
            <div id="track-05" class=""></div>
            <div id="track-04" class=""></div>
            <div id="track-03" class=""></div>
            <div id="track-02" class=""></div>
            <div id="track-01" class=""></div>
            <div id="track-timecode" class="d-none"></div>
        </div>

        <div id="track-audio" class="">
            <div id="track-audio-icons" class="" style="display: none;">
                <i id="track-audio-off" class="fa fa-volume-mute fa-4x"></i>
                <i id="track-audio-on" class="fa fa-volume-up fa-4x" style="display: none"></i>
            </div>
        </div>

        <div id="media-clips-holder" class="d-none">

        </div>

        <div class="login-button">
            <a href="<?= Router::url(['controller' => 'login',]) ?>">
                <i class="fa fa-user fa-lg"></i>
            </a>
        </div>

        <div class="exit-button">
            <a href="<?= Router::url(['controller' => 'theatre',]) ?>">
                <i class="fa fa-arrow-left fa-lg"></i>
            </a>
        </div>

    </div>

</div>

<?php
$this->start('viewCustomScripts');
echo $this->Html->script('/vendors/theatre/SlideshowPlayer.js');
$this->end();
?>

