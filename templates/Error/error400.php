<?php
/**
 * @var AppView $this
 * @var StatementInterface $error
 * @var string $message
 * @var string $url
 */

use App\View\AppView;
use Cake\Core\Configure;
use Cake\Database\StatementInterface;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error400.php');

    $this->start('file');
    echo $this->element('auto_table_warning');
    $this->end();
endif;
?>

<div class="container-xl px-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center mt-4">
                <?php
                $options = [
                    'class' => "img-fluid p-4",
                    'alt' => '',
                ];
                echo $this->Html->image("/assets/img/illustrations/500-internal-server-error.svg", $options)
                ?>
                <h3><?= h($message) ?></h3>
                <p><?= __d('cake', 'The requested address {0} was not found on this server.', "<strong>'{$url}'</strong>") ?></p>
                <?php
                $link = $this->IconMaker->bootstrapIcon('arrow-left', additionalClasses: 'ms-0 me-1') . ' Return to Dashboard';
                $url = ['controller' => '/', 'action' => ''];
                $options = [
                    'class' => 'text-arrow-icon',
                    'escape' => false
                ];
                echo $this->Html->link($link, $url, $options);
                ?>
            </div>
        </div>
    </div>
</div>
