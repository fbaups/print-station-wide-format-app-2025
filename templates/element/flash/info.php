<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}

$onClick = 'onclick="this.classList.add(\'hidden\');this.classList.add(\'d-none\')"';
if (isset($params['clickHide'])) {
    if ($params['clickHide'] === false) {
        $onClick = '';
    }
}
?>
<div class="container-fluid px-4">
    <div class="alert alert-info message info container-fluid" <?= $onClick ?> ><?= $message ?></div>
</div>
