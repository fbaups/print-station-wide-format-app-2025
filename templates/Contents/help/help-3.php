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
            <div class="ms-3"><h2 class="my-3">How To Create A Small Vegetable Garden</h2></div>
        </div>
        <div class="card-body">
            <p class="lead">Starting a small vegetable garden is a great way to enjoy fresh produce at home. Follow
                these steps to get growing!</p>
            <p class="lead">You don’t need much space to begin, just some seeds, soil, and a little sunlight.</p>
            <p class="lead">Here’s a simple process to help you create a garden that provides fresh vegetables all
                season.</p>
            <p class="lead mb-5">Let’s get started:</p>

            <h4>Step 1: Choose a Location</h4>
            <p class="lead mb-4">Pick a sunny spot with at least 6 hours of direct sunlight each day. Make sure there’s
                easy access to water.</p>

            <h4>Step 2: Prepare the Soil</h4>
            <p class="lead mb-4">Loosen the soil using a garden fork and mix in compost to add nutrients. Remove any
                weeds or rocks.</p>

            <h4>Step 3: Select Your Vegetables</h4>
            <p class="lead mb-4">Choose easy-to-grow vegetables like tomatoes, lettuce, or carrots. Consider what grows
                best in your climate.</p>

            <h4>Step 4: Plant the Seeds or Seedlings</h4>
            <p class="lead mb-4">Follow the planting instructions for each type of vegetable. Plant seeds or seedlings
                with enough space to grow.</p>

            <h4>Step 5: Water and Care for Your Garden</h4>
            <p class="lead mb-4">Water regularly, especially in dry weather. Remove weeds and check for pests to keep
                your plants healthy.</p>

            <h4>Step 6: Harvest and Enjoy</h4>
            <p class="lead mb-5">As your vegetables mature, pick them and enjoy! Regular harvesting encourages more
                growth.</p>

            <div class="alert alert-primary alert-icon mb-0" role="alert">
                <div class="alert-icon-aside">
                    <?php echo $this->IconMaker->bootstrapIcon('info-circle', 2) ?>
                </div>
                <div class="alert-icon-content">
                    <h5 class="alert-heading">Gardening Tip</h5>
                    Start small with just a few plants. This makes it easier to manage, especially if you’re new to
                    gardening!
                </div>
            </div>
        </div>
    </div>

</div>
