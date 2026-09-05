<?php
declare(strict_types=1);
?>
<div class="tab-pane fade" id="usersTabContent" role="tabpanel" aria-labelledby="users-tab">
    <div class="admin-panel-card">
        <div class="panel-header-row">
            <div>
                <h3 class="panel-title">👥 Yönetici ve Kullanıcı Hesapları</h3>
                <p class="panel-subtitle">Panoya afiş ve duyuru ekleyebilecek yetkili kullanıcıların yönetimi</p>
            </div>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newUserModal" style="border-radius: var(--radius-md); padding: 8px 16px; font-weight: 600;">
                <span>➕</span> Yeni Kullanıcı Ekle
            </button>
        </div>

        <div class="table-responsive">
            <table id="usersTable" class="table admin-table align-middle">
                <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Kullanıcı Adı</th>
                    <th>E-Posta Adresi</th>
                    <th>Adı Soyadı</th>
                    <th>Kayıt Tarihi</th>
                    <th class="text-center" style="width: 100px;">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                <!-- AJAX ile doldurulur -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Yeni Kullanıcı -->
<div class="modal fade" id="newUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newUserLabel">➕ Yeni Yönetici Hesabı Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="newUserForm" id="newUserForm" method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_userName" class="form-label">Kullanıcı Adı *</label>
                                <input required type="text" class="form-control" id="user_userName" name="userName" placeholder="Kullanıcı adı" minlength="3">
                            </div>
                            <div class="mb-3">
                                <label for="user_name" class="form-label">Adı *</label>
                                <input required type="text" class="form-control" id="user_name" name="name" placeholder="Ad">
                            </div>
                            <div class="mb-3">
                                <label for="user_password" class="form-label">Şifre *</label>
                                <input required type="password" class="form-control" id="user_password" name="password" placeholder="En az 6 karakter" minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_mail" class="form-label">E-Posta Adresi *</label>
                                <input required type="email" class="form-control" id="user_mail" name="mail" placeholder="ornek@edu.tr">
                            </div>
                            <div class="mb-3">
                                <label for="user_lastName" class="form-label">Soyadı *</label>
                                <input required type="text" class="form-control" id="user_lastName" name="lastName" placeholder="Soyad">
                            </div>
                            <div class="mb-3">
                                <label for="user_password2" class="form-label">Şifre (Tekrar) *</label>
                                <input required type="password" class="form-control" id="user_password2" name="password2" placeholder="Şifre tekrarı" minlength="6">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newUserForm" class="btn btn-primary fw-bold px-4">Kullanıcıyı Oluştur</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Kullanıcı Güncelle -->
<div class="modal fade" id="updateUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateUserLabel">✏️ Kullanıcı Bilgilerini Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form name="updateUserForm" id="updateUserForm" method="post">
                    <input type="hidden" id="update_user_id" name="id" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="update_user_userName" class="form-label">Kullanıcı Adı *</label>
                                <input required type="text" class="form-control" id="update_user_userName" name="userName" minlength="3">
                            </div>
                            <div class="mb-3">
                                <label for="update_user_name" class="form-label">Adı *</label>
                                <input required type="text" class="form-control" id="update_user_name" name="name">
                            </div>
                            <div class="mb-3">
                                <label for="update_user_password" class="form-label">Yeni Şifre (İsteğe Bağlı)</label>
                                <input type="password" class="form-control" id="update_user_password" name="password" placeholder="Değiştirmek istemiyorsanız boş bırakın">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="update_user_mail" class="form-label">E-Posta Adresi *</label>
                                <input required type="email" class="form-control" id="update_user_mail" name="mail">
                            </div>
                            <div class="mb-3">
                                <label for="update_user_lastName" class="form-label">Soyadı *</label>
                                <input required type="text" class="form-control" id="update_user_lastName" name="lastName">
                            </div>
                            <div class="mb-3">
                                <label for="update_user_password2" class="form-label">Yeni Şifre (Tekrar)</label>
                                <input type="password" class="form-control" id="update_user_password2" name="password2" placeholder="Şifre tekrarı">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="updateUserForm" class="btn btn-primary fw-bold px-4">Değişiklikleri Kaydet</button>
            </div>
        </div>
    </div>
</div>