<?php
/**
 * @var AppView $this
 * @var array $params
 * @var string $message
 *
 * Example use of HTML and stays on screen even when clicked
 * $this->Flash->xxx(__('<strong>Some Message</strong>'), ['escape' => false, 'params' => ['clickHide' => false]]);
 */

use App\View\AppView;

$class = 'message';
if (!empty($params['class'])) {
    $class .= ' ' . $params['class'];
}
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
    <div class="<?= h($class) ?>" <?= $onClick ?> ><?= $message ?></div>
</div>
