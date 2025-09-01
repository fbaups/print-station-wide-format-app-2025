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
use Cake\Error\Debugger;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error500.php');

    $this->start('file');
    ?>
    <?php if ($error instanceof Error) : ?>
    <?php $file = $error->getFile() ?>
    <?php $line = $error->getLine() ?>
    <strong>Error in: </strong>
    <?= $this->Html->link(sprintf('%s, line %s', Debugger::trimPath($file), $line), Debugger::editorUrl($file, $line)); ?>
<?php endif; ?>
    <?php
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
                echo $this->Html->image("/assets/img/illustrations/400-error-bad-request.svg", $options)
                ?>
                <h3><?= __d('cake', 'An Internal Error Has Occurred.') ?></h3>
                <p>Error Message: <?= h($message) ?></p>
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
