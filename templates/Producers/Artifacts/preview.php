<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Entity|Artifact $recordData
 */

use App\Model\Entity\Artifact;
use App\View\AppView;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;

$fields = $recordData->getAccessible();
?>

<div class="record-preview">
    <dl class="row mb-0">

        <dt class="col-sm-3 text-truncate"><?php echo __('ID') ?></dt>
        <dd class="col-sm-9"><?php echo $recordData->id ?></dd>

        <?php
        foreach ($fields as $name => $accessibility) {
            if (!$accessibility) {
                continue;
            }
            if (!isset($typeMap[$name])) {
                continue;
            }
            $skipFields = ['message_overflow'];
            if (in_array($name, $skipFields)) {
                continue;
            }

            $displayName = Inflector::humanize($name);

            $upperValues = ['Id', 'Url', 'Unc', 'UUID', 'GUID'];
            if (in_array($displayName, $upperValues)) {
                $displayName = strtoupper($displayName);
            }
            foreach ($upperValues as $value) {
                if (str_contains($displayName, $value)) {
                    $displayName = str_replace($value, strtoupper($value), $displayName);
                }
            }

            $displayValue = $recordData->{$name};
            if (empty($displayValue)) {
                $displayValue = "&nbsp;";
            } elseif ($typeMap[$name] === 'boolean') {
                $displayValue = $this->Text->boolToWord($displayValue);
            } elseif ($typeMap[$name] === 'integer') {
                $displayValue = $this->Number->format($displayValue);
            } elseif ($typeMap[$name] === 'datetime') {
                $displayValue = $this->Time->format($displayValue);
            } elseif ($typeMap[$name] === 'string') {
                $displayValue = h($displayValue);
            } elseif (!is_string($displayValue) && !is_numeric($displayValue)) {
                $displayValue = json_encode($displayValue, JSON_PRETTY_PRINT);
                $displayValue = "<pre>$displayValue</pre>";
            }

            if ($name === 'unc') {
                $displayValue = $recordData->full_unc;
            }

            if ($name === 'url') {
                $displayValue = $recordData->full_url;
            }

            if (empty($displayValue)) {
                $displayValue = "&nbsp;";
            }
            ?>

            <dt class="col-sm-3 text-truncate"><?php echo $displayName ?></dt>
            <dd class="col-sm-9" data-db-type="<?= $typeMap[$name] ?>"><?php echo $displayValue ?></dd>

            <?php
        }
        ?>

        <?php
        if (is_file($recordData->sample_unc_preview)) {
            $url = $recordData->sample_url_preview;
            $imgOpts = [
                'class' => 'artifact-preview-image border'
            ];
            ?>
            <dt class="col-sm-3 text-truncate">Image Preview</dt>
            <dd class="col-sm-9">
                <?= $this->Html->image($url, $imgOpts) ?>
            </dd>
            <?php
        }
        ?>
    </dl>
</div>


