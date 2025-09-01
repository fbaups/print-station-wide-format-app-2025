<?php
/**
 * @var AppView $this
 * @var array $services
 */

use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Load Tests'));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => false,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);

?>

<div class="container-fluid px-4">


    <div class="card mb-5">
        <div class="card-header">
            <?= __('Choose a Task') ?>
        </div>
        <div class="card-body">
            <div class="background-services index content">
                <p>
                    <?php
                    $options = [
                        'class' => "btn btn-primary btn-sm me-2",
                        'style' => "width: 200px !important"
                    ];
                    echo $this->Html->link(
                        __('Basic Performance'),
                        ['action' => 'application-performance',],
                        $options
                    );
                    ?>
                    Test the Applications basic performance. The test will call an internal URL that delivers back a
                    JSON response.

                </p>
                <p>
                    <?php
                    $url = ['action' => 'uninstall'];
                    $options = [
                        'class' => "btn btn-primary btn-sm me-2",
                        'style' => "width: 200px !important",
                    ];
                    echo $this->Html->link(
                        __('Variable URL Performance'),
                        ['action' => 'url-performance',],
                        $options
                    );
                    ?>
                    Call almost any URL (internal or external) and insert random numbers and words. Can be used to test
                    image rendering performance.
                </p>
                <p>
                    <?php
                    $url = ['action' => 'uninstall'];
                    $options = [
                        'class' => "btn btn-primary btn-sm me-2",
                        'style' => "width: 200px !important",
                    ];
                    echo $this->Html->link(
                        __('Repository Performance'),
                        ['action' => 'repository-performance',],
                        $options
                    );
                    ?>
                    Test the speed of writing to the Repository via UNC vs the local TMP path.
                </p>
            </div>
        </div>
    </div>

</div>


<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
$this->append('viewCustomScripts');
?>
<script>

</script>
<?php
$this->end();
?>


