<?php
require "config.php";
require "layout.php";
$volunteer = $_SESSION["registered"] ?? null;
if (!$volunteer) {
    header("Location: index.php");
    exit();
}
render_header("Registration successful");
?>
<div class="panel mx-auto panel-success">
    <div class="eyebrow">
        YOU’RE PART OF SOMETHING GOOD
    </div>
    <h1 class="mt-3">
        Volunteer Registered Successfully!
    </h1>
    <p>
        Thank you for joining us. Here are the details you submitted:
    </p>
    <dl class="row mt-4">
        <?php foreach (FIELD_LABELS as $key => $label): ?>
        <dt class="col-sm-5 py-2">
            <?= escape_html($label) ?>
        </dt>
        <dd class="col-sm-7 py-2">
            <?= escape_html($volunteer[$key]) ?>
        </dd>
        <?php endforeach; ?>
    </dl>
    <p class="text-secondary border-top pt-3">
        We’ll be in touch with more information.
    </p>
    <a href="index.php" class="btn btn-primary">
        Back to Homepage
    </a>
    <a href="view.php" class="btn btn-outline-primary">
        View volunteers
    </a>
</div>
<?php render_footer(); ?>
