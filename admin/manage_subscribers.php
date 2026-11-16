<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: auth/login.php");
    exit;
}

include '../includes/db.php';
include '../mailer.php';

$page_title = 'Manage Subscribers - PSA Admin';

$emailSent = false;
$emailError = '';
$bulkMessage = '';
$bulkError = '';

/*
|--------------------------------------------------------------------------
| FILTER
|--------------------------------------------------------------------------
| all      = Show all subscribers
| approved = Show approved subscribers
| pending  = Show unapproved subscribers
*/
$statusFilter = $_GET['status'] ?? 'all';

if (!in_array($statusFilter, ['all', 'approved', 'pending'], true)) {
    $statusFilter = 'all';
}

/*
|--------------------------------------------------------------------------
| BULK ACTIONS
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {

    $bulkAction = $_POST['bulk_action'];
    $selectedIds = $_POST['subscriber_ids'] ?? [];

    // Make sure we have an array
    if (!is_array($selectedIds)) {
        $selectedIds = [];
    }

    // Clean IDs
    $selectedIds = array_filter(
        array_map('intval', $selectedIds),
        function ($id) {
            return $id > 0;
        }
    );

    if (empty($selectedIds)) {
        $bulkError = 'Please select at least one subscriber.';
    } elseif (!in_array($bulkAction, ['approve', 'delete'], true)) {
        $bulkError = 'Invalid bulk action.';
    } else {

        /*
        |--------------------------------------------------------------------------
        | BULK APPROVE
        |--------------------------------------------------------------------------
        */
        if ($bulkAction === 'approve') {

            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $types = str_repeat('i', count($selectedIds));

            $sql = "UPDATE subscribers 
                    SET approved = 1 
                    WHERE id IN ($placeholders)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param($types, ...$selectedIds);

                if ($stmt->execute()) {
                    $count = $stmt->affected_rows;
                    $bulkMessage = "$count subscriber(s) approved successfully.";
                } else {
                    $bulkError = 'Unable to approve the selected subscribers.';
                }

                $stmt->close();
            } else {
                $bulkError = 'Database error while approving subscribers.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | BULK DELETE
        |--------------------------------------------------------------------------
        */
        if ($bulkAction === 'delete') {

            $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
            $types = str_repeat('i', count($selectedIds));

            $sql = "DELETE FROM subscribers 
                    WHERE id IN ($placeholders)";

            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param($types, ...$selectedIds);

                if ($stmt->execute()) {
                    $count = $stmt->affected_rows;
                    $bulkMessage = "$count subscriber(s) deleted successfully.";
                } else {
                    $bulkError = 'Unable to delete the selected subscribers.';
                }

                $stmt->close();
            } else {
                $bulkError = 'Database error while deleting subscribers.';
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| EMAIL SENDING
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {

    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $sendTo = $_POST['send_to'] ?? 'approved';

    if (empty($subject) || empty($message)) {

        $emailError = 'Subject and message are required.';

    } else {

        $query = "SELECT email, name FROM subscribers";

        if ($sendTo === 'approved') {
            $query .= " WHERE approved = 1";
        }

        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {

            $sentCount = 0;
            $failCount = 0;

            while ($sub = $result->fetch_assoc()) {

                $name = $sub['name'] ?? 'Subscriber';

                $personalizedMessage =
                    "Hi " . $name . ",\n\n" .
                    $message .
                    "\n\nPSA Malawi Team";

                if (sendEmail($sub['email'], $subject, $personalizedMessage)) {
                    $sentCount++;
                } else {
                    $failCount++;
                }
            }

            if ($failCount === 0) {
                $emailSent = true;
            } else {
                $emailError = "Sent to $sentCount subscribers. Failed for $failCount.";
            }

        } else {

            $emailError = 'No subscribers found to send to.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| GET SUBSCRIBERS BASED ON FILTER
|--------------------------------------------------------------------------
*/
if ($statusFilter === 'approved') {

    $subscribers = $conn->query(
        "SELECT * FROM subscribers 
         WHERE approved = 1 
         ORDER BY id DESC"
    );

} elseif ($statusFilter === 'pending') {

    $subscribers = $conn->query(
        "SELECT * FROM subscribers 
         WHERE approved = 0 
         ORDER BY id DESC"
    );

} else {

    $subscribers = $conn->query(
        "SELECT * FROM subscribers 
         ORDER BY id DESC"
    );
}

include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 ml-0 md:ml-64 min-h-screen">

    <!-- Top Header Bar -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">

        <div class="flex items-center justify-between px-6 py-4">

            <div class="flex items-center gap-4">

                <button
                    id="menu-toggle"
                    class="md:hidden text-gray-600 hover:text-gray-900"
                >
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <div>
                    <h1 class="text-xl font-bold text-gray-800">
                        Manage Subscribers
                    </h1>

                    <p class="text-sm text-gray-500">
                        View and manage newsletter subscribers
                    </p>
                </div>

            </div>

        </div>

    </header>


    <div class="p-6 space-y-6">

        <!-- Email All Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                Send Email to Subscribers
            </h2>

            <?php if ($emailSent): ?>

                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    Email sent successfully to all selected subscribers!
                </div>

            <?php endif; ?>


            <?php if ($emailError): ?>

                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($emailError) ?>
                </div>

            <?php endif; ?>


            <form method="POST" class="space-y-4">

                <div>

                    <label class="block mb-1 font-semibold text-gray-700">
                        Subject
                    </label>

                    <input
                        type="text"
                        name="subject"
                        required
                        class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"
                    >

                </div>


                <div>

                    <label class="block mb-1 font-semibold text-gray-700">
                        Message
                    </label>

                    <textarea
                        name="message"
                        rows="6"
                        required
                        class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"
                    ></textarea>

                </div>


                <div>

                    <label class="block mb-1 font-semibold text-gray-700">
                        Send To
                    </label>

                    <select
                        name="send_to"
                        class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-psaGreen"
                    >

                        <option value="approved">
                            Approved Subscribers Only
                        </option>

                        <option value="all">
                            All Subscribers
                        </option>

                    </select>

                </div>


                <button
                    type="submit"
                    name="send_email"
                    class="bg-psaGreen hover:bg-green-800 text-white px-6 py-2 rounded transition"
                >
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Email
                </button>

            </form>

        </div>


        <!-- Bulk Action / Subscriber List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

            <!-- Header -->
            <div class="p-5 border-b border-gray-200">

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-800">
                            Subscribers
                        </h2>

                        <p class="text-sm text-gray-500">
                            Select subscribers to approve or delete them in bulk.
                        </p>

                    </div>


                    <!-- Filter -->
                    <div class="flex items-center gap-2">

                        <label
                            for="statusFilter"
                            class="text-sm font-semibold text-gray-700"
                        >
                            Filter:
                        </label>

                        <select
                            id="statusFilter"
                            onchange="filterSubscribers(this.value)"
                            class="border border-gray-300 px-3 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-psaGreen"
                        >

                            <option
                                value="all"
                                <?= $statusFilter === 'all' ? 'selected' : '' ?>
                            >
                                All Subscribers
                            </option>

                            <option
                                value="approved"
                                <?= $statusFilter === 'approved' ? 'selected' : '' ?>
                            >
                                Approved
                            </option>

                            <option
                                value="pending"
                                <?= $statusFilter === 'pending' ? 'selected' : '' ?>
                            >
                                Unapproved / Pending
                            </option>

                        </select>

                    </div>

                </div>

            </div>


            <!-- Bulk Messages -->
            <?php if ($bulkMessage): ?>

                <div class="mx-5 mt-5 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($bulkMessage) ?>
                </div>

            <?php endif; ?>


            <?php if ($bulkError): ?>

                <div class="mx-5 mt-5 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($bulkError) ?>
                </div>

            <?php endif; ?>


            <!-- Bulk Action Form -->
            <form
                method="POST"
                id="bulkForm"
                onsubmit="return confirmBulkAction();"
            >

                <!-- Bulk Controls -->
                <div class="p-4 bg-gray-50 border-b border-gray-200">

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                        <div class="flex items-center gap-3">

                            <label class="flex items-center gap-2 cursor-pointer">

                                <input
                                    type="checkbox"
                                    id="selectAll"
                                    class="w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500"
                                >

                                <span class="text-sm font-medium text-gray-700">
                                    Select All
                                </span>

                            </label>


                            <span
                                id="selectedCount"
                                class="text-sm text-gray-500"
                            >
                                0 selected
                            </span>

                        </div>


                        <div class="flex flex-wrap gap-2">

                            <button
                                type="submit"
                                name="bulk_action"
                                value="approve"
                                id="approveSelectedBtn"
                                disabled
                                class="bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm transition"
                            >
                                <i class="fas fa-check mr-1"></i>
                                Approve Selected
                            </button>


                            <button
                                type="submit"
                                name="bulk_action"
                                value="delete"
                                id="deleteSelectedBtn"
                                disabled
                                class="bg-red-600 hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm transition"
                            >
                                <i class="fas fa-trash mr-1"></i>
                                Delete Selected
                            </button>

                        </div>

                    </div>

                </div>


                <!-- Subscribers Table -->
                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead class="bg-psaBlack text-white">

                            <tr>

                                <th class="p-3 text-center w-12">
                                    Select
                                </th>

                                <th class="p-3 text-left">
                                    #
                                </th>

                                <th class="p-3 text-left">
                                    Name
                                </th>

                                <th class="p-3 text-left">
                                    Email
                                </th>

                                <th class="p-3 text-center">
                                    Status
                                </th>

                                <th class="p-3 text-center">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php

                            $i = 1;

                            if ($subscribers && $subscribers->num_rows > 0):

                                while ($row = $subscribers->fetch_assoc()):

                            ?>

                                <tr class="border-b hover:bg-gray-50 transition">

                                    <!-- Checkbox -->
                                    <td class="p-3 text-center">

                                        <input
                                            type="checkbox"
                                            name="subscriber_ids[]"
                                            value="<?= (int)$row['id'] ?>"
                                            class="subscriber-checkbox w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500"
                                        >

                                    </td>


                                    <!-- Number -->
                                    <td class="p-3">
                                        <?= $i++ ?>
                                    </td>


                                    <!-- Name -->
                                    <td class="p-3 font-medium">

                                        <?= htmlspecialchars(
                                            $row['name'] ?? 'N/A'
                                        ) ?>

                                    </td>


                                    <!-- Email -->
                                    <td class="p-3">

                                        <?= htmlspecialchars(
                                            $row['email']
                                        ) ?>

                                    </td>


                                    <!-- Status -->
                                    <td class="p-3 text-center">

                                        <?php if ($row['approved']): ?>

                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Approved
                                            </span>

                                        <?php else: ?>

                                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                                <i class="fas fa-clock mr-1"></i>
                                                Pending
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Actions -->
                                    <td class="p-3 text-center space-x-3">

                                        <?php if (!$row['approved']): ?>

                                            <a
                                                href="subscriber_approve.php?id=<?= (int)$row['id'] ?>"
                                                class="text-green-600 hover:text-green-800"
                                                title="Approve"
                                                onclick="return confirm('Approve this subscriber?')"
                                            >
                                                <i class="fas fa-check"></i>
                                            </a>

                                        <?php endif; ?>


                                        <a
                                            href="subscriber_delete.php?id=<?= (int)$row['id'] ?>"
                                            onclick="return confirm('Delete this subscriber?')"
                                            class="text-red-600 hover:text-red-800"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </a>

                                    </td>

                                </tr>

                            <?php

                                endwhile;

                            else:

                            ?>

                                <tr>

                                    <td
                                        colspan="6"
                                        class="p-8 text-center text-gray-500"
                                    >

                                        <i class="fas fa-users text-3xl mb-3 block text-gray-300"></i>

                                        <?php if ($statusFilter === 'approved'): ?>

                                            No approved subscribers found.

                                        <?php elseif ($statusFilter === 'pending'): ?>

                                            No unapproved subscribers found.

                                        <?php else: ?>

                                            No subscribers found.

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </form>

        </div>

    </div>

</main>


<script>

/*
|--------------------------------------------------------------------------
| FILTER SUBSCRIBERS
|--------------------------------------------------------------------------
*/
function filterSubscribers(status) {

    window.location.href =
        'manage_subscribers.php?status=' +
        encodeURIComponent(status);
}


/*
|--------------------------------------------------------------------------
| SELECT ALL
|--------------------------------------------------------------------------
*/
const selectAllCheckbox = document.getElementById('selectAll');

const subscriberCheckboxes = document.querySelectorAll(
    '.subscriber-checkbox'
);

const selectedCount = document.getElementById('selectedCount');

const approveSelectedBtn = document.getElementById(
    'approveSelectedBtn'
);

const deleteSelectedBtn = document.getElementById(
    'deleteSelectedBtn'
);


/*
|--------------------------------------------------------------------------
| UPDATE SELECTED COUNT
|--------------------------------------------------------------------------
*/
function updateSelectedCount() {

    const checked = document.querySelectorAll(
        '.subscriber-checkbox:checked'
    ).length;

    selectedCount.textContent =
        checked + (checked === 1 ? ' selected' : ' selected');

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable Buttons
    |--------------------------------------------------------------------------
    */
    approveSelectedBtn.disabled = checked === 0;
    deleteSelectedBtn.disabled = checked === 0;


    /*
    |--------------------------------------------------------------------------
    | Update Select All State
    |--------------------------------------------------------------------------
    */
    if (subscriberCheckboxes.length === 0) {

        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;

    } else if (checked === subscriberCheckboxes.length) {

        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;

    } else if (checked > 0) {

        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;

    } else {

        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
}


/*
|--------------------------------------------------------------------------
| SELECT / UNSELECT ALL
|--------------------------------------------------------------------------
*/
if (selectAllCheckbox) {

    selectAllCheckbox.addEventListener('change', function () {

        subscriberCheckboxes.forEach(function (checkbox) {

            checkbox.checked = selectAllCheckbox.checked;

        });

        updateSelectedCount();

    });

}


/*
|--------------------------------------------------------------------------
| INDIVIDUAL CHECKBOXES
|--------------------------------------------------------------------------
*/
subscriberCheckboxes.forEach(function (checkbox) {

    checkbox.addEventListener('change', function () {

        updateSelectedCount();

    });

});


/*
|--------------------------------------------------------------------------
| BULK ACTION CONFIRMATION
|--------------------------------------------------------------------------
*/
function confirmBulkAction() {

    const selected = document.querySelectorAll(
        '.subscriber-checkbox:checked'
    ).length;

    if (selected === 0) {

        alert('Please select at least one subscriber.');

        return false;
    }


    const clickedButton = document.activeElement;

    let action = '';

    if (
        clickedButton &&
        clickedButton.value === 'approve'
    ) {

        action = 'approve';

    } else if (
        clickedButton &&
        clickedButton.value === 'delete'
    ) {

        action = 'delete';

    }


    if (action === 'approve') {

        return confirm(
            'Are you sure you want to approve ' +
            selected +
            ' selected subscriber(s)?'
        );

    }


    if (action === 'delete') {

        return confirm(
            'WARNING: This will permanently delete ' +
            selected +
            ' selected subscriber(s).\n\n' +
            'Are you sure you want to continue?'
        );

    }


    return true;
}


/*
|--------------------------------------------------------------------------
| INITIAL STATE
|--------------------------------------------------------------------------
*/
updateSelectedCount();

</script>


<?php include 'includes/admin_footer.php'; ?>