<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary m-0"><i data-lucide="mail" class="me-2"></i>Contact Requests</h3>
</div>

<div class="row g-4">
    <div class="col-12">
        <?php if(empty($requests)): ?>
            <div class="card border-0 shadow-sm text-center p-5" style="border-radius: var(--radius);">
                <i data-lucide="mail-open" style="width: 64px; height: 64px; color: #ccc;" class="mb-3 mx-auto"></i>
                <h5 class="text-muted fw-bold">Inbox Empty</h5>
                <p class="text-muted m-0">You have no contact requests.</p>
            </div>
        <?php else: ?>
            <div class="accordion shadow-sm" id="contactAccordion" style="border-radius: var(--radius); overflow: hidden;">
                <?php foreach($requests as $idx => $r): ?>
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="heading<?= $r['id'] ?>">
                            <button class="accordion-button <?= $r['is_read'] ? 'collapsed' : 'fw-bold bg-primary-pale' ?> d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $r['id'] ?>" aria-expanded="<?= !$r['is_read'] && $idx === 0 ? 'true' : 'false' ?>">
                                <div class="d-flex justify-content-between w-100 me-3 align-items-center">
                                    <div class="d-flex align-items-center">
                                        <?php if(!$r['is_read']): ?>
                                            <span class="badge bg-primary rounded-circle p-1 me-2" style="width: 10px; height: 10px;"><span class="visually-hidden">New</span></span>
                                        <?php endif; ?>
                                        <i data-lucide="user" class="text-muted me-2" style="width: 16px;"></i> 
                                        <?= e($r['name']) ?> 
                                        <span class="text-muted small ms-3 d-none d-md-inline">&bull; <?= e($r['subject']) ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        <?= \Carbon\Carbon::parse($r['date_creation'])->diffForHumans() ?? date('M d', strtotime($r['date_creation'])) ?>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?= $r['id'] ?>" class="accordion-collapse collapse <?= !$r['is_read'] && $idx === 0 ? 'show' : '' ?>" data-bs-parent="#contactAccordion">
                            <div class="accordion-body p-4 bg-white">
                                <div class="d-flex justify-content-between mb-4 border-bottom pb-3">
                                    <div>
                                        <div class="fw-bold fs-5 mb-1"><?= e($r['subject']) ?></div>
                                        <div class="text-muted small">From: <a href="mailto:<?= e($r['email']) ?>" class="text-decoration-none text-primary fw-bold"><?= e($r['email']) ?></a></div>
                                    </div>
                                    <div class="text-end text-muted small">
                                        <?= date('F j, Y, g:i a', strtotime($r['date_creation'])) ?>
                                    </div>
                                </div>
                                
                                <p class="mb-4" style="white-space: pre-wrap;"><?= e($r['message']) ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <a href="mailto:<?= e($r['email']) ?>?subject=RE: <?= e($r['subject']) ?>" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                        <i data-lucide="reply" class="me-2" style="width: 16px;"></i> Reply via Email
                                    </a>
                                    
                                    <?php if(!$r['is_read']): ?>
                                        <form action="<?= BASE_URL ?>/admin/contact/<?= $r['id'] ?>/read" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                            <button type="submit" class="btn btn-outline-secondary rounded-pill btn-sm px-3">Mark as Read</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small"><i data-lucide="check" class="me-1" style="width: 14px;"></i> Read</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
