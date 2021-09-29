<?php
namespace App\Admin;

use App\UsersControler;

$userControler = new UsersControler();
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
                    <button type="button" class="btn btn-primary btn-icon-text" data-bs-toggle="modal" data-bs-target="#newUserModal">
                        <i class="ti-plus btn-icon-prepend"></i>
                        Yeni ekle
                    </button>
                </div>
                <div>
                    <div class="table-responsive pt-3">
                        <table class="table table-bordered">
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
                                    Profil Fotoğrafı
                                </th>
                                <th>
                                    Kayıt Tarihi
                                </th>

                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $count=1;
                            foreach ($users as $user){
                                ?>
                                <tr>
                                    <td>
                                        <?= $count?>
                                    </td>
                                    <td>
                                        <?= $user->userName?>
                                    </td>
                                    <td>
                                        <?= $user->mail?>
                                    </td>
                                    <td>
                                        <?= $user->name?>
                                    </td>
                                    <td>
                                        <?= $user->lastName?>
                                    </td>
                                    <td>
                                        <?= $user->profilPicture?>
                                    </td>
                                    <td>
                                        <?= $user->createdDate?>
                                    </td>
                                </tr>
                                <?php
                                $count++;
                            }
                            ?>


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
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
                <button type="button" class="btn btn-primary">Ekle</button>
            </div>
        </div>
    </div>
</div>