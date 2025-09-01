<?php
/**
 * @var \App\View\AppView $this
 * @var array $user
 * @var string $header
 * @var string $message
 */
?>

<div class="container-xl px-4">
    <div class="row justify-content-center">
        <div class="col-lg">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-body">

                    <?= $this->Flash->render() ?>
                    <h1 class="mt-3"><?= $header ?></h1>
                    <p class="text-muted">
                        <?= $message ?>
                        <br>
                        <br>
                        Go to the
                        <?= $this->Html->link('forgot password', ['controller' => 'forgot']) ?>
                        page to get a new reset link.
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>
