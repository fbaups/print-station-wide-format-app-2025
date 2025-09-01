<?php
/**
 * @var AppView $this
 */

use App\View\AppView;

?>
<div class="container-xl px-4 mt-5">
    <!-- Help article-->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <a class="btn btn-transparent-dark btn-icon" href="<?= APP_LINK_HOME ?>">
                <?php echo $this->IconMaker->bootstrapIcon('arrow-left', 2) ?>
            </a>
            <div class="ms-3"><h2 class="my-3">How To Make The Perfect Omelette</h2></div>
        </div>
        <div class="card-body">
            <p class="lead">Learn how to make a delicious omelette with this step-by-step guide.</p>
            <p class="lead">Making an omelette is simple and quick. Follow these steps to create a fluffy, tasty
                breakfast.</p>
            <p class="lead">This guide will show you how to make a classic omelette that you can customize with your
                favorite ingredients.</p>
            <p class="lead mb-5">Let’s dive into the steps to create a perfect omelette:</p>

            <h4>Step 1: Prepare Your Ingredients</h4>
            <p class="lead mb-4">Gather 2-3 eggs, a pinch of salt, some pepper, and any fillings you’d like, such as
                cheese, ham, or vegetables. Whisk the eggs in a bowl with salt and pepper.</p>

            <h4>Step 2: Heat the Pan</h4>
            <p class="lead mb-4">Place a non-stick skillet over medium heat. Add a small amount of butter or oil and
                allow it to melt, coating the bottom of the pan evenly.</p>

            <h4>Step 3: Cook the Omelette</h4>
            <p class="lead mb-4">Pour the eggs into the pan. Let them sit for a few seconds, then use a spatula to
                gently stir and push the eggs toward the center until mostly set.</p>

            <h4>Step 4: Add Fillings and Fold</h4>
            <p class="lead mb-5">Sprinkle your fillings onto one half of the omelette. Once the eggs are fully set, fold
                the omelette over the fillings and slide it onto a plate. Enjoy your meal!</p>

            <div class="alert alert-primary alert-icon mb-0" role="alert">
                <div class="alert-icon-aside">
                    <?php echo $this->IconMaker->bootstrapIcon('info-circle', 2) ?>
                </div>
                <div class="alert-icon-content">
                    <h5 class="alert-heading">Cooking Tip</h5>
                    For a fluffier omelette, try adding a splash of milk or water to the eggs before whisking!
                </div>
            </div>
        </div>
    </div>
</div>
