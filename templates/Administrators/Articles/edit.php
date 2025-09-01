<?php
/**
 * @var AppView $this
 * @var Article $article
 * @var string[]|CollectionInterface $articleStatuses
 * @var int $articleStatusesDefault
 * @var CollectionInterface|string[] $roles
 * @var CollectionInterface|string[] $users
 */

use App\Model\Entity\Article;
use App\View\AppView;
use Cake\Collection\CollectionInterface;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Edit Article'));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => false,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
    'summernote' => true,
];
$this->set('coreLib', $coreLib);

?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Articles'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container px-4">
    <?= $this->Form->create($article) ?>
    <div class="card">

        <div class="card-header">
            <?= h($article->title) ?? "Article Details" ?>
        </div>

        <div class="card-body">
            <div class="articles form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    $fieldTitle = $this->Form->control('title', $opts);

                    $opts = [
                        'class' => 'form-control mb-4 d-none',
                        'data-type' => 'text',
                    ];
                    $this->Form->switchToSummernoteEditorTemplate();
                    $fieldBody = $this->Form->control('body', $opts);
                    $this->Form->switchBackTemplates();

                    $this->Form->switchToSummernoteDateRageTemplate();

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'date',
                        'type' => 'date',
                        'empty' => true,
                    ];
                    $fieldActivation = $this->Form->control('activation', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'date',
                        'type' => 'date',
                        'empty' => true,
                    ];
                    $fieldExpiration = $this->Form->control('expiration', $opts);

                    $this->Form->switchBackTemplates();

                    $priorityList = [
                        1 => 'Highest',
                        2 => 'High',
                        3 => 'Medium',
                        4 => 'Low',
                        5 => 'Lowest',

                    ];

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'integer',
                        'empty' => false,
                        'default' => 3,
                        'options' => $priorityList
                    ];
                    $fieldPriority = $this->Form->control('priority', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'integer',
                        'options' => $articleStatuses,
                        'default' => $articleStatusesDefault,
                        'empty' => false,
                    ];
                    $fieldStatus = $this->Form->control('article_status_id', $opts);

                    $opts = [
                        'type' => 'text' //must be text so form tampering does not reject
                    ];
                    $fieldFiles = $this->Form->control('files', $opts);
                    ?>

                    <div class="row">
                        <div class="col-12">
                            <?php echo $fieldTitle ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <?php echo $fieldBody ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 col-xl-6">
                            <?php echo $fieldActivation ?>
                            <span class="mx-4">To</span>
                            <?php echo $fieldExpiration ?>
                        </div>
                        <div class="col-sm-12 col-xl-3">
                            <?php echo $fieldPriority ?>
                        </div>
                        <div class="col-sm-12 col-xl-3">
                            <?php echo $fieldStatus ?>
                        </div>
                    </div>

                </fieldset>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => 'articles'], $options);

                $options = [
                    'class' => 'btn btn-primary'
                ];
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
        </div>

    </div>
    <?= $this->Form->end() ?>
</div>

<?php
include("summernote.php");
?>

