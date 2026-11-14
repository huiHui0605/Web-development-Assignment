<?php
// Update only the chosen record after server validation and CSRF verification.
require "config.php";
require "layout.php";
$volunteerId = volunteer_id();
$volunteer = find_volunteer($volunteerId);
$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_valid_post();
    [$volunteer, $errors] = validate_volunteer_input();
    $volunteer["volunteer_id"] = $volunteerId;
    if (!$errors) {
        $statement = database()->prepare(
            "UPDATE volunteers SET volunteer_name=?,ic_number=?,contact_number=?,email=?,service_category=?,available_date=? WHERE volunteer_id=?",
        );
        $statement->execute([
            ...array_map(fn($field) => $volunteer[$field], FIELDS),
            $volunteerId,
        ]);
        redirect_with_notice("Volunteer details updated successfully.");
    }
    http_response_code(422);
}
render_header("Edit volunteer");
?>
<div class="panel mx-auto panel-form">
    <a href="view.php">
        &larr; Back to volunteers
    </a>
    <h1 class="mt-3">
        Edit volunteer
    </h1>
    <p class="text-secondary">
        Keep availability and contact details up to date.
    </p>
    <?php render_volunteer_form($volunteer, $errors, true); ?>
</div>
<?php render_footer(); ?>
