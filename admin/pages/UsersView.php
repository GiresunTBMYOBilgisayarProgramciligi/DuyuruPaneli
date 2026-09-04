<?php
declare(strict_types=1);

namespace App\Admin;
?>
<div class="tab-pane fade" id="kullanıcılarTabContent" role="tabpanel" aria-labelledby="kullanıcılar-tab">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-sm-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title fw-bold text-dark">Yönetici ve Kullanıcı Hesapları</h4>
                        <h5 class="card-subtitle text-muted mb-0">Panoyu yönetebilecek yetkili kullanıcılar</h5>
                    </div>
                    <button type="button" class="btn btn-primary btn-icon-text" data-bs-toggle="modal" data-bs-target="#newUserModal">
                        <i class="ti-plus btn-icon-prepend"></i> Yeni Kullanıcı Ekle
                    </button>
                </div>
                <div>
                    <div class="table-responsive pt-2">
                        <table id="usersTable" class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Kullanıcı Adı</th>
                                <th>E-Posta Adresi</th>
                                <th>Adı</th>
                                <th>Soyadı</th>
                                <th>Kayıt Tarihi</th>
                                <th class="text-center">İşlemler</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Yeni Kullanıcı -->
<div class="modal fade" id="newUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="newUserLabel">Yeni Yönetici Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="newUserForm" id="newUserForm" method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="user_userName" class="form-label fw-semibold">Kullanıcı Adı *</label>
                                <input required type="text" class="form-control" id="user_userName" name="userName" placeholder="Kullanıcı adı" minlength="3">
                            </div>
                            <div class="form-group mb-3">
                                <label for="user_name" class="form-label fw-semibold">Adı *</label>
                                <input required type="text" class="form-control" id="user_name" name="name" placeholder="Adınız">
                            </div>
                            <div class="form-group mb-3">
                                <label for="user_password" class="form-label fw-semibold">Şifre *</label>
                                <input required type="password" class="form-control" id="user_password" name="password" placeholder="En az 6 karakter" minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="user_mail" class="form-label fw-semibold">E-Posta Adresi *</label>
                                <input required type="email" class="form-control" id="user_mail" name="mail" placeholder="ornek@edu.tr">
                            </div>
                            <div class="form-group mb-3">
                                <label for="user_lastName" class="form-label fw-semibold">Soyadı *</label>
                                <input required type="text" class="form-control" id="user_lastName" name="lastName" placeholder="Soyadınız">
                            </div>
                            <div class="form-group mb-3">
                                <label for="user_password2" class="form-label fw-semibold">Şifre (Tekrar) *</label>
                                <input required type="password" class="form-control" id="user_password2" name="password2" placeholder="Şifrenizi tekrar girin" minlength="6">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newUserForm" class="btn btn-primary">Kullanıcıyı Oluştur</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Kullanıcı Güncelle -->
<div class="modal fade" id="updateUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="updateUserLabel">Kullanıcı Bilgilerini Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="updateUserForm" id="updateUserForm" method="post">
                    <input type="hidden" id="update_user_id" name="id" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="update_user_userName" class="form-label fw-semibold">Kullanıcı Adı *</label>
                                <input required type="text" class="form-control" id="update_user_userName" name="userName" minlength="3">
                            </div>
                            <div class="form-group mb-3">
                                <label for="update_user_name" class="form-label fw-semibold">Adı *</label>
                                <input required type="text" class="form-control" id="update_user_name" name="name">
                            </div>
                            <div class="form-group mb-3">
                                <label for="update_user_password" class="form-label fw-semibold">Yeni Şifre (İsteğe Bağlı)</label>
                                <input type="password" class="form-control" id="update_user_password" name="password" placeholder="Değiştirmek istemiyorsanız boş bırakın">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="update_user_mail" class="form-label fw-semibold">E-Posta Adresi *</label>
                                <input required type="email" class="form-control" id="update_user_mail" name="mail">
                            </div>
                            <div class="form-group mb-3">
                                <label for="update_user_lastName" class="form-label fw-semibold">Soyadı *</label>
                                <input required type="text" class="form-control" id="update_user_lastName" name="lastName">
                            </div>
                            <div class="form-group mb-3">
                                <label for="update_user_password2" class="form-label fw-semibold">Yeni Şifre (Tekrar)</label>
                                <input type="password" class="form-control" id="update_user_password2" name="password2" placeholder="Şifre tekrarı">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="updateUserForm" class="btn btn-primary">Değişiklikleri Kaydet</button>
            </div>
        </div>
    </div>
</div>