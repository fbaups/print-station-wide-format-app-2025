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
            <div class="ms-3"><h2 class="my-3">How To Build A Blanket Cubby House</h2></div>
        </div>
        <div class="card-body">
            <p class="lead">Transform any room into a cozy cubby house with blankets and a little creativity!</p>
            <p class="lead">Follow this simple guide to create a fun, comfy fort in your home.</p>
            <p class="lead">This setup can be customized with extra pillows, lights, and personal touches for extra
                coziness.</p>
            <p class="lead mb-5">Here are the steps to create your very own cubby house:</p>

            <h4>Step 1: Choose the Location</h4>
            <p class="lead mb-4">Pick a spot with enough room for your cubby, like the living room or a bedroom. Make
                sure you have access to chairs, sofas, or other sturdy furniture to support the blankets.</p>

            <h4>Step 2: Gather Materials</h4>
            <p class="lead mb-4">You’ll need a few large blankets or sheets, pillows, and clips or heavy objects (like
                books) to hold everything in place. Optional: string lights for a cozy touch!</p>

            <h4>Step 3: Set Up the Frame</h4>
            <p class="lead mb-4">Arrange chairs or other sturdy furniture around your chosen area to create a rough
                frame for the cubby. Position them close enough to support the blankets but leave enough room to crawl
                inside.</p>

            <h4>Step 4: Drape the Blankets</h4>
            <p class="lead mb-4">Drape blankets over the furniture to form walls and a roof. Adjust until you’re happy
                with the structure, and use heavy objects to secure the blankets if needed.</p>

            <h4>Step 5: Secure the Corners</h4>
            <p class="lead mb-4">Use clothespins or clips to secure any loose corners. This helps prevent blankets from
                slipping off, keeping the cubby house stable.</p>

            <h4>Step 6: Create an Entrance</h4>
            <p class="lead mb-4">Fold back part of a blanket to make an opening so you can get in and out easily. This
                is now the “door” of your cubby house!</p>

            <h4>Step 7: Add Cozy Touches Inside</h4>
            <p class="lead mb-4">Place pillows, cushions, and any soft blankets on the floor inside for comfort. If you
                have string lights, carefully hang them for a warm glow.</p>

            <h4>Step 8: Personalize Your Space</h4>
            <p class="lead mb-4">Bring in toys, books, or other small items to make your cubby house your own. Get
                creative with decorations for a truly personalized space!</p>

            <h4>Step 9: Enjoy Your Cubby House!</h4>
            <p class="lead mb-5">Crawl in and enjoy your cozy hideaway! Invite friends or family to join, or use it as
                your own private nook to relax and unwind.</p>

            <div class="alert alert-primary alert-icon mb-0" role="alert">
                <div class="alert-icon-aside">
                    <?php echo $this->IconMaker->bootstrapIcon('info-circle', 2) ?>
                </div>
                <div class="alert-icon-content">
                    <h5 class="alert-heading">Cubby Tip</h5>
                    For an extra cozy atmosphere, try adding a small battery-operated lamp or fairy lights inside! 
                </div>

            </div>
        </div>
    </div>
