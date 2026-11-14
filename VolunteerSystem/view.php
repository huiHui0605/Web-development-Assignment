<?php
require "config.php";
require "layout.php";
// Count all records, clamp the requested page, then fetch only its 50 rows.
$totalVolunteers = (int) database()
    ->query("SELECT COUNT(*) FROM volunteers")
    ->fetchColumn();
$totalPages = max(1, (int) ceil($totalVolunteers / PAGE_SIZE));
$currentPage = max(1, min($totalPages, (int) ($_GET["page"] ?? 1)));
$offset = ($currentPage - 1) * PAGE_SIZE;
// Bind LIMIT and OFFSET as integers; newest registrations appear first.
$statement = database()->prepare(
    "SELECT * FROM volunteers ORDER BY volunteer_id DESC LIMIT :limit OFFSET :offset",
);
$statement->bindValue(":limit", PAGE_SIZE, PDO::PARAM_INT);
$statement->bindValue(":offset", $offset, PDO::PARAM_INT);
$statement->execute();
$volunteers = $statement->fetchAll();
render_header("Volunteer directory");
?>
<div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-4">
    <div>
        <div class="eyebrow">
            PEOPLE MAKING A DIFFERENCE
        </div>
        <h1 class="mt-2">
            Our volunteers
        </h1>
        <p class="text-secondary mb-0">
            Manage registrations, availability and ways to help.
        </p>
    </div>
    <a href="index.php" class="btn btn-primary px-4">
        + Register volunteer
    </a>
</div>
<?php if (isset($_SESSION["notice"])): ?>
<div class="alert alert-success" role="status">
    <?= escape_html($_SESSION["notice"]) ?>
</div>
<?php
    unset($_SESSION["notice"]);
endif;
?>
<div class="panel p-0 overflow-hidden">
    <div class="d-flex justify-content-between p-4 border-bottom">
        <strong>
            <?= number_format($totalVolunteers) ?> registrations
        </strong>
        <span class="text-secondary">
            Latest registration first · <?= PAGE_SIZE ?> per page
        </span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <caption class="px-4">
                Volunteer directory — page <?= $currentPage ?> of <?= $totalPages ?>
            </caption>
            <thead>
                <tr>
                    <?php foreach (["ID", ...array_values(FIELD_LABELS), "Actions"] as $label): ?>
                    <th scope="col">
                        <?= escape_html($label) ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($volunteers as $volunteer): ?>
                <tr>
                    <td class="text-secondary">
                        #<?= $volunteer["volunteer_id"] ?>
                    </td>
                    <?php foreach (FIELDS as $field): ?>
                    <td>
                        <?= escape_html($volunteer[$field]) ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="edit.php?id=<?= $volunteer["volunteer_id"] ?>">
                            Edit
                        </a>
                        <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?= $volunteer["volunteer_id"] ?>">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$volunteers): ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <h2 class="h4">
                            A community starts with one person.
                        </h2>
                        <p class="text-secondary">
                            No volunteers registered yet.
                        </p>
                        <a href="index.php" class="btn btn-primary">
                            Add the first volunteer
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-4 d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <span class="small text-secondary">
            Showing <?= $totalVolunteers ? $offset + 1 : 0 ?>–<?= min($offset + PAGE_SIZE, $totalVolunteers) ?> of <?= $totalVolunteers ?>
        </span>
        <nav aria-label="Volunteer pages">
            <ul class="pagination mb-0">
                <li class="page-item <?= $currentPage === 1 ? "disabled" : "" ?>">
                    <a class="page-link" href="?page=<?= max(1, $currentPage - 1) ?>" <?= $currentPage === 1 ? 'aria-disabled="true" tabindex="-1"' : "" ?>>
                        Previous
                    </a>
                </li>
                <?php for ( $pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++ ): ?>
                <li class="page-item <?= $pageNumber === $currentPage ? "active" : "" ?>">
                    <a class="page-link" href="?page=<?= $pageNumber ?>" <?= $pageNumber === $currentPage ? 'aria-current="page"' : "" ?>>
                        <?= $pageNumber ?>
                    </a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage === $totalPages ? "disabled" : "" ?>">
                    <a class="page-link" href="?page=<?= min($totalPages, $currentPage + 1) ?>" <?= $currentPage === $totalPages ? 'aria-disabled="true" tabindex="-1"' : "" ?>>
                        Next
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<?php render_footer(); ?>
