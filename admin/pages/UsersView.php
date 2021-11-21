<?php
namespace App\Admin;

use App\UsersController;

$userControler = new UsersController();
$users= $userControler->getUsers();
?>
<div class="tab-pane fade" id="kullanıcılarTabContent" role="tabpanel" aria-labelledby="kullanıcılar-tab">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="card-title card-tit-dash">Kullanıcı Yönetimi</h4>
                        <h5 class="card-subtitle card-subtitle-dash">Kullanıcı Yönetimi</h5>
                    </div>
                    <button type="button" class="btn btn-success btn-icon-text" data-bs-toggle="modal" data-bs-target="#newUserModal">
                        <i class="ti-plus btn-icon-prepend"></i>
                        Yeni ekle
                    </button>
                </div>
                <div>
                    <div class="table-responsive pt-3">
                        <table id="usersTable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>
                                    #
                                </th>
                                <th>
                                    Kullanıcı Adı
                                </th>
                                <th>
                                    Mail Adresi
                                </th>
                                <th>
                                    Adı
                                </th>
                                <th>
                                    Soyadı
                                </th>
                                <th>
                                    Kayıt Tarihi
                                </th>
                                <th>
                                    İşlemler
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade " id="newUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Yeni Kullanıcı Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="newUserForm" id="newUserForm" method="post">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="userName">Kullanıcı Adı</label>
                                <input required type="text" class="form-control" id="userName" name="userName" placeholder="Kullanıcı Adı" minlength="3">
                            </div>
                            <div class="form-group">
                                <label for="password">Şifre</label>
                                <input required type="password" class="form-control" id="password" name="password" placeholder="Şifre">
                            </div>
                            <div class="form-group">
                                <label for="link">Adı</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Şifre tekrar">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="mail">e-Mail</label>
                                <input  type="email" class="form-control" id="mail" name="mail" placeholder="Mail adresiniz">
                            </div>
                            <div class="form-group">
                                <label for="link">Şifre (Doğrulama)</label>
                                <input required type="password" class="form-control" id="password2" name="password2" placeholder="Şifre tekrar">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Soyadı</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Şifre tekrar">
                            </div>
                        </div>
                    </div>


                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="newUserForm" class="btn btn-primary">Ekle</button>
            </div>
        </div>
    </div>
</div>
<!-- Update Modal-->
<div class="modal fade " id="updateUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Kullanıcı Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="updateUserForm" id="updateUserForm" method="post">
                    <input type="hidden" id="id" name="id" value="">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="userName">Kullanıcı Adı</label>
                                <input required type="text" class="form-control" id="userName" name="userName" placeholder="Kullanıcı Adı" minlength="3">
                            </div>
                            <div class="form-group">
                                <label for="password">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Şifre">
                            </div>
                            <div class="form-group">
                                <label for="link">Adı</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Şifre tekrar">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="mail">e-Mail</label>
                                <input  type="email" class="form-control" id="mail" name="mail" placeholder="Mail adresiniz">
                            </div>
                            <div class="form-group">
                                <label for="link">Şifre (Doğrulama)</label>
                                <input type="password" class="form-control" id="password2" name="password2" placeholder="Şifre tekrar">
                            </div>
                            <div class="form-group">
                                <label for="lastName">Soyadı</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Şifre tekrar">
                            </div>
                        </div>
                    </div>


                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="submit" form="updateUserForm" class="btn btn-primary">Güncelle</button>
            </div>
        </div>
    </div>
</div>