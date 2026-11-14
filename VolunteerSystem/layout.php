<?php
function render_header(string $title): void
{
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escape_html($title) ?> · <?= escape_html(BRAND_NAME) ?></title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="border-bottom bg-white">
        <nav class="container d-flex align-items-center justify-content-between py-3" aria-label="Main navigation">
            <a class="brand" href="index.php">
                <img class="brand-logo" src="images/brand/logo.png" alt="" width="60" height="50">
                <span class="brand-name"><?= escape_html(BRAND_NAME) ?></span>
            </a>
            <div class="d-flex gap-3">
                <a href="index.php">Register</a>
                <a href="view.php">Volunteers</a>
            </div>
        </nav>
    </header>
    <main class="container py-5">
<?php
}

function render_footer(): void
{
    ?>
    </main>
    <footer class="container border-top py-4 d-flex justify-content-between">
        <span>Small acts. Lasting impact.</span>
        <span><?= escape_html(BRAND_NAME) ?> · Volunteer services</span>
    </footer>
</body>
</html>
<?php
}

// Registration and editing share this form so validation and fields stay consistent.
function render_volunteer_form(array $volunteer = [], array $errors = [], bool $isEditing = false): void
{
    $textFields = [
        'volunteer_name' => ['type' => 'text', 'placeholder' => 'e.g. Aisha Tan'],
        'ic_number' => ['type' => 'text', 'placeholder' => 'e.g. 001215-14-5678'],
        'contact_number' => ['type' => 'tel', 'placeholder' => 'e.g. +60 12-345 6789'],
        'email' => ['type' => 'email', 'placeholder' => 'you@example.com'],
    ];
    ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            Please correct the highlighted fields. Your entries have been retained.
        </div>
    <?php endif; ?>

    <form action="<?= $isEditing ? 'edit.php' : 'save.php' ?>" method="post">
        <?= csrf_field() ?>
        <?php if ($isEditing): ?>
            <input type="hidden" name="id" value="<?= escape_html($volunteer['volunteer_id']) ?>">
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($textFields as $field => $options): ?>
                <div class="col-md-6">
                    <label class="form-label" for="<?= $field ?>">
                        <?= escape_html(FIELD_LABELS[$field]) ?> <span aria-hidden="true">*</span>
                    </label>
                    <input
                        class="form-control <?= isset($errors[$field]) ? 'is-invalid' : '' ?>"
                        id="<?= $field ?>"
                        name="<?= $field ?>"
                        type="<?= $options['type'] ?>"
                        placeholder="<?= escape_html($options['placeholder']) ?>"
                        maxlength="<?= FIELD_MAX_LENGTHS[$field] ?>"
                        value="<?= escape_html($volunteer[$field] ?? '') ?>"
                        aria-describedby="<?= $field ?>-help"
                        required
                    >
                    <div id="<?= $field ?>-help" class="<?= isset($errors[$field]) ? 'invalid-feedback' : 'form-text' ?>">
                        <?= escape_html($errors[$field] ?? ($field === 'ic_number' ? '12 digits, with or without hyphens.' : '')) ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="col-md-6">
                <label for="service_category" class="form-label">Service Category *</label>
                <select
                    class="form-select <?= isset($errors['service_category']) ? 'is-invalid' : '' ?>"
                    name="service_category"
                    id="service_category"
                    required
                >
                    <option value="">Choose how you’ll help</option>
                    <?php foreach (CATEGORIES as $category): ?>
                        <option value="<?= escape_html($category) ?>" <?= ($volunteer['service_category'] ?? '') === $category ? 'selected' : '' ?>>
                            <?= escape_html($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= escape_html($errors['service_category'] ?? '') ?></div>
            </div>

            <div class="col-md-6">
                <label for="available_date" class="form-label">Available Date *</label>
                <input
                    class="form-control <?= isset($errors['available_date']) ? 'is-invalid' : '' ?>"
                    type="date"
                    id="available_date"
                    name="available_date"
                    min="1000-01-01"
                    max="9999-12-31"
                    value="<?= escape_html($volunteer['available_date'] ?? '') ?>"
                    required
                >
                <div class="invalid-feedback"><?= escape_html($errors['available_date'] ?? '') ?></div>
            </div>
        </div>

        <div class="mt-4 p-3 privacy">
            Your details help our coordinators organise community activities.
            Please use this local demonstration with fictional information.
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <span class="small text-secondary">* All fields are required</span>
            <button class="btn btn-primary px-4 py-2" type="submit">
                <?= $isEditing ? 'Save changes' : 'Register as a volunteer' ?> &rarr;
            </button>
        </div>
    </form>
<?php
}
