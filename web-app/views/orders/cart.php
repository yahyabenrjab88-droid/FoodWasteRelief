<div class="container py-5">
    <div class="d-flex align-items-center mb-4">
        <h1 class="fw-bold m-0 me-3">Your Cart</h1>
        <span class="badge bg-primary fs-6 rounded-pill"><?= count($cartItems) ?> items</span>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: var(--radius);">
            <div class="card-body">
                <i data-lucide="shopping-cart" class="text-muted mb-3" style="width: 64px; height: 64px;"></i>
                <h3 class="fw-bold">Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added any products yet.</p>
                <a href="<?= BASE_URL ?>/products" class="btn btn-primary rounded-pill px-4 py-2 mt-3 fw-bold">Browse Products</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-5">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius);">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle m-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="ps-4">Product</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Subtotal</th>
                                        <th scope="col" class="pe-4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cartItems as $item): ?>
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <?php $img = !empty($item['image_url']) ? $item['image_url'] : 'https://images.unsplash.com/photo-1543362906-acfc16c67564?w=100'; ?>
                                                    <img src="<?= htmlspecialchars($img) ?>" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['nom']) ?></h6>
                                                        <small class="text-muted"><i data-lucide="store" style="width:12px;"></i> <?= htmlspecialchars($item['nom_business']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($item['type'] === 'vente'): ?>
                                                    <span class="fw-bold"><?= number_format((float)$item['prix'], 2) ?> TND</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">FREE</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="fw-bold px-3 py-1 bg-light rounded border"><?= $item['cart_qty'] ?></span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary"><?= number_format((float)$item['item_total'], 2) ?> TND</span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <form action="<?= BASE_URL ?>/cart/remove" method="POST" class="m-0">
                                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                                    <input type="hidden" name="produit_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Remove">
                                                        <i data-lucide="trash-2" style="width:16px; height:16px;"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius: var(--radius); position: sticky; top: 100px;">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">Order Summary</h4>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold"><?= number_format($subtotal, 2) ?> TND</span>
                        </div>
                        
                        <?php if ($savings > 0): ?>
                            <div class="d-flex justify-content-between mb-3 text-success">
                                <span>Estimated Savings</span>
                                <span class="fw-bold">-<?= number_format($savings, 2) ?> TND</span>
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4 align-items-center">
                            <span class="fs-5 fw-bold">Total</span>
                            <span class="fs-3 fw-bold text-primary"><?= number_format($subtotal, 2) ?> TND</span>
                        </div>
                        
                        <form action="<?= BASE_URL ?>/cart/checkout" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-5">
                                <i data-lucide="check-circle" class="me-2"></i> Place Order
                            </button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted"><i data-lucide="shield-check" class="me-1" style="width:14px;"></i> Secure Checkout</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
