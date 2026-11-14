<?php
// Create a registration using placeholders; submitted values never become SQL code.
require "config.php";
require_valid_post();
[$volunteer, $errors] = validate_volunteer_input();
if ($errors) {
    http_response_code(422);
    require "layout.php";
    render_header("Check registration");
    echo '<div class="panel mx-auto panel-form"><h1>Check your registration</h1>';
    render_volunteer_form($volunteer, $errors);
    echo "</div>";
    render_footer();
    exit();
}
try {
    $statement = database()->prepare(
        "INSERT INTO volunteers (volunteer_name,ic_number,contact_number,email,service_category,available_date) VALUES (?,?,?,?,?,?)",
    );
    $statement->execute(array_values($volunteer));
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    exit("Unable to save registration. Please try again later.");
}
// Redirect after POST so refreshing the confirmation does not insert a duplicate.
$_SESSION["registered"] = $volunteer;
header("Location: success.php", true, 303);
exit();
