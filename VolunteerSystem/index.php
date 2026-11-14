<?php
require "config.php";
require "layout.php";

render_header("Volunteer registration");
?>
<div class="row g-5 align-items-start">
    <section class="col-lg-4">
        <div class="eyebrow">
            MAKE TIME FOR GOOD
        </div>
        <h1 class="display-4 mt-3">
            A little of your time.
            <br>
            <em>
                A world of difference.
            </em>
        </h1>
        <p class="lead mt-4">
            Join a community of people who care. Find your way to give back, one meaningful moment at a time.
        </p>
        <img class="w-100 my-4" src="images/community.svg" alt="Illustration of hands supporting a growing plant">
        <div class="d-flex gap-3">
            <span class="step">
                01
            </span>
            <div>
                <strong>
                    Tell us about yourself
                </strong>
                <p class="small text-secondary">
                    Choose a cause and a day that works for you.
                </p>
            </div>
        </div>
        <div class="d-flex gap-3">
            <span class="step">
                02
            </span>
            <div>
                <strong>
                    Make a connection
                </strong>
                <p class="small text-secondary">
                    Our coordinators will help you get involved.
                </p>
            </div>
        </div>
    </section>
    <section class="col-lg-8">
        <div class="panel">
            <span class="eyebrow">
                YOUR NEXT CHAPTER STARTS HERE
            </span>
            <h2 class="mt-2">
                Volunteer Registration Form
            </h2>
            <p class="text-secondary mb-4">
                Every helping hand makes our community stronger.
            </p>
            <?php render_volunteer_form(); ?>
        </div>
        <p class="text-center small text-secondary mt-3">
            Already registered?
            <a href="view.php">
                View volunteer registrations &rarr;
            </a>
        </p>
    </section>
</div>
<?php render_footer(); ?>
