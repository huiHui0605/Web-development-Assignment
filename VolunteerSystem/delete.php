<?php
// GET displays confirmation; only a CSRF-protected POST deletes the record.
require "config.php";
require "layout.php";
$volunteerId = volunteer_id();
$volunteer = find_volunteer($volunteerId);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_valid_post();
    $statement = database()->prepare(
        "DELETE FROM volunteers WHERE volunteer_id=?",
    );
    $statement->execute([$volunteerId]);
    redirect_with_notice("Volunteer registration deleted.");
}
render_header("Delete registration");
?>
<div class="panel mx-auto panel-delete">
    <div class="eyebrow">
        CONFIRM REMOVAL
    </div>
    <h1 class="mt-2">
        Delete registration?
    </h1>
    <p>
        This will permanently remove
        <strong>
            <?= escape_html($volunteer["volunteer_name"]) ?>
        </strong>
        from the volunteer list.
    </p>
    <form method="post" action="delete.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $volunteerId ?>">
        <a class="btn btn-outline-secondary" href="view.php">
            Keep registration
        </a>
        <button class="btn btn-danger" type="submit">
            Delete registration
        </button>
    </form>
</div>
<?php render_footer(); ?>
