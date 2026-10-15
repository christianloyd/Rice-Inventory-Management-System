<?php 
$title = "System Users";
ob_start(); 
?>

<div class="sm:flex sm:items-center justify-between">
    <div class="sm:flex-auto">
        <p class="text-sm text-text-muted">Manage administrators and cashiers authorized to access RISMS.</p>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <button onclick="openAddUserModal()" class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:w-auto">
            <i data-lucide="user-plus" class="-ml-1 mr-2 h-5 w-5"></i>
            Add User
        </button>
    </div>
</div>

<div id="alertContainer" class="mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="rounded-md bg-green-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0"><i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i></div>
                <div class="ml-3"><p class="text-sm font-medium text-green-800"><?= e($_SESSION['success']) ?></p></div>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="rounded-md bg-red-50 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0"><i data-lucide="x-circle" class="h-5 w-5 text-red-400"></i></div>
                <div class="ml-3"><p class="text-sm font-medium text-red-800"><?= e($_SESSION['error']) ?></p></div>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>

<div class="mt-4 flex flex-col">
    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-background">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-text-primary sm:pl-6">Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Username</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Role</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Status</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-text-primary">Joined</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border bg-surface">
                        <?php foreach($users as $user): ?>
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-text-primary sm:pl-6">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 flex-shrink-0 rounded-full bg-background flex items-center justify-center text-text-muted border border-border">
                                            <i data-lucide="user" class="h-4 w-4"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-medium text-text-primary"><?= e($user['full_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= e($user['username']) ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span class="inline-flex rounded-full bg-primary bg-opacity-10 px-2 py-0.5 text-xs font-semibold leading-5 text-primary tracking-wider uppercase">
                                        <?= e($user['role']) ?>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <?php if($user['status'] == 1 || $user['status'] === true || $user['status'] === 'active'): ?>
                                        <span class="inline-flex rounded-full bg-status-successBg px-2 py-0.5 text-xs font-semibold leading-5 text-success">Active</span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-status-cancelBg px-2 py-0.5 text-xs font-semibold leading-5 text-error">Locked</span>
                                    <?php endif; ?>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-text-muted">
                                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <button type="button" onclick="openEditUserModal(<?= $user['id'] ?>, '<?= e($user['full_name']) ?>', '<?= e($user['username']) ?>', '<?= e($user['role']) ?>', <?= $user['can_record_stock_in'] ? 'true' : 'false' ?>)" class="text-indigo-500 hover:text-indigo-900 mr-3" title="Edit User">
                                        <i data-lucide="edit-3" class="h-5 w-5"></i>
                                    </button>
                                    <button type="button" onclick="openResetPasswordModal(<?= $user['id'] ?>, '<?= e($user['username']) ?>')" class="text-blue-500 hover:text-blue-900 mr-3" title="Reset Password">
                                        <i data-lucide="key" class="h-5 w-5"></i>
                                    </button>
                                    <?php if($_SESSION['user_id'] != $user['id']): ?>
                                    <form action="<?= url('/users/' . $user['id'] . '/toggle') ?>" method="POST" class="inline-block">
                                        <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                                        <?php if($user['status'] == 1 || $user['status'] === true || $user['status'] === 'active'): ?>
                                            <button type="submit" class="text-error hover:text-red-900" title="Lock User">
                                                <i data-lucide="lock" class="h-5 w-5"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="text-success hover:text-green-900" title="Unlock User">
                                                <i data-lucide="unlock" class="h-5 w-5"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="hidden relative z-50">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-surface text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form action="<?= url('/users') ?>" method="POST">
                    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                    <div class="bg-surface px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="user-plus" class="h-6 w-6 text-primary"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-semibold leading-6 text-text-primary" id="modal-title">Add New User</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-text-primary">Full Name <span class="text-error">*</span></label>
                                        <input type="text" name="full_name" id="full_name" required class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-sm">
                                    </div>
                                    <div>
                                        <label for="username" class="block text-sm font-medium text-text-primary">Username <span class="text-error">*</span></label>
                                        <input type="text" name="username" id="username" required class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-sm" autocomplete="new-username">
                                    </div>
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-text-primary">Password <span class="text-error">*</span></label>
                                        <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-sm" autocomplete="new-password">
                                    </div>
                                    <div>
                                        <label for="role" class="block text-sm font-medium text-text-primary">Role Level <span class="text-error">*</span></label>
                                        <select name="role" id="role" required class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-sm">
                                            <option value="staff">Staff (Sales & POS access)</option>
                                            <option value="admin">Administrator (Full Access)</option>
                                            <option value="viewer">Viewer (Read-Only)</option>
                                        </select>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="can_record_stock_in" name="can_record_stock_in" type="checkbox" value="1" class="h-4 w-4 rounded border-border text-primary focus:ring-primary" checked>
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="can_record_stock_in" class="font-medium text-text-primary">Allow Stock-in Access</label>
                                            <p class="text-text-muted text-xs">Users assigned `staff` role will be able to restock inventory.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-hover sm:ml-3 sm:w-auto">Create User</button>
                        <button type="button" onclick="closeAddUserModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<!-- Edit User Modal -->
<div id="editUserModal" class="hidden relative z-50">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-surface text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <form id="editUserForm" action="" method="POST">
                    <?= \App\Middleware\CsrfMiddleware::csrfField() ?>
                    <div class="bg-surface px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i data-lucide="edit" class="h-6 w-6 text-indigo-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-semibold leading-6 text-text-primary">Edit User</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="edit_full_name" class="block text-sm font-medium text-text-primary">Full Name <span class="text-error">*</span></label>
                                        <input type="text" name="full_name" id="edit_full_name" required class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-sm">
                                    </div>
                                    <div>
                                        <label for="edit_username" class="block text-sm font-medium text-text-primary">Username <span class="text-error">*</span></label>
                                        <input type="text" name="username" id="edit_username" required class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-sm">
                                    </div>
                                    <div>
                                        <label for="edit_role" class="block text-sm font-medium text-text-primary">Role Level <span class="text-error">*</span></label>
                                        <select name="role" id="edit_role" required class="mt-1 block w-full rounded-md border border-border bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary shadow-sm">
                                            <option value="staff">Staff (Sales & POS access)</option>
                                            <option value="admin">Administrator (Full Access)</option>
                                            <option value="viewer">Viewer (Read-Only)</option>
                                        </select>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex h-6 items-center">
                                            <input id="edit_can_record_stock_in" name="can_record_stock_in" type="checkbox" value="1" class="h-4 w-4 rounded border-border text-primary focus:ring-primary">
                                        </div>
                                        <div class="ml-3 text-sm leading-6">
                                            <label for="edit_can_record_stock_in" class="font-medium text-text-primary">Allow Stock-in Access</label>
                                            <p class="text-text-muted text-xs">Users assigned `staff` role will be able to restock inventory.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:ml-3 sm:w-auto">Update User</button>
                        <button type="button" onclick="closeEditUserModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
}
function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
}
function openResetPasswordModal(id, username) {
    document.getElementById('reset_username_display').innerText = username;
    document.getElementById('resetPasswordForm').action = '<?= url('/users/') ?>' + id + '/reset-password';
    document.getElementById('new_password').value = '';
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}
function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
}
function openEditUserModal(id, fullName, username, role, canRecord) {
    document.getElementById('edit_full_name').value = fullName;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_can_record_stock_in').checked = canRecord;
    document.getElementById('editUserForm').action = '<?= url('/users/') ?>' + id;
    document.getElementById('editUserModal').classList.remove('hidden');
}
function closeEditUserModal() {
    document.getElementById('editUserModal').classList.add('hidden');
}
</script>

<?php 
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php'; 
?>
