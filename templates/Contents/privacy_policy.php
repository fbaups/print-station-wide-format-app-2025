<?php
/**
 * @var AppView $this
 */

use App\View\AppView;

?>
<div class="container-xl px-4 mt-5">
    <!-- Privacy Policy article-->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <a class="btn btn-transparent-dark btn-icon" href="<?= APP_LINK_HOME ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-arrow-left">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div class="ms-3"><h2 class="my-3">Privacy Policy</h2></div>
        </div>
        <div class="card-body">
            <p class="lead">Here's an example of what an article in your knowledge base will look like.</p>
            <p class="lead">You can use the paragraph element along with other typography elements and text tools to
                create articles within your knowledge base. This is a great way to display support information to your
                users.</p>
            <p class="lead">The knowledge base page examples are a great starting point for creating FAQ's,
                documentation, and more. In this section, we're using the lead paragraph class to make the text a bit
                larger so it's more legible since it's meant to be read in sentences.</p>
            <p class="lead mb-5">You can also use step-by-step examples in this section:</p>
            <h4>Step 1: Start the process</h4>
            <p class="lead mb-4">Here is some example text of a longer version of a first step. This format is great
                when you need to explain things more, not just using a ordered list.</p>
            <h4>Step 2: Continue doing something</h4>
            <p class="lead mb-4">We're using built-in elements and text utilities to make this list more legible and
                understandable. This is a great starting point for a step by step guide within your knowledge base
                article.</p>
            <h4>Step 3: Finish the process</h4>
            <p class="lead mb-5">We've used spacing utilities, text utilities, and other components within this article
                example. This is just an example layout of a few things you can do within an article page. Thanks for
                reading!</p>
            <div class="alert alert-primary alert-icon mb-0" role="alert">
                <div class="alert-icon-aside">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         class="feather feather-alert-triangle">
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div class="alert-icon-content">
                    <h5 class="alert-heading">Article Alert</h5>
                    If there is something in your article that you really want to emphasize, use the alert component, or
                    our custom icon alert component like this one here!
                </div>
            </div>
        </div>
    </div>
</div>
