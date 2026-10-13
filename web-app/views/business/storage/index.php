<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold m-0">Warehouse Storage</h1>
        <button type="button" class="btn btn-primary fw-bold rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addStorageModal">
            <i data-lucide="plus" style="width:18px;"></i> Add Storage Item
        </button>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Item Name</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No items in storage. Add one!</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <?php 
                                    $isExpired = new DateTime($item['expiry_date']) < new DateTime();
                                    $daysLeft = (new DateTime($item['expiry_date']))->diff(new DateTime())->days;
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><i data-lucide="archive" class="me-2 text-muted" style="width:16px;"></i><?= htmlspecialchars($item['item_name']) ?></td>
                                    <td><span class="fw-bold fs-5"><?= (float)$item['quantity'] ?></span> <span class="text-muted"><?= htmlspecialchars($item['unit']) ?></span></td>
                                    <td>
                                        <span class="<?= $isExpired ? 'text-danger fw-bold' : ($daysLeft <= 3 ? 'text-warning fw-bold' : '') ?>">
                                            <?= htmlspecialchars((new DateTime($item['expiry_date']))->format('Y-m-d')) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary rounded-circle p-2 me-1" title="Edit" onclick="editItem(<?= htmlspecialchars(json_encode($item)) ?>)">
                                            <i data-lucide="edit-2" style="width:16px; height:16px;"></i>
                                        </button>
                                        <form action="<?= BASE_URL ?>/storage/<?= $item['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?');">
                                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Delete">
                                                <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addStorageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0" style="border-radius: var(--radius);">
            <div class="modal-header border-bottom-0 p-4">
                <h5 class="modal-title fw-bold" id="modalTitle">Add Storage Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/storage/store" method="POST">
                <div class="modal-body p-4 pt-0">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="id" id="itemId" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Item Name</label>
                        <input type="text" name="item_name" id="itemName" class="form-control" required>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Quantity</label>
                            <input type="number" step="0.01" name="quantity" id="itemQuantity" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unit</label>
                            <input type="text" name="unit" id="itemUnit" class="form-control" value="kg" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Expiry Date</label>
                        <input type="datetime-local" name="expiry_date" id="itemExpiry" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editItem(item) {
    document.getElementById('modalTitle').innerText = 'Edit Storage Item';
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemName').value = item.item_name;
    document.getElementById('itemQuantity').value = item.quantity;
    document.getElementById('itemUnit').value = item.unit;

    const date = new Date(item.expiry_date);
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    document.getElementById('itemExpiry').value = date.toISOString().slice(0,16);
    
    const modal = new bootstrap.Modal(document.getElementById('addStorageModal'));
    modal.show();
}

document.getElementById('addStorageModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalTitle').innerText = 'Add Storage Item';
    document.getElementById('itemId').value = '0';
    document.getElementById('itemName').value = '';
    document.getElementById('itemQuantity').value = '';
    document.getElementById('itemUnit').value = 'kg';
    document.getElementById('itemExpiry').value = '';
});
</script>
