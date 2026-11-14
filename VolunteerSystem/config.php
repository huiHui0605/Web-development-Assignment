<?php
declare(strict_types=1);
session_set_cookie_params([
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => !empty($_SERVER["HTTPS"]),
]);
session_start();
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store");
function escape_html($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
// Reuse one PDO connection per request. Native prepared statements keep values separate from SQL.
function database(): PDO
{
    static $connection;
    if (!$connection) {
        try {
            $connection = new PDO(
                "mysql:host=" .
                    (getenv("DB_HOST") ?: "127.0.0.1") .
                    ";dbname=volunteer_db;charset=utf8mb4",
                getenv("DB_USER") ?: "root",
                getenv("DB_PASSWORD") ?: "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ],
            );
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            http_response_code(503);
            exit(
                "Database unavailable. Check database configuration and import database.sql."
            );
        }
    }
    return $connection;
}
const CATEGORIES = [
    "Community Outreach",
    "Environmental Care",
    "Food Distribution",
    "Education Support",
    "Elderly Care",
    "Animal Welfare",
];
const BRAND_NAME = "The Heart community hub";
const PAGE_SIZE = 50;
// Keep labels and field order shared by forms, confirmation, and the directory.
const FIELD_LABELS = [
    "volunteer_name" => "Volunteer Name",
    "ic_number" => "IC Number",
    "contact_number" => "Contact Number",
    "email" => "Email Address",
    "service_category" => "Service Category",
    "available_date" => "Available Date",
];
const FIELD_MAX_LENGTHS = [
    "volunteer_name" => 100,
    "ic_number" => 20,
    "contact_number" => 20,
    "email" => 100,
    "service_category" => 100,
];
const FIELDS = [
    "volunteer_name",
    "ic_number",
    "contact_number",
    "email",
    "service_category",
    "available_date",
];
// A random session token ties every modifying form to the browser that opened it.
$_SESSION["csrf"] ??= bin2hex(random_bytes(32));
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' .
        escape_html($_SESSION["csrf"]) .
        '">';
}
// Only POST requests with a matching CSRF token may create, update, or delete records.
function require_valid_post(): void
{
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Allow: POST");
        http_response_code(405);
        exit("POST required.");
    }
    if (
        !is_string($_POST["csrf"] ?? null) ||
        !hash_equals($_SESSION["csrf"], $_POST["csrf"])
    ) {
        http_response_code(403);
        exit("Invalid session token. Reload the form.");
    }
}
// Validate on the server as well as in HTML; retain submitted values when showing errors.
function validate_volunteer_input(): array
{
    $volunteer = [];
    $errors = [];
    foreach (FIELDS as $field) {
        $volunteer[$field] = is_string($_POST[$field] ?? null)
            ? trim($_POST[$field])
            : "";
        if ($volunteer[$field] === "") {
            $errors[$field] = "This field is required.";
        }
    }
    foreach (FIELD_MAX_LENGTHS as $field => $maxLength) {
        if (strlen($volunteer[$field]) > $maxLength) {
            $errors[$field] = "Maximum " . $maxLength . " bytes.";
        }
    }
    if (
        $volunteer["ic_number"] !== "" &&
        !preg_match(
            '/^(?:[0-9]{12}|[0-9]{6}-[0-9]{2}-[0-9]{4})$/D',
            $volunteer["ic_number"],
        )
    ) {
        $errors["ic_number"] = "Use 12 digits or YYMMDD-SS-NNNN.";
    }
    if (
        $volunteer["contact_number"] !== "" &&
        !preg_match(
            '/^\+?[0-9][0-9 ()-]{6,19}$/D',
            $volunteer["contact_number"],
        )
    ) {
        $errors["contact_number"] =
            "Enter a valid contact number (7–20 characters).";
    }
    if (!filter_var($volunteer["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email address.";
    }
    if (!in_array($volunteer["service_category"], CATEGORIES, true)) {
        $errors["service_category"] = "Choose a listed category.";
    }
    // Round-trip the date because PHP can otherwise normalise impossible dates.
    $date = preg_match(
        '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D',
        $volunteer["available_date"],
    )
        ? DateTimeImmutable::createFromFormat(
            "!Y-m-d",
            $volunteer["available_date"],
        )
        : false;
    if (
        !$date ||
        $date->format("Y-m-d") !== $volunteer["available_date"] ||
        $volunteer["available_date"] < "1000-01-01"
    ) {
        $errors["available_date"] = "Choose a valid date.";
    }
    return [$volunteer, $errors];
}
function volunteer_id(): int
{
    $volunteerId = filter_var(
        $_GET["id"] ?? ($_POST["id"] ?? null),
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]],
    );
    if (!$volunteerId) {
        http_response_code(400);
        exit("Invalid volunteer ID.");
    }
    return $volunteerId;
}
// Read one record with a bound ID; missing records return HTTP 404.
function find_volunteer(int $volunteerId): array
{
    $statement = database()->prepare(
        "SELECT * FROM volunteers WHERE volunteer_id=?",
    );
    $statement->execute([$volunteerId]);
    $volunteer = $statement->fetch();
    if (!$volunteer) {
        http_response_code(404);
        exit("Volunteer not found.");
    }
    return $volunteer;
}
function redirect_with_notice(string $message): never
{
    $_SESSION["notice"] = $message;
    header("Location: view.php", true, 303);
    exit();
}
